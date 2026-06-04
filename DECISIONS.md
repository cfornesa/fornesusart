# Decisions
<!-- IMPORTANT: Load CONSTRAINTS.md and DESIGN.md alongside this
file at every session start. Constraints listed in CONSTRAINTS.md are binding regardless of what is recorded here. Design identity in DESIGN.md informs all gallery
options regardless of session context. -->

## Project Profile

<!-- Operational details for this project. Kept here, not in AGENTS.md,
     to keep the root instruction file framework-agnostic and safe to
     publish. Do not put credentials, hostnames, file paths, or API
     keys here — those belong in .env.

     An agent fills this section during Phase 1 by asking the person
     plain-language questions. If this section is empty, ask before
     writing any code. See AGENTS.md → Detect the Framework. -->

- **Stack:** Bare PHP (no framework), MySQL, PDO
- **Deployment:** Apache/Nginx, `public/` as DocumentRoot. URL rewriting via `.htaccess`.
- **Database:** MySQL via PDO. No ORM.
- **Version pins:** PHP 8.1+, MySQL 8+
- **Framework AGENTS.md:** No framework sub-file — root AGENTS.md only.
- **Profile switch rule:** Stop before touching existing files. Record
  current state and reason here. Confirm new profile explicitly. Flag
  every file needing migration before starting.

---

## REVIEW REQUIRED — Read before starting next session
- [x] 2026-06-04 Claude Code. URL structure confirmed via plan approval: `/`, `/about`, `/work/[slug]`, `/admin/*`
- [x] 2026-06-04 Claude Code. Google Fonts CDN dependency disclosed and accepted; documented in `docs/dependencies.md`

---

## Phase 1 — Claude Code (2026-06-04)

### Stack Confirmed
- Bare PHP, no framework
- Front controller in `public/index.php` with regex router
- MVC-lite pattern: controllers → views via `require`; models via static PDO methods
- Admin auth: single bcrypt-hashed password in `.env`, session-based
- URL rewriting via `.htaccess` (Apache) — Nginx equivalent: `try_files $uri $uri/ /index.php`

### Schema and Data Decisions
- `artworks` table: `piece_type` ENUM discriminator (`image_upload`, `image_link`, `embed`); `thumbnail_type` ENUM (`upload`, `link`)
- `thumbnail_value` / `piece_value`: stores relative file path (uploads) or URL or raw iframe HTML
- `categories` → `artworks` foreign key with `ON DELETE SET NULL` (uncategorised works remain visible)
- `bio_sections`: `heading` nullable (NULL = opening paragraph, rendered without a heading element)
- `contact_messages`: stored in DB; viewable in admin at `/admin/messages`
- Auto-slug derived from title at creation; slug not changed on subsequent edits to protect URLs

### Files Created
- `schema.sql` — database schema
- `public/index.php` — front controller + regex router
- `public/.htaccess` — rewrite all to index.php
- `public/uploads/.htaccess` — block PHP execution in uploads directory
- `.htaccess` (root) — redirect to `public/` when DocumentRoot is project root
- `.gitignore` — excludes `.env`, uploaded files
- `app/bootstrap.php` — env loader, session start, autoloader
- `app/config/database.php` — PDO singleton via `db()` function
- `app/helpers/auth.php` — `admin_check()`, `admin_login()`, `admin_logout()`
- `app/helpers/upload.php` — MIME-validated file upload via magic bytes
- `app/helpers/slugify.php` — `slugify()`, `unique_slug()`, `unique_category_slug()`
- `app/models/Artwork.php`, `Category.php`, `BioSection.php` — static PDO models
- `app/controllers/GalleryController.php`, `WorkController.php`, `AboutController.php`, `AdminController.php`
- `app/views/layout.php`, `gallery.php`, `work.php`, `about.php`, `404.php`
- `app/views/admin/layout.php`, `login.php`, `dashboard.php`
- `app/views/admin/artworks/index.php`, `form.php`
- `app/views/admin/categories/index.php`
- `app/views/admin/bio/index.php`
- `app/views/admin/messages.php`
- `public/assets/css/style.css` — Celestial Archive design
- `public/assets/css/admin.css` — admin panel styles
- `public/assets/js/main.js` — admin toggle panels
- `docs/dependencies.md` — dependency register
- `env.example` — updated with DB and admin vars

### Vendor Dependencies Added
- Google Fonts CDN (Cinzel Decorative, IM Fell English, Courier Prime) — off-domain (logs visitor IPs); self-hosting alternative: download `.woff2` files to `public/assets/fonts/`. Documented in `docs/dependencies.md`.

### Environment Variables Required
- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `ADMIN_PASSWORD_HASH` — bcrypt hash; generate with: `php -r "echo password_hash('yourpassword', PASSWORD_BCRYPT);"`

### Gaps and Deferred Items
- Email delivery for contact form not implemented (messages stored in DB only)
- No rate limiting on contact form or admin login (deferred)
- Nginx rewrite config not written (Apache `.htaccess` only)

### Unresolved Checkpoints Entering Phase 2
- [ ] Decide whether to add email notifications for contact form submissions

---

## Phase 2 — Claude Code (2026-06-04)

### New URL Structure Confirmed
- `/categories` — public categories listing
- `/category/[slug]` — individual category page
- `/exhibit/[slug]` — individual exhibit page
- `/admin/categories/create`, `/admin/categories/[id]/edit` — category full CRUD (replaces inline table editing)
- `/admin/exhibits`, `/admin/exhibits/create`, `/admin/exhibits/[id]/edit` — exhibits CRUD

### Schema and Data Decisions
- `categories` table: added `thumbnail_type ENUM('upload','link')`, `thumbnail_value VARCHAR(500)`, `description TEXT` columns (nullable — categories without thumbnails/descriptions are valid)
- `exhibits` table (new): same thumbnail pattern as categories; `sort_order` for drag-to-reorder
- `exhibit_artworks` junction table (new): many-to-many between exhibits and artworks; composite primary key `(exhibit_id, artwork_id)`; `ON DELETE CASCADE` on both foreign keys
- Artworks can belong to multiple exhibits simultaneously
- Exhibit artwork assignment stored in `exhibit_artworks`; synced on each edit (delete-then-reinsert)
- Gallery restructured: flat `allSorted()` query replaces grouped-by-category query; first 3 works shown, rest toggled via JS

### Files Created (Phase 2)
- `migrate_phase2.sql` — migration for existing databases
- `app/models/Exhibit.php` — full exhibit model including `syncArtworks()`
- `app/controllers/CategoriesController.php` — public categories listing and detail
- `app/controllers/ExhibitController.php` — public exhibit detail
- `app/views/categories.php` — /categories
- `app/views/category.php` — /category/[slug]
- `app/views/exhibit.php` — /exhibit/[slug]
- `app/views/admin/exhibits/index.php`, `form.php`
- `app/views/admin/categories/form.php` — new dedicated create/edit form replacing inline table

### Files Updated (Phase 2)
- `schema.sql` — includes exhibits and exhibit_artworks tables; categories has new columns
- `app/models/Category.php` — thumbnail/description fields; `findBySlug()`, `artworks()` methods
- `app/models/Artwork.php` — `allSorted()` method; `category_slug` added to all JOIN queries
- `app/controllers/GalleryController.php` — passes `$exhibits` and `$artworks` (flat)
- `app/controllers/AdminController.php` — category full CRUD with thumbnail; exhibits full CRUD; shared `resolveThumbnail()` helper
- `app/views/layout.php` — nav: Gallery · Categories · About
- `app/views/gallery.php` — exhibits strip + flat Works section with See More
- `app/views/work.php` — category rendered as link to `/category/[slug]`
- `app/views/admin/layout.php` — Exhibits added to nav
- `app/views/admin/categories/index.php` — read-only list with Edit links (inline editing removed)
- `public/index.php` — all new routes added
- `public/assets/css/style.css` — exhibits strip, collection pages, See More button
- `public/assets/css/admin.css` — exhibit artwork checklist
- `public/assets/js/main.js` — See More toggle; generic slug auto-fill for categories and exhibits

### Vendor Dependencies Added
- None

### Environment Variables Required (cumulative)
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- `ADMIN_PASSWORD_HASH`

### Gaps and Deferred Items
- Email delivery for contact form still not implemented
- Exhibit artwork checklist has no search/filter (may be unwieldy with many artworks)

### Unresolved Checkpoints Entering Phase 3
- [ ] Decide whether to add email notifications for contact form submissions

---

<!-- Add a new dated section at the start of each phase following
     the same pattern. Resolved checkpoints from the prior phase
     should be marked [x] and left in place — do not delete them.
     They are the audit trail. If empty, begin with Phase 1. -->