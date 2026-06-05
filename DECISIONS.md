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

---

## Phase 4 — Claude Code (2026-06-04)

### Blob Storage Migration

Images are now stored directly in the database as LONGBLOBs rather than on the filesystem. The `media_files` table previously stored file paths (`path`, `subfolder`); those columns have been dropped. All image serving goes through `/image/[id]`.

**Motivation:** Simplify the stack — no filesystem dependency for media, no uploads directory to manage or back up separately, no path-reference synchronisation needed.

### Schema Changes
- `media_files`: added `data LONGBLOB NULL`, `mime_type VARCHAR(50) NULL`; dropped `path` and `subfolder`
- Migration files: `migrate_phase4_blob.sql` (adds columns), `migrate_phase4_cleanup.sql` (drops legacy columns)
- Data migration: `migrate_images_to_blob.php` — reads files from `public/uploads/`, writes BLOBs to DB, rewrites path references in `artworks`, `categories`, `exhibits`

### New Route
- `GET /image/[id]` — served by `ImageController::serve()`; returns binary blob with correct `Content-Type`, `ETag`, and `Cache-Control: immutable` headers. Returns 404 if blob is NULL, deleted, or id invalid.

### Files Created (Phase 4)
- `app/controllers/ImageController.php` — blob image serving
- `migrate_phase4_blob.sql` — schema migration (adds columns)
- `migrate_phase4_cleanup.sql` — cleanup migration (drops `path`, `subfolder`)
- `migrate_images_to_blob.php` — one-time data migration CLI script

### Files Updated (Phase 4)
- `app/models/MediaFile.php` — `create()` now takes `(string $data, string $mimeType)`; `getData()` method added; all listing queries exclude blob column; `hardDelete()` no longer touches filesystem
- `app/helpers/upload.php` — stores blob via `MediaFile::create()`; returns `/image/{id}`; `SET SESSION max_allowed_packet` wrapped in try/catch (MariaDB 11+ made it read-only at session level)
- `app/views/admin/media.php` — thumbnails via `/image/{id}`; filename display replaced with ID + mime type (path column gone)
- `app/views/admin/trash.php` — removed references to `path` and `subfolder` columns
- `public/index.php` — added `cli-server` static file handler (required for PHP built-in server to serve CSS/JS); added `/image/([0-9]+)` route
- `public/assets/css/admin.css` — responsive media grid (single column mobile, two columns ≥768px); button `appearance: none` override for macOS/Safari native rendering
- `app/controllers/AdminController.php` — dashboard now includes `$exhibitCount`
- `app/views/admin/dashboard.php` — Exhibits stat card added

### Operational Notes
- Remote server is **MariaDB 11.8.6**, not MySQL. Use `--column-statistics=0` with `mysqldump` from MySQL client 8.x.
- `max_allowed_packet` on the server is 1 GB; the session-level SET is unnecessary and errors on MariaDB 11+.
- `public/uploads/categorys/` and `public/uploads/thumbnails/` deleted from disk after migration.
- PHP built-in dev server requires the router argument: `php -S localhost:8000 -t public public/index.php`

### Unresolved Items Entering Phase 5
- [x] Media Library UI: button styling not applying in Safari (appearance: none served correctly but not rendering; under active investigation)
- [x] Media Library UI: fuller redesign deferred — type labels, sort, context ("used by"), original filename storage

---

## Phase 5 — Antigravity (2026-06-04)

### New URL Structure Confirmed
- `POST /admin/media/upload` — Direct media file upload endpoint

### Layout & Formatting Decisions
- Redesigned the Media Library into a dual-pane "Darkroom Gallery" split-view workspace:
  - Left pane: Spacious grid of aspect-ratio 1:1 square image tiles (140px minimum width) with subtle grayscale filters, spotlight vignette overlays, and zoom animations on hover.
  - Right pane: Sticky details panel with a radial gradient preview canvas displaying ID, MIME type, upload timestamp, and action buttons.
  - Added a drag-and-drop / file upload zone at the top of the grid with display headings.
  - Responsive alignment: Dual-pane split on screens ≥ 900px, stacking vertically on smaller screens.
  - Expanded layout: Overrode `.admin-main` container max-width to `1200px` on this page to provide optimal grid space.
- CSS Cache-buster: Added dynamic `filemtime` query cache busters to style sheets in `app/views/admin/layout.php` to force instant client updates.

### Button Style & Compatibility Decisions
- Standardized copyable inputs: Enclosed URLs and HTML tags in a `.media-code-input-wrap` that joins the input field and a left-bordered Copy button with no gap, sharing a border and aligning to the exact pixel.
- Unified action buttons: Standardized full-height `.admin-btn` (gold) and `.admin-btn-danger` (crimson) outline buttons to stretch to the exact dimensions of the details forms.
- Applied `-webkit-appearance: none` and `border-radius: 0` to all button styles, satisfying the "no rounded corners" design constraint and resolving native Safari rendering inconsistencies.
- Cleaned up Recycle Bin count badge styles to use `border-radius: 0`.

### Files Created (Phase 5)
- None

### Files Updated (Phase 5)
- `public/index.php` — Registered the POST route for `/admin/media/upload`
- `app/controllers/AdminController.php` — Implemented `mediaUpload()` method
- `app/views/admin/layout.php` — Added `filemtime`-based cache busters to CSS stylesheets
- `app/views/admin/media.php` — Entirely rewritten to implement the split view layout and clipboard copying / drop zone logic
- `public/assets/css/admin.css` — Standardized danger button classes and designed split-workspace, tiles, and upload-zone styles

### Vendor Dependencies Added
- None

### Environment Variables Required
- None

### Unresolved Items Entering Phase 6
- [ ] Decide whether to add email notifications for contact form submissions (carried over)

---

## Phase 6 — Codex (2026-06-04)

### Admin UI Decisions
- Reframed the Media Library from the earlier "Darkroom Gallery" split-view treatment into an admin-native asset manager so it inherits the same typography, controls, and hierarchy as the rest of `/admin/*`.
- Standardized the shared admin header/navigation to read as administrative chrome rather than public-site navigation:
  - Added explicit active-link states with `aria-current="page"`
  - Shifted branding/navigation emphasis toward the existing metadata-style mono system
  - Preserved all existing admin routes and labels
- Added a page-level wide-content mode for the Media Library via `.admin-main-wide` instead of inline per-view CSS overrides.

### Media Library Layout & Interaction Decisions
- Rebuilt the Media Library into a grid-first workspace:
  - Asset cards now show thumbnail, asset ID, MIME type, and upload date
  - Selected asset details remain available in a secondary panel
  - Upload zone remains drag-and-drop and click-to-browse
  - Trash and permanent delete remain confirmation-gated
- Replaced one-off inline success styling with a reusable `.admin-notice` pattern.
- Updated copy controls to use the shared admin button/form language while preserving direct URL and HTML embed copying.
- Added keyboard activation for the upload zone (`Enter` / `Space`) to improve accessibility.

### Admin Layout Bugfix
- Corrected multiple admin view layout includes so admin pages consistently render through `app/views/admin/layout.php` rather than accidentally resolving toward the public layout path.
- Verified the corrected admin chrome rendering on representative routes:
  - `/admin`
  - `/admin/artworks`
  - `/admin/media`
  - `/admin/trash`

### Files Updated (Phase 6)
- `app/views/admin/layout.php` — active nav state, admin branding block, configurable body/main classes
- `app/views/admin/media.php` — grid-first Media Library markup, reusable notice usage, wide main mode, keyboard-friendly upload zone
- `public/assets/css/admin.css` — admin chrome refresh, wide main variant, media-card grid system, upload/details panel styling
- `app/views/admin/dashboard.php` — corrected admin layout include path
- `app/views/admin/trash.php` — corrected admin layout include path
- `app/views/admin/artworks/index.php`, `form.php` — corrected admin layout include paths
- `app/views/admin/categories/index.php`, `form.php` — corrected admin layout include paths
- `app/views/admin/exhibits/index.php`, `form.php` — corrected admin layout include paths
- `app/views/admin/bio/index.php`, `form.php` — corrected admin layout include paths

### Verification Notes
- PHP syntax checks passed for the updated admin layout and representative admin views.
- Direct-render verification confirmed:
  - admin body/nav markup is present
  - active nav states render on current admin pages
  - Media Library renders with `admin-main-wide`
  - Media asset cards render in the expected grid markup

---

## Phase 7 — Codex (2026-06-04)

### Public Design Decisions
- Kept the public "Celestial Archive" identity, but shifted the primary reading/content font to `Lora` for readability across the site.
- Strengthened public accessibility basics in the shared layout and stylesheet:
  - central metadata rendering in the public layout
  - canonical URLs, descriptions, Open Graph, and Twitter metadata support
  - skip link and stronger focus-visible treatment
  - reduced-motion handling for decorative atmosphere
- Expanded the ambient background with an additional low-opacity nebula layer in CSS while keeping the existing stars/cosmos treatment lightweight.

### Pages System Decisions
- Replaced the one-off `bio_sections` concept with a reusable Pages system:
  - `pages` for page identity, template, nav behavior, and SEO/social metadata
  - `page_sections` for ordered sections within each page
- Split the old `/about` concept into:
  - `/bio` — managed standard page
  - `/contact` — managed page with editable intro sections plus the fixed contact form
- Added legacy compatibility by redirecting `GET /about` to `/bio` and accepting `POST /about` through the contact submission handler.
- Reserved `/contact` for the contact-template page so the built-in form remains coherent with its public route.

### Admin Content Decisions
- Replaced the admin “Bio” entry point with “Pages” in the shared admin navigation.
- Added a Pages portal in admin for:
  - page creation and editing
  - SEO/social metadata management
  - ordered section management per page
  - page deletion and page ordering
- Kept admin visuals broadly intact while extending the current CRUD pattern rather than redesigning the admin area.

### Files Updated (Phase 7)
- `public/index.php` — page routes, `/about` redirect, `/contact` POST handler, new model/helper/controller requires
- `app/controllers/PageController.php` — managed page rendering, contact form handling, `/about` redirect
- `app/controllers/AdminController.php` — Pages CRUD and page-section CRUD/reorder
- `app/models/Page.php`, `PageSection.php` — page identity/navigation and section ordering
- `app/views/layout.php` — centralized metadata tags, skip link, managed-page nav
- `app/views/page.php` — shared managed page template with optional contact section
- `app/views/gallery.php`, `work.php`, `categories.php`, `category.php`, `exhibit.php`, `404.php` — explicit metadata variables and public layout cleanup
- `app/views/admin/pages/index.php`, `form.php`, `section-form.php` — admin Pages interface
- `public/assets/css/style.css` — `Lora` reading system, nebula layer, accessibility/responsive improvements
- `public/assets/js/cosmos.js` — reduced-motion-aware lighter atmosphere behavior
- `public/assets/js/main.js` — page slug auto-fill support
- `schema.sql`, `migrate_phase6_pages.sql` — page system schema and migration
- `docs/dependencies.md`, `README.md` — updated font dependency and architecture docs


<!-- Add a new dated section at the start of each phase following
     the same pattern. Resolved checkpoints from the prior phase
     should be marked [x] and left in place — do not delete them.
     They are the audit trail. If empty, begin with Phase 1. -->
