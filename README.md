# fornesusart

Personal art gallery site. PHP + MySQL, no framework.

## Development

```bash
php -S localhost:8000 -t public
```

The site will be at `http://localhost:8000`.

## Requirements

- PHP 8.1+
- MySQL 8+
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
   - **Existing database:** Import `migrate_phase2.sql` instead to apply only the Phase 2 changes.

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
│   └── uploads/                   # Uploaded artwork images (git-ignored)
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
│   │   └── AdminController.php    # All /admin/* routes
│   ├── models/
│   │   ├── Artwork.php
│   │   ├── Category.php
│   │   ├── Exhibit.php
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
│       ├── upload.php             # MIME-validated file upload
│       └── slugify.php            # Title-to-slug utility
├── docs/
│   └── dependencies.md            # Register of external dependencies
├── schema.sql                     # Full database schema (fresh installs)
├── migrate_phase2.sql             # Phase 2 migration (existing databases)
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

### Admin

| URL | Page |
|-----|------|
| `/admin` | Dashboard |
| `/admin/artworks` | Manage artworks |
| `/admin/categories` | Manage categories |
| `/admin/exhibits` | Manage exhibits + artwork assignment |
| `/admin/bio` | Manage bio sections |
| `/admin/messages` | View contact form submissions |

## License

MIT
