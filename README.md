# Fornesus Art

Personal art archive — PHP + MySQL, no framework.

## Development

```bash
php -S localhost:8000 -t public public/index.php
```

The router argument is required; without it PHP's built-in server won't handle clean URLs. The site runs at `http://localhost:8000`.

## Requirements

- PHP 8.1+
- MySQL 8+ or MariaDB 10.6+
- Apache with `mod_rewrite` (or Nginx with equivalent `try_files` config)

## Setup

### 1. Clone and configure

```bash
cp env.example .env
```

Fill in `.env`:

```
DB_HOST=your_host
DB_NAME=your_db
DB_USER=your_user
DB_PASS=your_password
ADMIN_PASSWORD_HASH=...
```

Generate the admin password hash:

```bash
php -r 'echo password_hash("yourpassword", PASSWORD_BCRYPT) . PHP_EOL;'
```

### 2. Create the database

**Fresh install** — import `schema.sql`:

```bash
mysql -h HOST -u USER -p DB_NAME < schema.sql
```

**Existing database** — run migrations in order:

| File | When to run |
|---|---|
| `migrate_phase2.sql` | Adds categories / exhibits schema |
| `migrate_phase4_blob.sql` | Adds `data` + `mime_type` columns to `media_files` |
| `migrate_phase4_cleanup.sql` | Drops legacy `path` / `subfolder` columns (run after verifying blob migration) |
| `migrate_phase6_pages.sql` | Creates `pages` + `page_sections`; seeds Bio and Contact pages |
| `migrate_pages_softdelete.sql` | Adds `deleted_at` to `pages` for soft delete / trash |

Run each via your MySQL client or PHP PDO:

```bash
mysql -h HOST -u USER -p DB_NAME < migrate_pages_softdelete.sql
```

### 3. Point the web server

Set `DocumentRoot` to the `public/` directory. If your server root is the project root, the root `.htaccess` redirects to `public/` automatically.

### 4. Log in

Visit `/admin/login`.

---

## Changing the admin password

```bash
php -r 'echo password_hash("newpassword", PASSWORD_BCRYPT) . PHP_EOL;'
```

Replace `ADMIN_PASSWORD_HASH` in `.env`. No database changes needed.

---

## Architecture

Plain PHP with a front-controller router. No ORM, no framework. Request flow:

```
public/index.php  →  regex router  →  Controller::method()
                                            ↓
                                       Model (static PDO)
                                            ↓
                                       require view.php
```

Admin authentication is a single bcrypt-hashed password stored in `.env`, checked via `admin_check()` on every admin action.

The public layout centralizes SEO and accessibility concerns in one place:

- canonical URLs, meta descriptions, Open Graph, Twitter, and optional `robots` tags
- `og:image:alt` / `twitter:image:alt` support for richer page previews
- skip link, visible focus states, and `aria-current` navigation states
- progressive enhancement hooks so gallery/work toggles stay readable without JavaScript
- low-power / reduced-motion celestial fallbacks that quiet decorative motion automatically

---

## File structure

```
fornesusart/
├── public/                         Web root — point DocumentRoot here
│   ├── index.php                   Front controller and regex router
│   ├── .htaccess                   Rewrites all requests to index.php
│   └── assets/
│       ├── css/
│       │   ├── style.css           Public site styles (Celestial Archive)
│       │   ├── admin.css           Admin panel styles
│       │   └── tiptap.css          Rich text editor + media picker modal styles
│       ├── js/
│       │   ├── main.js             Drag-and-drop reorder, slug auto-fill, gallery toggle
│       │   ├── cosmos.js           Ambient celestial background animation
│       │   └── tiptap-editor.js    Tiptap editor init, media picker, link/image popovers
│       └── fonts/                  Self-hosted Lora, Pinyon Script, Courier Prime (.woff2)
│
├── app/
│   ├── bootstrap.php               Env load, session start, class autoloader
│   ├── config/
│   │   └── database.php            PDO singleton — db() function
│   ├── controllers/
│   │   ├── AdminController.php     All /admin/* routes
│   │   ├── GalleryController.php   / — gallery with exhibit strip
│   │   ├── WorkController.php      /work/[slug]
│   │   ├── PageController.php      Managed pages, /contact form, /about redirect
│   │   ├── AboutController.php     Legacy /about fallback + contact handling when managed pages are unavailable
│   │   ├── CategoriesController.php  /categories and /category/[slug]
│   │   ├── ExhibitController.php   /exhibit/[slug]
│   │   └── ImageController.php     /image/[id] — serves blobs from DB
│   ├── models/
│   │   ├── Artwork.php             Soft delete, category join, slug
│   │   ├── Category.php            Soft delete, thumbnail, slug
│   │   ├── Exhibit.php             Soft delete, thumbnail, artwork sync
│   │   ├── MediaFile.php           LONGBLOB storage — create(data, mime), getData()
│   │   ├── BioSection.php          Legacy About/Bio fallback sections
│   │   ├── Page.php                Soft delete, nav toggle, slug validation
│   │   └── PageSection.php         Ordered sections within a page
│   ├── views/
│   │   ├── layout.php              Public shared header/footer + SEO metadata + celestial background
│   │   ├── gallery.php             /
│   │   ├── work.php                /work/[slug]
│   │   ├── about.php               Legacy About/contact fallback template
│   │   ├── page.php                /bio, /contact, /[slug] — renders page + sections
│   │   ├── categories.php          /categories
│   │   ├── category.php            /category/[slug]
│   │   ├── exhibit.php             /exhibit/[slug]
│   │   ├── 404.php
│   │   └── admin/
│   │       ├── layout.php          Admin shell — nav, importmap, Tiptap module, media picker modal
│   │       ├── login.php
│   │       ├── dashboard.php
│   │       ├── messages.php
│   │       ├── media.php           Media library — grid, asset details, + New Image button
│   │       ├── trash.php           Recycle bin (artworks / categories / exhibits / media)
│   │       ├── artworks/
│   │       │   ├── index.php       Drag-reorder list
│   │       │   └── form.php        Create + edit — Tiptap description, media picker thumbnail + piece
│   │       ├── categories/
│   │       │   ├── index.php
│   │       │   └── form.php        Tiptap description, media picker thumbnail
│   │       ├── exhibits/
│   │       │   ├── index.php
│   │       │   └── form.php        Tiptap description, media picker thumbnail, artwork checklist
│   │       └── pages/
│   │           ├── index.php       Eye-icon nav toggle, trash count, drag-reorder
│   │           ├── form.php        Page metadata + SEO + sections list
│   │           ├── section-form.php  Tiptap content editor
│   │           └── trash.php       Pages-specific recycle bin (separate from /admin/trash)
│   └── helpers/
│       ├── auth.php                admin_check(), admin_login(), admin_logout()
│       ├── seo.php                 seo_excerpt(), seo_absolute_url()
│       ├── upload.php              MIME-validated blob upload → returns /image/{id}
│       └── slugify.php             slugify(), unique_slug()
│
├── docs/
│   └── dependencies.md             External dependency register
│
├── schema.sql                      Full schema for fresh installs
├── migrate_phase2.sql              Adds categories / exhibits tables
├── migrate_phase4_blob.sql         Adds blob columns to media_files
├── migrate_phase4_cleanup.sql      Drops legacy path/subfolder columns
├── migrate_phase6_pages.sql        Creates pages + page_sections; seeds Bio + Contact
├── migrate_pages_softdelete.sql    Adds deleted_at to pages
├── migrate_images_to_blob.php      One-time CLI script — filesystem images → DB blobs
├── env.example
└── .env                            Local config — never committed
```

---

## URLs

### Public

| URL | Description |
|---|---|
| `/` | Gallery — exhibit strip + works grid |
| `/categories` | All categories |
| `/category/[slug]` | Category with artworks |
| `/exhibit/[slug]` | Exhibit with artworks |
| `/work/[slug]` | Individual artwork |
| `/about` | Legacy route: redirects to `/bio` when managed pages are available; otherwise renders the fallback About/contact page |
| `/bio` | Managed biography page |
| `/contact` | Managed contact page with editable intro + contact form; falls back through the legacy About flow if the Pages migration has not been applied yet |
| `/[slug]` | Any other managed page |
| `/image/[id]` | Serves an image blob from the database |

### Admin

| URL | Description |
|---|---|
| `/admin` | Dashboard — stat cards for works, categories, exhibits, messages, trash |
| `/admin/artworks` | Manage artworks — drag-reorder, soft delete |
| `/admin/categories` | Manage categories |
| `/admin/exhibits` | Manage exhibits + artwork assignment |
| `/admin/pages` | Manage pages — drag-reorder, eye-icon nav toggle, soft delete |
| `/admin/pages/trash` | Pages-specific recycle bin (restore / delete forever) |
| `/admin/messages` | Contact form submissions |
| `/admin/media` | Media library — grid, asset details, + New Image (upload/import) |
| `/admin/trash` | Recycle bin for artworks, categories, exhibits, media |

---

## Media storage

All images are stored as LONGBLOBs in the `media_files` table and served via `/image/[id]` with `ETag` and `Cache-Control: immutable` headers. There is no uploads directory.

The **Media Library modal** (available everywhere in the admin) has three tabs:

- **Select** — choose from existing library images; includes optional alt text field
- **Upload** — drag-and-drop or file picker; client-side 8 MB / MIME validation before POST
- **Import** — paste a URL; server fetches, validates (8 MB, JPEG/PNG/GIF/WebP/AVIF), and stores the image

---

## Rich text editor

Content fields (page section content, artwork / category / exhibit descriptions) use the [Tiptap](https://tiptap.dev/) rich text editor loaded via ES module imports from `esm.sh`. No build step required.

**Toolbar**: headings, font family, font size, bold, italic, underline, strikethrough, text colour, highlight, horizontal rule, link (with title), insert image from library, insert iframe embed, HTML source toggle.

**Image editing**: a pencil icon appears at the bottom-right corner of each image on hover. Clicking it opens an inline popover for alt text editing.

**Link editing**: a small pencil icon appears to the right of any link the cursor is inside. Clicking it opens a floating popover with URL and title fields.

**HTML source mode**: a `HTML` toolbar button reveals the raw HTML in an editable textarea. All other controls are disabled while in source mode.

Content is stored as HTML in `TEXT` columns and rendered as raw HTML on the public site.

See `docs/dependencies.md` for CDN details and self-hosting instructions.

---

## Public-site behavior

- Typography: public reading text uses `Lora`; display headings use `Pinyon Script`; mono/admin metadata uses `Courier Prime`.
- Atmosphere: the celestial background combines three low-opacity nebula washes, a slow astrolabe grid, and lightweight stars/comets. On reduced-motion or low-power devices, decorative effects scale back automatically.
- Progressive enhancement: the homepage "See More" control and long work-description expand/collapse behavior only activate when JavaScript is available; content stays fully readable without JS.
- Accessibility: public templates use skip links, labelled sections, descriptive card links, decorative `aria-hidden` markers, responsive touch targets, and contact-form status/error messaging.
- Metadata: page-level SEO/social tags are emitted centrally from the public layout, with dynamic canonical URLs and optional social preview image alt text.

---

## License

MIT
