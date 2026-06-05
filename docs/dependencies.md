# External Dependencies

## Self-Hosted (no network requests)

The public site and the main authenticated admin shell serve their primary fonts locally.

### Self-Hosted Fonts (`public/assets/fonts/`)

Registered via `@font-face` in `public/assets/css/style.css`. Preloaded in both `app/views/layout.php` and `app/views/admin/layout.php`.

| Typeface | Usage |
|---|---|
| **Lora** | Body text — `'Lora', Georgia, serif` |
| **Pinyon Script** | Display headings — `'Pinyon Script', 'Lora', cursive` |
| **Courier Prime** | Monospace / admin metadata — `'Courier Prime', 'Courier New', monospace` |

---

## CDN Dependencies (admin panel only)

The **public-facing site has no CDN dependencies at all**. The remaining off-domain requests are limited to the admin experience.

### Google Fonts — admin login only

**CDN**: `https://fonts.googleapis.com/` and `https://fonts.gstatic.com/`  
**Loaded via**: `<link rel="stylesheet">` in `app/views/admin/login.php`

**Fonts loaded**:

| Typeface | Purpose |
|---|---|
| **Cinzel Decorative** | Legacy login display title |
| **Lora** | Login body/form text |
| **Courier Prime** | Login button / mono accents |

**Risk**: If Google Fonts is unavailable, the admin login falls back to local/system serif and monospace fonts. Admin authentication still works, but branding and typography shift.

**Self-hosting alternative**: Move the login view to the same self-hosted font stack already used by `app/views/layout.php` and `app/views/admin/layout.php`, then remove the Google Fonts `<link>` tags.

---

### Tiptap Rich Text Editor — `esm.sh`

**CDN**: `https://esm.sh/`  
**Loaded via**: `<script type="module" src="/assets/js/tiptap-editor.js">` in `app/views/admin/layout.php`  
**Import map**: also in `admin/layout.php` — deduplicates shared ProseMirror instances across all extensions

**Risk**: If `esm.sh` is unavailable, the Tiptap editor fails to load. Existing saved content is unaffected; admin users can still submit forms (the `<textarea>` is hidden but present). The rest of the admin panel functions normally.

**Packages loaded**:

| Package | Version | Purpose |
|---|---|---|
| `@tiptap/core` | `^2` | Editor core |
| `@tiptap/starter-kit` | `^2` | Bold, italic, headings, lists, blockquote, code, HR, history |
| `@tiptap/extension-underline` | `^2` | Underline mark |
| `@tiptap/extension-text-style` | `^2` | Base for font size, color, font family |
| `@tiptap/extension-color` | `^2` | Text color |
| `@tiptap/extension-highlight` | `^2` | Highlight color (multicolor) |
| `@tiptap/extension-font-family` | `^2` | Font family |
| `@tiptap/extension-link` | `^2` | Hyperlinks (extended with `title` attribute) |
| `@tiptap/extension-image` | `^2` | Image nodes (extended with hover alt-text editor NodeView) |

**Self-hosting alternative**: Run `npm install @tiptap/core @tiptap/starter-kit ...` and `npx esbuild public/assets/js/tiptap-editor.js --bundle --format=esm --outfile=public/assets/js/tiptap.bundle.js`, then update the script src. No changes to PHP required.
