# Constraints

<!-- One entry per constraint. Format:
     CONSTRAINT: [plain-language description]
     SCOPE: [what it applies to]
     SET: [date or "this session"]

     Constraints are permanent until explicitly lifted.
     See AGENTS.md → User Constraints for full rules. -->


<!-- An empty file is still valid and still required.
     Absence of entries means no constraints have been stated yet —
     not that this file is optional. The agent must create this
     file at project root the first time any constraint is stated,
     even if AGENTS.md is read-only. -->

CONSTRAINT: Artworks must support mixed-media ordered slides using `artwork_media_items`, while legacy `artworks.piece_type` / `piece_value` remain in place through the migration release for fallback compatibility.
SCOPE: artworks schema, admin artwork form, gallery and work detail rendering
SET: 2026-06-09

CONSTRAINT: Admin authentication uses provider-backed admin identities via GitHub and Google OAuth only. No public user accounts table is introduced by this change.
SCOPE: all /admin/* routes
SET: 2026-06-09

CONSTRAINT: Uploaded files stored in public/uploads/ with .htaccess blocking PHP execution in that directory. MIME validated by magic bytes, not filename extension or Content-Type header.
SCOPE: file upload handling (app/helpers/upload.php)
SET: 2026-06-04

CONSTRAINT: Artwork slugs must not change after first publication. Once a /work/[slug] URL is live, it is permanent. Redirects required if a slug ever must change.
SCOPE: artwork admin form, Artwork model
SET: 2026-06-04

CONSTRAINT: Category and exhibit slugs must not change after first publication. Once /category/[slug] or /exhibit/[slug] is live, it is permanent.
SCOPE: category admin form, exhibit admin form, Category model, Exhibit model
SET: 2026-06-04

CONSTRAINT: Exhibits and artworks are many-to-many. An artwork may belong to multiple exhibits simultaneously. This is implemented via the exhibit_artworks junction table and must never be collapsed to a single foreign key.
SCOPE: exhibits schema, Exhibit::syncArtworks(), exhibit admin form
SET: 2026-06-04
