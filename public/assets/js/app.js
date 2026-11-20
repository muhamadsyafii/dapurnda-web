(() => {
    'use strict';

    const $ = (selector, scope = document) => scope.querySelector(selector);
    const $$ = (selector, scope = document) => [...scope.querySelectorAll(selector)];

    const header = $('[data-header]');
    const nav = $('[data-nav]');
    const navToggle = $('[data-nav-toggle]');
    const syncHeader = () => header?.classList.toggle('is-scrolled', scrollY > 18);
    syncHeader();
    addEventListener('scroll', syncHeader, { passive: true });

    const scrollProgress = $('[data-scroll-progress]');
    const syncScrollProgress = () => {
        if (!scrollProgress) return;
        const scrollable = document.documentElement.scrollHeight - innerHeight;
        const progress = scrollable > 0 ? Math.min(100, Math.max(0, (scrollY / scrollable) * 100)) : 0;
        scrollProgress.style.width = `${progress}%`;
    };
    syncScrollProgress();
    addEventListener('scroll', syncScrollProgress, { passive: true });
    addEventListener('resize', syncScrollProgress);
    navToggle?.addEventListener('click', () => {
        const open = nav?.classList.toggle('is-open');
        navToggle.setAttribute('aria-expanded', String(Boolean(open)));
    });
    document.addEventListener('click', (event) => {
        if (nav?.classList.contains('is-open') && !nav.contains(event.target) && !navToggle?.contains(event.target)) {
            nav.classList.remove('is-open');
            navToggle?.setAttribute('aria-expanded', 'false');
        }
    });

    const revealObserver = 'IntersectionObserver' in window
        ? new IntersectionObserver((entries, observer) => entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        }), { threshold: .08, rootMargin: '0px 0px -35px' })
        : null;
    const observeReveals = (scope = document) => $$('.reveal', scope).forEach((element) => {
        if (element.classList.contains('is-visible')) return;
        if (revealObserver) revealObserver.observe(element); else element.classList.add('is-visible');
    });
    observeReveals();

    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const precisePointer = matchMedia('(pointer: fine)').matches;
    const heroMotion = $('[data-hero-motion]');
    if (heroMotion && precisePointer && !reducedMotion) {
        heroMotion.addEventListener('pointermove', (event) => {
            const bounds = heroMotion.getBoundingClientRect();
            const horizontal = ((event.clientX - bounds.left) / bounds.width - .5) * 10;
            const vertical = ((event.clientY - bounds.top) / bounds.height - .5) * 8;
            heroMotion.style.setProperty('--hero-x', `${horizontal}px`);
            heroMotion.style.setProperty('--hero-y', `${vertical}px`);
        });
        heroMotion.addEventListener('pointerleave', () => {
            heroMotion.style.setProperty('--hero-x', '0px');
            heroMotion.style.setProperty('--hero-y', '0px');
        });
    }

    const bindMotionCards = (scope = document) => $$('[data-motion-card]', scope).forEach((card) => {
        if (card.dataset.motionBound || !precisePointer || reducedMotion) return;
        card.dataset.motionBound = 'true';
        card.addEventListener('pointermove', (event) => {
            const bounds = card.getBoundingClientRect();
            const horizontal = ((event.clientX - bounds.left) / bounds.width - .5) * -8;
            const vertical = ((event.clientY - bounds.top) / bounds.height - .5) * -8;
            card.style.setProperty('--media-x', `${horizontal}px`);
            card.style.setProperty('--media-y', `${vertical}px`);
        });
        card.addEventListener('pointerleave', () => {
            card.style.setProperty('--media-x', '0px');
            card.style.setProperty('--media-y', '0px');
        });
    });
    bindMotionCards();

    $$('[data-toast]').forEach((toast, index) => setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 4500 + index * 500));

    const carousel = $('[data-carousel]');
    if (carousel) {
        const slides = $$('[data-slide]', carousel);
        const dots = $$('[data-slide-dot]', carousel);
        let active = 0;
        let timer;
        const show = (index) => {
            active = (index + slides.length) % slides.length;
            slides.forEach((slide, itemIndex) => slide.classList.toggle('is-active', itemIndex === active));
            dots.forEach((dot, itemIndex) => dot.classList.toggle('is-active', itemIndex === active));
        };
        const autoplay = () => {
            clearInterval(timer);
            if (slides.length > 1 && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
                timer = setInterval(() => show(active + 1), 5500);
            }
        };
        dots.forEach((dot) => dot.addEventListener('click', () => { show(Number(dot.dataset.slideDot)); autoplay(); }));
        carousel.addEventListener('mouseenter', () => clearInterval(timer));
        carousel.addEventListener('mouseleave', autoplay);
        autoplay();
    }

    const searchForm = $('[data-live-search]');
    const recipeGrid = $('[data-recipe-grid]');
    const summary = $('[data-search-summary]');
    const emptyState = $('[data-empty-state]');
    const filterContainer = $('[data-category-filters]');
    const loadMoreWrap = $('[data-load-more-wrap]');
    const loadMoreButton = $('[data-load-more]');
    const loadedCount = $('[data-loaded-count]');
    const totalCount = $('[data-total-count]');
    const infiniteLabel = $('[data-infinite-label]');
    if (searchForm && recipeGrid) {
        const input = $('input[name="q"]', searchForm);
        let categoryInput = $('[name="category"]', searchForm);
        const categorySelect = $('[data-category-select]', searchForm);
        const categoryPicker = $('[data-category-picker]', searchForm);
        const categoryTrigger = $('[data-category-trigger]', searchForm);
        const categoryMenu = $('[data-category-menu]', searchForm);
        const categoryCurrent = $('[data-category-current]', searchForm);
        const categoryOptions = $$('[data-category-option]', searchForm);
        let searchTimer;
        let requestController;
        let isLoadingRecipes = false;
        let automaticLoadingPaused = false;
        const endpoint = searchForm.dataset.searchEndpoint || new URL('api/search.php', document.baseURI);
        const pageSize = Number(loadMoreButton?.dataset.pageSize || 10);
        const updateLoadMore = (total) => {
            const loaded = $$('[data-recipe-card]', recipeGrid).length;
            if (loadedCount) loadedCount.textContent = String(loaded);
            if (totalCount) totalCount.textContent = String(total);
            loadMoreWrap?.classList.toggle('is-hidden', loaded >= total);
            if (infiniteLabel && loaded < total && !automaticLoadingPaused) infiniteLabel.textContent = 'Gulir sedikit lagi untuk melihat resep berikutnya';
        };
        const runSearch = async (pushUrl = false, append = false) => {
            if (isLoadingRecipes && append) return;
            clearTimeout(searchTimer);
            requestController?.abort();
            const currentController = new AbortController();
            requestController = currentController;
            isLoadingRecipes = true;
            if (!append) automaticLoadingPaused = false;
            const params = new URLSearchParams();
            if (input.value.trim()) params.set('q', input.value.trim());
            if (categoryInput?.value) params.set('category', categoryInput.value);
            params.set('limit', String(pageSize));
            params.set('offset', String(append ? $$('[data-recipe-card]', recipeGrid).length : 0));
            recipeGrid.classList.add('is-loading');
            loadMoreWrap?.classList.add('is-loading');
            loadMoreWrap?.classList.remove('has-error');
            if (infiniteLabel) infiniteLabel.textContent = append ? 'Menyiapkan 10 resep berikutnya…' : 'Mencari resep yang cocok…';
            if (loadMoreButton) loadMoreButton.disabled = true;
            try {
                const response = await fetch(`${endpoint}?${params}`, { signal: currentController.signal, headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Pencarian gagal');
                const result = await response.json();
                if (append) recipeGrid.insertAdjacentHTML('beforeend', result.html); else recipeGrid.innerHTML = result.html;
                summary.innerHTML = `${input.value.trim() ? `Hasil untuk “${escapeText(input.value.trim())}” · ` : ''}<span>${result.total}</span> resep ditemukan`;
                emptyState?.classList.toggle('is-hidden', result.total > 0);
                updateLoadMore(result.total);
                observeReveals(recipeGrid);
                bindMotionCards(recipeGrid);
                if (pushUrl) {
                    const visibleParams = new URLSearchParams();
                    if (input.value.trim()) visibleParams.set('q', input.value.trim());
                    if (categoryInput?.value) visibleParams.set('category', categoryInput.value);
                    history.pushState({}, '', visibleParams.toString() ? `${searchForm.action}?${visibleParams}` : searchForm.action);
                }
            } catch (error) {
                if (error.name !== 'AbortError' && !append) searchForm.submit();
                if (error.name !== 'AbortError' && append) {
                    automaticLoadingPaused = true;
                    loadMoreWrap?.classList.add('has-error');
                    if (infiniteLabel) infiniteLabel.textContent = 'Koneksi terputus. Sentuh tombol untuk mencoba lagi.';
                }
            } finally {
                if (requestController === currentController) {
                    isLoadingRecipes = false;
                    recipeGrid.classList.remove('is-loading');
                    loadMoreWrap?.classList.remove('is-loading');
                    if (loadMoreButton) loadMoreButton.disabled = false;
                }
            }
        };
        input?.addEventListener('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(() => runSearch(true), 320); });
        searchForm.addEventListener('submit', (event) => { event.preventDefault(); runSearch(true); });
        const syncCategoryPicker = (value) => {
            const activeOption = categoryOptions.find((option) => option.dataset.value === value);
            categoryOptions.forEach((option) => option.classList.toggle('is-active', option === activeOption));
            if (categoryCurrent) categoryCurrent.textContent = activeOption?.dataset.label || 'Semua kategori';
        };
        const closeCategoryPicker = () => {
            if (categoryMenu) categoryMenu.hidden = true;
            categoryPicker?.classList.remove('is-open');
            categoryTrigger?.setAttribute('aria-expanded', 'false');
        };
        filterContainer?.addEventListener('click', (event) => {
            const chip = event.target.closest('[data-category]');
            if (!chip) return;
            event.preventDefault();
            if (!categoryInput) {
                categoryInput = document.createElement('input');
                categoryInput.type = 'hidden';
                categoryInput.name = 'category';
                searchForm.append(categoryInput);
            }
            categoryInput.value = chip.dataset.category;
            if (categorySelect) categorySelect.value = chip.dataset.category;
            syncCategoryPicker(chip.dataset.category);
            $$('[data-category]', filterContainer).forEach((item) => item.classList.toggle('is-active', item === chip));
            runSearch(true);
        });
        categorySelect?.addEventListener('change', () => {
            $$('[data-category]', filterContainer).forEach((item) => item.classList.toggle('is-active', item.dataset.category === categorySelect.value));
            syncCategoryPicker(categorySelect.value);
            runSearch(true);
        });
        categoryTrigger?.addEventListener('click', () => {
            const willOpen = Boolean(categoryMenu?.hidden);
            if (categoryMenu) categoryMenu.hidden = !willOpen;
            categoryPicker?.classList.toggle('is-open', willOpen);
            categoryTrigger.setAttribute('aria-expanded', String(willOpen));
        });
        categoryMenu?.addEventListener('click', (event) => {
            const option = event.target.closest('[data-category-option]');
            if (!option) return;
            if (categorySelect) categorySelect.value = option.dataset.value;
            categoryInput = categorySelect || categoryInput;
            syncCategoryPicker(option.dataset.value);
            $$('[data-category]', filterContainer).forEach((item) => item.classList.toggle('is-active', item.dataset.category === option.dataset.value));
            closeCategoryPicker();
            runSearch(true);
        });
        document.addEventListener('click', (event) => {
            if (categoryPicker?.classList.contains('is-open') && !categoryPicker.contains(event.target)) closeCategoryPicker();
        });
        categoryPicker?.addEventListener('keydown', (event) => { if (event.key === 'Escape') { closeCategoryPicker(); categoryTrigger?.focus(); } });
        loadMoreButton?.addEventListener('click', () => {
            automaticLoadingPaused = false;
            runSearch(false, true);
        });
        if ('IntersectionObserver' in window && loadMoreWrap) {
            const mobileViewport = window.matchMedia('(max-width: 720px)');
            const infiniteObserver = new IntersectionObserver((entries) => {
                const shouldLoad = entries.some((entry) => entry.isIntersecting)
                    && mobileViewport.matches
                    && !loadMoreWrap.classList.contains('is-hidden')
                    && !automaticLoadingPaused
                    && !isLoadingRecipes;
                if (shouldLoad) runSearch(false, true);
            }, { rootMargin: '420px 0px 420px', threshold: 0.01 });
            infiniteObserver.observe(loadMoreWrap);
        }
        updateLoadMore(Number(totalCount?.textContent || $$('[data-recipe-card]', recipeGrid).length));
    }

    const viewSwitches = $$('[data-view-switch]');
    if (viewSwitches.length && recipeGrid) {
        const storageKey = 'dapurnda-recipe-view';
        const buttons = viewSwitches.flatMap((switcher) => $$('[data-view]', switcher));
        const applyView = (view, remember = true) => {
            const selected = view === 'list' ? 'list' : 'grid';
            recipeGrid.classList.toggle('is-view-grid', selected === 'grid');
            recipeGrid.classList.toggle('is-view-list', selected === 'list');
            buttons.forEach((button) => {
                const active = button.dataset.view === selected;
                button.classList.toggle('is-active', active);
                button.setAttribute('aria-pressed', String(active));
            });
            if (remember) localStorage.setItem(storageKey, selected);
        };
        const requestedView = new URLSearchParams(location.search).get('view');
        let savedView = requestedView || 'grid';
        try { savedView = requestedView || localStorage.getItem(storageKey) || 'grid'; } catch (error) { savedView = requestedView || 'grid'; }
        applyView(savedView, false);
        viewSwitches.forEach((switcher) => switcher.addEventListener('click', (event) => {
            const button = event.target.closest('[data-view]');
            if (button) applyView(button.dataset.view);
        }));
    }

    function escapeText(value) {
        const node = document.createElement('span');
        node.textContent = value;
        return node.innerHTML;
    }

    const recipeForm = $('[data-recipe-form]');
    if (recipeForm) {
        const ingredientList = $('[data-ingredient-list]', recipeForm);
        const stepList = $('[data-step-list]', recipeForm);
        const refreshSteps = () => $$('.step-index', stepList).forEach((item, index) => { item.textContent = String(index + 1).padStart(2, '0'); });
        $('[data-add-ingredient]', recipeForm)?.addEventListener('click', () => {
            if ($$('.ingredient-row', ingredientList).length >= 40) return;
            const row = $('.ingredient-row', ingredientList)?.cloneNode(true);
            if (!row) return;
            $$('input', row).forEach((field) => { field.value = ''; });
            ingredientList.append(row);
            $('input', row)?.focus();
        });
        $('[data-add-step]', recipeForm)?.addEventListener('click', () => {
            if ($$('.step-row', stepList).length >= 40) return;
            const row = $('.step-row', stepList)?.cloneNode(true);
            if (!row) return;
            const textarea = $('textarea', row);
            if (textarea) textarea.value = '';
            stepList.append(row);
            refreshSteps();
            textarea?.focus();
        });
        recipeForm.addEventListener('click', (event) => {
            const remove = event.target.closest('[data-remove-row]');
            if (!remove) return;
            const list = remove.closest('.dynamic-list');
            if ($$('.ingredient-row, .step-row', list).length <= 2) return;
            remove.parentElement.remove();
            refreshSteps();
        });
        const description = $('textarea[name="description"]', recipeForm);
        const counter = $('[data-char-count]', recipeForm);
        const updateCounter = () => { if (counter && description) counter.textContent = description.value.length; };
        description?.addEventListener('input', updateCounter);
        updateCounter();

        const wizardSteps = $$('[data-form-step]', recipeForm);
        const wizardMedia = matchMedia('(max-width: 720px)');
        const wizardProgress = $('[data-form-progress]', recipeForm);
        const wizardBar = $('[data-wizard-bar]', recipeForm);
        const wizardTitle = $('[data-wizard-title]', recipeForm);
        const wizardCount = $('[data-wizard-count]', recipeForm);
        const wizardShortCount = $('[data-wizard-short-count]', recipeForm);
        const wizardBack = $('[data-wizard-back]', recipeForm);
        const wizardNext = $('[data-wizard-next]', recipeForm);
        const wizardTitles = ['Informasi resep', 'Foto resep', 'Bahan-bahan', 'Langkah memasak'];
        let wizardIndex = 0;

        const showWizardStep = (index, scrollToProgress = true) => {
            wizardIndex = Math.max(0, Math.min(index, wizardSteps.length - 1));
            recipeForm.dataset.currentStep = String(wizardIndex + 1);
            wizardSteps.forEach((step, stepIndex) => {
                const active = stepIndex === wizardIndex;
                step.classList.toggle('is-wizard-active', active);
                if (wizardMedia.matches) step.setAttribute('aria-hidden', String(!active)); else step.removeAttribute('aria-hidden');
                if (active) step.classList.add('is-visible');
            });
            if (wizardBar) wizardBar.style.width = `${((wizardIndex + 1) / wizardSteps.length) * 100}%`;
            if (wizardTitle) wizardTitle.textContent = wizardTitles[wizardIndex] || 'Tulis resep';
            if (wizardCount) wizardCount.textContent = `Langkah ${wizardIndex + 1} dari ${wizardSteps.length}`;
            if (wizardShortCount) wizardShortCount.textContent = `${wizardIndex + 1}/${wizardSteps.length}`;
            if (wizardBack) wizardBack.disabled = wizardIndex === 0;
            if (wizardNext) wizardNext.hidden = wizardIndex === wizardSteps.length - 1;
            if (scrollToProgress && wizardMedia.matches) wizardProgress?.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
        };

        const currentStepIsValid = () => {
            const fields = $$('input, select, textarea', wizardSteps[wizardIndex]).filter((field) => !field.disabled && field.type !== 'hidden');
            const invalid = fields.find((field) => !field.checkValidity());
            if (invalid) {
                invalid.reportValidity();
                invalid.focus({ preventScroll: true });
                invalid.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'center' });
                return false;
            }
            return true;
        };

        const syncWizardMode = () => {
            recipeForm.classList.toggle('has-wizard', wizardMedia.matches);
            showWizardStep(wizardIndex, false);
        };
        wizardBack?.addEventListener('click', () => showWizardStep(wizardIndex - 1));
        wizardNext?.addEventListener('click', () => { if (currentStepIsValid()) showWizardStep(wizardIndex + 1); });
        wizardMedia.addEventListener?.('change', syncWizardMode);
        syncWizardMode();

        recipeForm.addEventListener('submit', (event) => {
            if (recipeForm.classList.contains('has-wizard') && wizardIndex < wizardSteps.length - 1) {
                event.preventDefault();
                if (currentStepIsValid()) showWizardStep(wizardIndex + 1);
                return;
            }
            const submit = $('[data-submit-button]', recipeForm);
            if (submit) { submit.disabled = true; submit.textContent = 'Sedang mengajukan…'; }
        });
    }

    $$('[data-image-input]').forEach((input) => input.addEventListener('change', async () => {
        if (!input.files?.[0]) return;
        const original = input.files[0];
        if (original.type && !original.type.startsWith('image/')) {
            alert('File yang dipilih harus berupa gambar.'); input.value = ''; return;
        }
        try {
            const zone = input.closest('[data-upload-zone]');
            const sizeLabel = zone?.querySelector('[data-file-size]');
            zone?.classList.add('is-processing');
            if (sizeLabel) sizeLabel.textContent = 'Sedang mengoptimalkan foto…';
            const compressed = await compressImage(original);
            const transfer = new DataTransfer();
            transfer.items.add(compressed);
            input.files = transfer.files;
            const preview = zone?.querySelector('[data-image-preview]');
            if (preview) {
                preview.src = URL.createObjectURL(compressed);
                zone.classList.add('has-image');
                if (sizeLabel) {
                    const originalSize = Math.ceil(original.size / 1024);
                    const finalSize = Math.ceil(compressed.size / 1024);
                    sizeLabel.textContent = originalSize > finalSize ? `${originalSize} KB → ${finalSize} KB · siap dikirim` : `${finalSize} KB · siap dikirim`;
                }
            }
        } catch (error) {
            alert(error.message || 'Gambar tidak dapat diproses.');
            input.value = '';
        } finally {
            input.closest('[data-upload-zone]')?.classList.remove('is-processing');
        }
    }));

    async function compressImage(file) {
        const targetBytes = 500 * 1024;
        const safeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (file.size <= targetBytes && safeTypes.includes(file.type)) return file;
        const image = await loadImageForCompression(file);
        let width = image.width;
        let height = image.height;
        const maxDimension = 2200;
        if (Math.max(width, height) > maxDimension) {
            const ratio = maxDimension / Math.max(width, height);
            width = Math.round(width * ratio); height = Math.round(height * ratio);
        }
        let blob;
        for (let pass = 0; pass < 5; pass++) {
            const canvas = document.createElement('canvas');
            canvas.width = width; canvas.height = height;
            const context = canvas.getContext('2d', { alpha: false });
            context.fillStyle = '#fff'; context.fillRect(0, 0, width, height);
            context.drawImage(image, 0, 0, width, height);
            for (let quality = .88; quality >= .46; quality -= .07) {
                blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/webp', quality));
                if (!blob) blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
                if (blob && blob.size <= targetBytes) break;
            }
            if (blob && blob.size <= targetBytes) break;
            width = Math.round(width * .82); height = Math.round(height * .82);
        }
        image.close?.();
        if (!blob || blob.size > targetBytes) throw new Error('Foto belum dapat dioptimalkan otomatis. Coba pilih foto lain.');
        const extension = blob.type === 'image/webp' ? 'webp' : 'jpg';
        return new File([blob], file.name.replace(/\.[^.]+$/, '') + `.${extension}`, { type: blob.type, lastModified: Date.now() });
    }

    async function loadImageForCompression(file) {
        if ('createImageBitmap' in window) return createImageBitmap(file);
        return new Promise((resolve, reject) => {
            const image = new Image();
            const objectUrl = URL.createObjectURL(file);
            image.onload = () => { URL.revokeObjectURL(objectUrl); resolve(image); };
            image.onerror = () => { URL.revokeObjectURL(objectUrl); reject(new Error('Format foto ini belum didukung oleh browser.')); };
            image.src = objectUrl;
        });
    }

    const shareBlock = $('[data-share]');
    if (shareBlock) {
        const shareData = { title: shareBlock.dataset.title, text: `Coba resep ${shareBlock.dataset.title} dari DapurNda`, url: shareBlock.dataset.url };
        const performShare = async () => {
            if (navigator.share) await navigator.share(shareData).catch(() => {});
            else await copyShareLink();
        };
        $('[data-native-share]', shareBlock)?.addEventListener('click', performShare);
        $('[data-mobile-share]')?.addEventListener('click', performShare);
        $('[data-copy-link]', shareBlock)?.addEventListener('click', copyShareLink);
        async function copyShareLink() {
            await navigator.clipboard.writeText(shareData.url);
            const button = $('[data-copy-link]', shareBlock);
            const label = $('[data-copy-label]', button);
            if (label) { const old = label.textContent; label.textContent = 'Tautan tersalin'; setTimeout(() => { label.textContent = old; }, 1800); }
        }
    }

    const saveButtons = $$('[data-save-recipe]');
    if (saveButtons.length) {
        const key = 'dapurnda-saved-recipes';
        const saved = new Set(JSON.parse(localStorage.getItem(key) || '[]').map(String));
        const id = String(saveButtons[0].dataset.id);
        const refresh = () => saveButtons.forEach((button) => {
            button.classList.toggle('is-saved', saved.has(id));
            const label = $('span', button);
            if (label) label.textContent = saved.has(id) ? 'Tersimpan' : 'Simpan';
            button.setAttribute('aria-pressed', String(saved.has(id)));
        });
        refresh();
        saveButtons.forEach((button) => button.addEventListener('click', () => { saved.has(id) ? saved.delete(id) : saved.add(id); localStorage.setItem(key, JSON.stringify([...saved])); refresh(); }));
    }

    const recipeMobileBar = $('[data-recipe-mobile-bar]');
    if (recipeMobileBar) {
        const syncRecipeMobileBar = () => {
            const visible = scrollY > 260;
            document.body.classList.toggle('recipe-mobile-bar-visible', visible);
            recipeMobileBar.setAttribute('aria-hidden', String(!visible));
        };
        syncRecipeMobileBar();
        addEventListener('scroll', syncRecipeMobileBar, { passive: true });
    }

    const ingredientChecklist = $('[data-ingredient-list-check]');
    if (ingredientChecklist) {
        const checkboxes = $$('input[type="checkbox"]', ingredientChecklist);
        const completed = $('[data-ingredients-done]');
        const progress = $('[data-ingredient-bar]');
        const refreshIngredients = () => {
            const done = checkboxes.filter((checkbox) => checkbox.checked).length;
            if (completed) completed.textContent = String(done);
            if (progress) progress.style.width = `${checkboxes.length ? (done / checkboxes.length) * 100 : 0}%`;
        };
        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', refreshIngredients));
        refreshIngredients();
    }

    const cookingSteps = $('[data-cooking-steps]');
    if (cookingSteps) {
        const steps = $$('.cooking-step', cookingSteps);
        const completed = $('[data-steps-done]');
        const progress = $('[data-cooking-bar]');
        const reset = $('[data-reset-cooking]');
        const storageKey = `dapurnda-cooking:${location.pathname}${location.search}`;
        let savedSteps = [];
        try { savedSteps = JSON.parse(localStorage.getItem(storageKey) || '[]').map(Number); } catch (error) { savedSteps = []; }

        const refreshCooking = () => {
            const doneIndexes = [];
            steps.forEach((step, index) => {
                const isDone = step.classList.contains('is-done');
                const button = $('[data-step-check]', step);
                step.setAttribute('aria-current', isDone ? 'step' : 'false');
                button?.setAttribute('aria-pressed', String(isDone));
                const label = $('[data-step-check-label]', button);
                const hint = $('[data-step-check-hint]', button);
                if (label) label.textContent = isDone ? 'Langkah selesai' : 'Tandai sudah selesai';
                if (hint) hint.textContent = isDone ? 'Tersimpan di perangkat ini' : 'Sentuh setelah langkah ini selesai';
                if (isDone) doneIndexes.push(index);
            });
            if (completed) completed.textContent = String(doneIndexes.length);
            if (progress) progress.style.width = `${steps.length ? (doneIndexes.length / steps.length) * 100 : 0}%`;
            localStorage.setItem(storageKey, JSON.stringify(doneIndexes));
        };

        savedSteps.forEach((index) => steps[index]?.classList.add('is-done'));
        cookingSteps.addEventListener('click', (event) => {
            const button = event.target.closest('[data-step-check]');
            if (!button) return;
            button.closest('.cooking-step')?.classList.toggle('is-done');
            refreshCooking();
        });
        reset?.addEventListener('click', () => {
            steps.forEach((step) => step.classList.remove('is-done'));
            refreshCooking();
            steps[0]?.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'center' });
        });
        refreshCooking();
    }

    const placeModalAtDocumentRoot = (modal) => {
        if (modal.parentElement !== document.body) document.body.appendChild(modal);
    };

    const userEditorModals = $$('[data-user-editor-modal]');
    userEditorModals.forEach(placeModalAtDocumentRoot);
    userEditorModals.forEach((modal) => modal.addEventListener('shown.bs.modal', () => $('input[name="username"]', modal)?.focus()));
    const autoOpenUserModal = userEditorModals.find((modal) => modal.dataset.autoOpen === 'true');
    if (autoOpenUserModal && window.bootstrap?.Modal) window.bootstrap.Modal.getOrCreateInstance(autoOpenUserModal).show();

    const sponsorEditorModals = $$('[data-sponsor-editor-modal]');
    sponsorEditorModals.forEach(placeModalAtDocumentRoot);
    sponsorEditorModals.forEach((modal) => modal.addEventListener('shown.bs.modal', () => $('input[name="name"]', modal)?.focus()));
    const autoOpenSponsorModal = sponsorEditorModals.find((modal) => modal.dataset.autoOpen === 'true');
    if (autoOpenSponsorModal && window.bootstrap?.Modal) window.bootstrap.Modal.getOrCreateInstance(autoOpenSponsorModal).show();

    $$('[data-confirm]').forEach((button) => button.addEventListener('click', (event) => {
        if (!confirm(button.dataset.confirm)) event.preventDefault();
    }));
    $$('[data-auto-submit]').forEach((field) => field.addEventListener('change', () => field.form?.requestSubmit()));
})();
