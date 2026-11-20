-- Jalankan sekali pada database DapurNda lama melalui phpMyAdmin.
ALTER TABLE recipes
    ADD COLUMN contributor_contact VARCHAR(160) NULL AFTER author_name,
    ADD COLUMN publishing_consent_at TIMESTAMP NULL AFTER contributor_contact;

ALTER TABLE recipe_ingredients ADD UNIQUE INDEX uq_ingredients_recipe_order (recipe_id, sort_order);
ALTER TABLE recipe_steps ADD UNIQUE INDEX uq_steps_recipe_order (recipe_id, sort_order);
