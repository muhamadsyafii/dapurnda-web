/**
 * DapurNda secure Sneat runtime.
 * Keeps the official Sneat layout interactive without requiring unsafe-eval.
 */
(() => {
  'use strict';

  const root = document.documentElement;
  const runtimeScript = document.currentScript;
  const menuEnabled = runtimeScript?.dataset.menu === 'true';
  const mobileBreakpoint = 1200;
  const sidebarStorageKey = 'dapurnda-admin-sidebar-collapsed';
  const sidebarToggle = document.querySelector('.dapurnda-sidebar-toggle');

  const getStoredSidebarState = () => {
    try {
      return localStorage.getItem(sidebarStorageKey) === 'true';
    } catch {
      return false;
    }
  };

  const storeSidebarState = (collapsed) => {
    try {
      localStorage.setItem(sidebarStorageKey, String(collapsed));
    } catch {
      // The layout still works when storage is blocked by the browser.
    }
  };

  const updateSidebarToggle = () => {
    if (!sidebarToggle) return;
    const isDesktop = window.innerWidth >= mobileBreakpoint;
    const collapsed = root.classList.contains('layout-menu-collapsed');
    const expanded = isDesktop ? !collapsed : root.classList.contains('layout-menu-expanded');
    sidebarToggle.setAttribute('aria-expanded', String(expanded));
    sidebarToggle.setAttribute('aria-label', isDesktop && !collapsed ? 'Ciutkan menu samping' : 'Buka menu samping');
    sidebarToggle.classList.toggle('is-collapsed', isDesktop && collapsed);
  };

  const closeMenu = () => {
    root.classList.remove('layout-menu-expanded');
  };

  if (menuEnabled) {
    if (window.innerWidth >= mobileBreakpoint && getStoredSidebarState()) {
      root.classList.add('layout-menu-collapsed');
    }
    updateSidebarToggle();

    sidebarToggle?.addEventListener('click', () => {
      if (window.innerWidth >= mobileBreakpoint) {
        const collapsed = root.classList.toggle('layout-menu-collapsed');
        storeSidebarState(collapsed);
      } else {
        root.classList.toggle('layout-menu-expanded');
      }
      updateSidebarToggle();
    });

    document.querySelectorAll('.layout-menu-toggle').forEach((toggle) => {
      toggle.addEventListener('click', (event) => {
        event.preventDefault();
        root.classList.toggle('layout-menu-expanded');
        updateSidebarToggle();
      });
    });

    document.querySelectorAll('#layout-menu .menu-link').forEach((link) => {
      link.addEventListener('click', () => {
        if (window.innerWidth < mobileBreakpoint) {
          closeMenu();
          updateSidebarToggle();
        }
      });
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth >= mobileBreakpoint) {
        closeMenu();
        root.classList.toggle('layout-menu-collapsed', getStoredSidebarState());
      } else {
        root.classList.remove('layout-menu-collapsed');
      }
      updateSidebarToggle();
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeMenu();
        updateSidebarToggle();
      }
    });
  }

  document.querySelectorAll('.form-password-toggle').forEach((field) => {
    const input = field.querySelector('input');
    const button = field.querySelector('.input-group-text');
    const icon = button?.querySelector('i');

    if (!input || !button) return;

    button.setAttribute('role', 'button');
    button.setAttribute('tabindex', '0');
    button.setAttribute('aria-label', 'Tampilkan kata sandi');

    const togglePassword = () => {
      const willShow = input.type === 'password';
      input.type = willShow ? 'text' : 'password';
      button.setAttribute('aria-label', willShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
      button.setAttribute('aria-pressed', String(willShow));
      icon?.classList.toggle('bx-hide', !willShow);
      icon?.classList.toggle('bx-show', willShow);
    };

    button.addEventListener('click', togglePassword);
    button.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        togglePassword();
      }
    });
  });
})();
