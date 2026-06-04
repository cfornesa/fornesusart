<?php
$isEdit    = $artwork !== null;
$artwork   = $artwork ?? [];
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
            <textarea name="description" rows="4"><?= htmlspecialchars($artwork['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <label>Sort Order</label>
            <input type="number" name="sort_order" value="<?= (int) ($artwork['sort_order'] ?? 0) ?>" min="0">
        </div>

        <!-- Thumbnail -->
        <fieldset class="form-fieldset">
            <legend>Thumbnail <span class="form-hint">(optional)</span></legend>
            <div class="toggle-group" data-target="thumb">
                <label class="toggle-opt">
                    <input type="radio" name="thumbnail_type" value="upload"
                        <?= ($artwork['thumbnail_type'] ?? '') === 'upload' ? 'checked' : '' ?>>
                    Upload image
                </label>
                <label class="toggle-opt">
                    <input type="radio" name="thumbnail_type" value="link"
                        <?= ($artwork['thumbnail_type'] ?? 'link') === 'link' ? 'checked' : '' ?>>
                    Image URL
                </label>
            </div>
            <div class="toggle-panel" data-panel="thumb-upload"
                 style="display:<?= ($artwork['thumbnail_type'] ?? 'link') === 'upload' ? 'block' : 'none' ?>">
                <input type="file" name="thumbnail_upload" accept="image/*">
                <?php if ($isEdit && $artwork['thumbnail_type'] === 'upload'): ?>
                    <img src="<?= htmlspecialchars($artwork['thumbnail_value']) ?>" class="admin-thumb-preview" alt="">
                <?php endif ?>
            </div>
            <div class="toggle-panel" data-panel="thumb-link"
                 style="display:<?= ($artwork['thumbnail_type'] ?? 'link') !== 'upload' ? 'block' : 'none' ?>">
                <input type="url" name="thumbnail_link"
                       value="<?= htmlspecialchars(($artwork['thumbnail_type'] ?? '') === 'link' ? ($artwork['thumbnail_value'] ?? '') : '') ?>"
                       placeholder="https://…">
            </div>
        </fieldset>

        <!-- Piece -->
        <fieldset class="form-fieldset">
            <legend>Artwork Piece *</legend>
            <div class="toggle-group" data-target="piece">
                <label class="toggle-opt">
                    <input type="radio" name="piece_type" value="image_upload"
                        <?= ($artwork['piece_type'] ?? '') === 'image_upload' ? 'checked' : '' ?>>
                    Upload image
                </label>
                <label class="toggle-opt">
                    <input type="radio" name="piece_type" value="image_link"
                        <?= ($artwork['piece_type'] ?? 'image_link') === 'image_link' ? 'checked' : '' ?>>
                    Image URL
                </label>
                <label class="toggle-opt">
                    <input type="radio" name="piece_type" value="embed"
                        <?= ($artwork['piece_type'] ?? '') === 'embed' ? 'checked' : '' ?>>
                    Iframe embed
                </label>
            </div>
            <div class="toggle-panel" data-panel="piece-image_upload"
                 style="display:<?= ($artwork['piece_type'] ?? 'image_link') === 'image_upload' ? 'block' : 'none' ?>">
                <input type="file" name="piece_upload" accept="image/*">
                <?php if ($isEdit && $artwork['piece_type'] === 'image_upload'): ?>
                    <img src="<?= htmlspecialchars($artwork['piece_value']) ?>" class="admin-thumb-preview" alt="">
                <?php endif ?>
            </div>
            <div class="toggle-panel" data-panel="piece-image_link"
                 style="display:<?= ($artwork['piece_type'] ?? 'image_link') === 'image_link' ? 'block' : 'none' ?>">
                <input type="url" name="piece_link"
                       value="<?= htmlspecialchars(($artwork['piece_type'] ?? '') === 'image_link' ? ($artwork['piece_value'] ?? '') : '') ?>"
                       placeholder="https://…">
            </div>
            <div class="toggle-panel" data-panel="piece-embed"
                 style="display:<?= ($artwork['piece_type'] ?? '') === 'embed' ? 'block' : 'none' ?>">
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
require dirname(__DIR__) . '/layout.php';
