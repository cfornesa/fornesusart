# fornesusart

Personal art gallery site. PHP + MySQL, no framework.

## Development

```bash
php -S localhost:8000 -t public public/index.php
```

The router script argument is required — without it, PHP's built-in server won't handle clean URLs. The site will be at `http://localhost:8000`.

## Requirements

- PHP 8.1+
- MySQL 8+ or MariaDB 10.6+
- Apache with `mod_rewrite` (or Nginx with equivalent `try_files` config)

## Setup

1. Clone the repo and copy the environment file:
   ```bash
   cp env.example .env
   ```

2. Fill in your MySQL credentials in `.env`:
   ```
   DB_HOST=localhost
   DB_NAME=fornesusart
   DB_USER=your_db_user
   DB_PASS=your_db_password
   ```

3. Generate an admin password hash and add it to `.env`:
   ```bash
   php -r 'echo password_hash("yourpassword", PASSWORD_BCRYPT) . PHP_EOL;'
   ```
   Paste the output as `ADMIN_PASSWORD_HASH` in `.env`. This is only for the admin panel — it has nothing to do with your database credentials.

4. Create the database and run the schema:
   - **Fresh install:** Import `schema.sql` via phpMyAdmin (Import tab) or your MySQL client.
   - **Existing database (pre-Phase 4):** Run migrations in order:
     1. `migrate_phase2.sql` — adds categories/exhibits schema (skip if already on Phase 2+)
     2. `migrate_phase4_blob.sql` — adds `data` and `mime_type` columns to `media_files`
     3. Run `php -d memory_limit=256M migrate_images_to_blob.php` to move existing files to the database
     4. `migrate_phase4_cleanup.sql` — drops the legacy `path` and `subfolder` columns (run last, after verifying the script output)

5. Point your web server's `DocumentRoot` to the `public/` directory. If your server is configured at the project root instead, the root `.htaccess` will redirect traffic into `public/` automatically.

6. Visit `/admin/login` to access the admin panel.

## Changing the admin password

Generate a new hash and replace `ADMIN_PASSWORD_HASH` in `.env`:

```bash
php -r 'echo password_hash("yournewpassword", PASSWORD_BCRYPT) . PHP_EOL;'
```

No database changes are needed. The hash lives only in `.env`, which is never committed.

## File structure

```
fornesusart/
├── public/                        # Web root — point DocumentRoot here
│   ├── index.php                  # Front controller and router
│   ├── .htaccess                  # Rewrites all requests to index.php
│   ├── assets/
│   │   ├── css/
│   │   │   ├── style.css          # Main site styles (Celestial Archive design)
│   │   │   └── admin.css          # Admin panel styles
│   │   ├── js/
│   │   │   └── main.js            # Gallery, form, and drag-and-drop behaviour
│   │   └── fonts/                 # Self-hosted font fallbacks (optional)
│   └── uploads/                   # Legacy upload directory (now empty — images stored in DB)
│       └── .htaccess              # Blocks PHP execution in this directory
├── app/
│   ├── bootstrap.php              # Env loading, session start, autoloader
│   ├── config/
│   │   └── database.php           # PDO connection (db() singleton function)
│   ├── controllers/
│   │   ├── GalleryController.php  # / — gallery with exhibits strip + works
│   │   ├── WorkController.php     # /work/[slug]
│   │   ├── AboutController.php    # /about + contact form
│   │   ├── CategoriesController.php  # /categories and /category/[slug]
│   │   ├── ExhibitController.php  # /exhibit/[slug]
│   │   ├── ImageController.php    # /image/[id] — serves blobs from DB
│   │   └── AdminController.php    # All /admin/* routes
│   ├── models/
│   │   ├── Artwork.php
│   │   ├── Category.php
│   │   ├── Exhibit.php
│   │   ├── MediaFile.php          # BLOB storage, getData(), create(blob, mime)
│   │   └── BioSection.php
│   ├── views/
│   │   ├── layout.php             # Shared header and footer
│   │   ├── gallery.php            # /
│   │   ├── work.php               # /work/[slug]
│   │   ├── about.php              # /about
│   │   ├── categories.php         # /categories
│   │   ├── category.php           # /category/[slug]
│   │   ├── exhibit.php            # /exhibit/[slug]
│   │   ├── 404.php
│   │   └── admin/
│   │       ├── layout.php
│   │       ├── login.php
│   │       ├── dashboard.php
│   │       ├── messages.php
│   │       ├── media.php          # Media library with grid cards, upload zone, and details panel
│   │       ├── trash.php          # Recycle bin
│   │       ├── artworks/
│   │       │   ├── index.php
│   │       │   └── form.php       # Create + edit (same view)
│   │       ├── categories/
│   │       │   ├── index.php
│   │       │   └── form.php       # Create + edit (same view)
│   │       ├── exhibits/
│   │       │   ├── index.php
│   │       │   └── form.php       # Create + edit with artwork assignment
│   │       └── bio/
│   │           ├── index.php
│   │           └── form.php       # Create + edit (same view)
│   └── helpers/
│       ├── auth.php               # Admin session gate
│       ├── upload.php             # MIME-validated blob upload, returns /image/{id}
│       └── slugify.php            # Title-to-slug utility
├── docs/
│   └── dependencies.md            # Register of external dependencies
├── schema.sql                     # Full database schema (fresh installs)
├── migrate_phase2.sql             # Phase 2 migration
├── migrate_phase4_blob.sql        # Phase 4 migration — adds blob columns
├── migrate_phase4_cleanup.sql     # Phase 4 cleanup — drops legacy path/subfolder columns
├── migrate_images_to_blob.php     # One-time data migration CLI script
├── env.example                    # Environment variable template
└── .env                           # Your local config — never committed
```

## URLs

### Public

| URL | Page |
|-----|------|
| `/` | Gallery — exhibits strip + works |
| `/categories` | Listing of all categories |
| `/category/[slug]` | Individual category with artworks |
| `/exhibit/[slug]` | Individual exhibit with artworks |
| `/work/[slug]` | Individual artwork |
| `/about` | Bio + contact form |
| `/image/[id]` | Serves an image blob from the database |

### Admin

| URL | Page |
|-----|------|
| `/admin` | Dashboard — Works, Categories, Exhibits, Messages, Trash counts |
| `/admin/artworks` | Manage artworks |
| `/admin/categories` | Manage categories |
| `/admin/exhibits` | Manage exhibits + artwork assignment |
| `/admin/bio` | Manage bio sections |
| `/admin/messages` | View contact form submissions |
| `/admin/media` | Media library — grid-based asset manager with upload zone, details panel, and copy URL/embed |
| `/admin/trash` | Recycle bin for soft-deleted content |

## License

MIT
