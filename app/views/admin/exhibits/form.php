<?php
$isEdit    = $exhibit !== null;
$exhibit   = $exhibit ?? [];
$pageTitle = ($isEdit ? 'Edit Exhibit' : 'New Exhibit') . ' — Fornesus Art Admin';
ob_start();
?>
<div class="admin-section">
    <h1 class="admin-heading"><?= $isEdit ? 'Edit Exhibit' : 'New Exhibit' ?></h1>

    <?php if ($error ?? null): ?>
        <p class="admin-error"><?= htmlspecialchars($error) ?></p>
    <?php endif ?>

    <form
        method="POST"
        enctype="multipart/form-data"
        action="<?= $isEdit ? '/admin/exhibits/' . $exhibit['id'] . '/edit' : '/admin/exhibits/create' ?>"
        class="admin-form"
    >
        <div class="form-row">
            <label>Name *</label>
            <input type="text" name="name" id="exhibit-name" value="<?= htmlspecialchars($exhibit['name'] ?? '') ?>" required>
        </div>

        <div class="form-row">
            <label>Slug <span class="form-hint">(auto-generated; do not change after publishing)</span></label>
            <input type="text" name="slug" id="exhibit-slug" value="<?= htmlspecialchars($exhibit['slug'] ?? '') ?>">
        </div>

        <div class="form-row">
            <label>Description</label>
            <textarea name="description" rows="5"><?= htmlspecialchars($exhibit['description'] ?? '') ?></textarea>
        </div>

        <!-- Thumbnail -->
        <fieldset class="form-fieldset">
            <legend>Thumbnail</legend>
            <div class="toggle-group" data-target="thumb">
                <label class="toggle-opt">
                    <input type="radio" name="thumbnail_type" value="upload"
                        <?= ($exhibit['thumbnail_type'] ?? '') === 'upload' ? 'checked' : '' ?>>
                    Upload image
                </label>
                <label class="toggle-opt">
                    <input type="radio" name="thumbnail_type" value="link"
                        <?= ($exhibit['thumbnail_type'] ?? 'link') === 'link' ? 'checked' : '' ?>>
                    Image URL
                </label>
            </div>
            <div class="toggle-panel" data-panel="thumb-upload"
                 style="display:<?= ($exhibit['thumbnail_type'] ?? 'link') === 'upload' ? 'block' : 'none' ?>">
                <input type="file" name="thumbnail_upload" accept="image/*">
                <?php if ($isEdit && ($exhibit['thumbnail_type'] ?? '') === 'upload' && $exhibit['thumbnail_value']): ?>
                    <img src="<?= htmlspecialchars($exhibit['thumbnail_value']) ?>" class="admin-thumb-preview" alt="">
                <?php endif ?>
            </div>
            <div class="toggle-panel" data-panel="thumb-link"
                 style="display:<?= ($exhibit['thumbnail_type'] ?? 'link') !== 'upload' ? 'block' : 'none' ?>">
                <input type="url" name="thumbnail_link"
                       value="<?= htmlspecialchars(($exhibit['thumbnail_type'] ?? '') === 'link' ? ($exhibit['thumbnail_value'] ?? '') : '') ?>"
                       placeholder="https://…">
            </div>
        </fieldset>

        <!-- Artwork assignment -->
        <fieldset class="form-fieldset">
            <legend>Artworks in this exhibit</legend>
            <?php if (empty($allArtworks)): ?>
                <p class="admin-hint">No artworks exist yet. Add some first.</p>
            <?php else: ?>
                <div class="exhibit-artwork-list">
                    <?php foreach ($allArtworks as $aw): ?>
                        <label class="exhibit-artwork-check">
                            <input
                                type="checkbox"
                                name="artwork_ids[]"
                                value="<?= $aw['id'] ?>"
                                <?= in_array((string) $aw['id'], array_map('strval', $assigned)) ? 'checked' : '' ?>
                            >
                            <span><?= htmlspecialchars($aw['title']) ?><?= $aw['year'] ? ' · ' . htmlspecialchars($aw['year']) : '' ?></span>
                        </label>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
        </fieldset>

        <div class="form-actions">
            <button type="submit" class="admin-btn"><?= $isEdit ? 'Save Changes' : 'Create Exhibit' ?></button>
            <a href="/admin/exhibits" class="admin-btn admin-btn-ghost">Cancel</a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
