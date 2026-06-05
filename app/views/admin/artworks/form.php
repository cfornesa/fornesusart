<?php
$isEdit    = $artwork !== null;
$artwork   = $artwork ?? [];
$pieceState = $isEdit ? Artwork::inspectPiece($artwork) : null;
$pageTitle = ($isEdit ? 'Edit Work' : 'Add Work') . ' — Fornesus Art Admin';
ob_start();
?>
<div class="admin-section">
    <h1 class="admin-heading"><?= $isEdit ? 'Edit Work' : 'Add Work' ?></h1>

    <?php if ($error ?? null): ?>
        <p class="admin-error"><?= htmlspecialchars($error) ?></p>
    <?php endif ?>

    <form
        method="POST"
        enctype="multipart/form-data"
        action="<?= $isEdit ? '/admin/artworks/' . $artwork['id'] . '/edit' : '/admin/artworks/create' ?>"
        class="admin-form"
    >
        <div class="form-row">
            <label>Title *</label>
            <input type="text" name="title" value="<?= htmlspecialchars($artwork['title'] ?? '') ?>" required>
        </div>

        <div class="form-row">
            <label>Slug <span class="form-hint">(auto-generated from title; override by typing here — do not change after publishing)</span></label>
            <input type="text" name="slug" value="<?= htmlspecialchars($artwork['slug'] ?? '') ?>">
        </div>

        <div class="form-row">
            <label>Year</label>
            <input type="text" name="year" value="<?= htmlspecialchars($artwork['year'] ?? '') ?>" placeholder="2024">
        </div>

        <div class="form-row">
            <label>Category</label>
            <select name="category_id">
                <option value="">— None —</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= ($artwork['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach ?>
            </select>
        </div>

        <div class="form-row">
            <label>Description</label>
            <textarea name="description" rows="4" data-tiptap><?= htmlspecialchars($artwork['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <label>Sort Order</label>
            <input type="number" name="sort_order" value="<?= (int) ($artwork['sort_order'] ?? 0) ?>" min="0">
        </div>

        <!-- Thumbnail -->
        <fieldset class="form-fieldset">
            <legend>Thumbnail <span class="form-hint">(optional)</span></legend>
            <input type="hidden" name="thumbnail_type" value="link">
            <div class="media-field-preview" id="artwork-thumb-preview">
                <?php if ($isEdit && $artwork['thumbnail_value']): ?>
                    <img src="<?= htmlspecialchars($artwork['thumbnail_value']) ?>" alt="">
                <?php endif ?>
            </div>
            <input id="artwork-thumb-url" type="url" name="thumbnail_link"
                   value="<?= htmlspecialchars($artwork['thumbnail_value'] ?? '') ?>"
                   placeholder="No image selected" readonly>
            <div class="media-field-actions">
                <button type="button" class="picker-trigger"
                        data-picker-target="artwork-thumb-url"
                        data-picker-preview="artwork-thumb-preview">Choose Image</button>
                <button type="button" class="admin-btn admin-btn-ghost admin-btn-sm"
                        data-clear-input="artwork-thumb-url"
                        data-clear-preview="artwork-thumb-preview">Clear</button>
            </div>
        </fieldset>

        <!-- Piece -->
        <fieldset class="form-fieldset">
            <legend>Artwork Piece *</legend>
            <?php if ($pieceState): ?>
                <div class="piece-source-status <?= !empty($pieceState['valid']) ? 'is-valid' : 'is-invalid' ?>">
                    <strong>Current piece source:</strong>
                    <span><?= htmlspecialchars($pieceState['message']) ?></span>
                    <?php if (!empty($pieceState['source'])): ?>
                        <code><?= htmlspecialchars($pieceState['source']) ?></code>
                    <?php endif ?>
                </div>
            <?php endif ?>
            <div class="toggle-group" data-target="piece">
                <label class="toggle-opt">
                    <input type="radio" name="piece_type" value="image_link"
                        <?= in_array($artwork['piece_type'] ?? 'image_link', ['image_link', 'image_upload']) ? 'checked' : '' ?>>
                    Direct image URL
                </label>
                <label class="toggle-opt">
                    <input type="radio" name="piece_type" value="embed"
                        <?= ($artwork['piece_type'] ?? '') === 'embed' ? 'checked' : '' ?>>
                    Raw iframe embed HTML
                </label>
            </div>
            <div class="toggle-panel" data-panel="piece-image_link"
                 style="display:<?= ($artwork['piece_type'] ?? 'image_link') !== 'embed' ? 'block' : 'none' ?>">
                <p class="admin-hint">Use the media picker for uploaded artwork or paste a direct image URL. Do not paste HTML here.</p>
                <div class="media-field-preview" id="artwork-piece-preview">
                    <?php if ($isEdit && in_array($artwork['piece_type'] ?? '', ['image_link', 'image_upload'])): ?>
                        <img src="<?= htmlspecialchars($artwork['piece_value']) ?>" alt="">
                    <?php endif ?>
                </div>
                <input id="artwork-piece-url" type="url" name="piece_link"
                       value="<?= htmlspecialchars(in_array($artwork['piece_type'] ?? '', ['image_link', 'image_upload']) ? ($artwork['piece_value'] ?? '') : '') ?>"
                       placeholder="No image selected" readonly>
                <button type="button" class="picker-trigger"
                        data-picker-target="artwork-piece-url"
                        data-picker-preview="artwork-piece-preview"
                        data-picker-radio="piece_type"
                        data-picker-radio-value="image_link">Choose Image</button>
            </div>
            <div class="toggle-panel" data-panel="piece-embed"
                 style="display:<?= ($artwork['piece_type'] ?? '') === 'embed' ? 'block' : 'none' ?>">
                <p class="admin-hint">Paste the complete iframe embed HTML for this artwork here. Direct image URLs belong in the image field above.</p>
                <textarea name="piece_embed" rows="5" placeholder="<iframe …></iframe>"><?= htmlspecialchars(($artwork['piece_type'] ?? '') === 'embed' ? ($artwork['piece_value'] ?? '') : '') ?></textarea>
            </div>
        </fieldset>

        <div class="form-actions">
            <button type="submit" class="admin-btn"><?= $isEdit ? 'Save Changes' : 'Create Work' ?></button>
            <a href="/admin/artworks" class="admin-btn admin-btn-ghost">Cancel</a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
