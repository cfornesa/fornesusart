<?php
$isEdit    = $category !== null;
$category  = $category ?? [];
$pageTitle = ($isEdit ? 'Edit Category' : 'New Category') . ' — Fornesus Art Admin';
ob_start();
?>
<div class="admin-section">
    <h1 class="admin-heading"><?= $isEdit ? 'Edit Category' : 'New Category' ?></h1>

    <?php if ($error ?? null): ?>
        <p class="admin-error"><?= htmlspecialchars($error) ?></p>
    <?php endif ?>

    <form
        method="POST"
        enctype="multipart/form-data"
        action="<?= $isEdit ? '/admin/categories/' . $category['id'] . '/edit' : '/admin/categories/create' ?>"
        class="admin-form"
    >
        <div class="form-row">
            <label>Name *</label>
            <input type="text" name="name" id="cat-name" value="<?= htmlspecialchars($category['name'] ?? '') ?>" required>
        </div>

        <div class="form-row">
            <label>Slug <span class="form-hint">(auto-generated; do not change after publishing)</span></label>
            <input type="text" name="slug" id="cat-slug" value="<?= htmlspecialchars($category['slug'] ?? '') ?>">
        </div>

        <div class="form-row">
            <label>Description</label>
            <textarea name="description" rows="5"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
        </div>

        <!-- Thumbnail -->
        <fieldset class="form-fieldset">
            <legend>Thumbnail</legend>
            <div class="toggle-group" data-target="thumb">
                <label class="toggle-opt">
                    <input type="radio" name="thumbnail_type" value="upload"
                        <?= ($category['thumbnail_type'] ?? '') === 'upload' ? 'checked' : '' ?>>
                    Upload image
                </label>
                <label class="toggle-opt">
                    <input type="radio" name="thumbnail_type" value="link"
                        <?= ($category['thumbnail_type'] ?? 'link') === 'link' ? 'checked' : '' ?>>
                    Image URL
                </label>
            </div>
            <div class="toggle-panel" data-panel="thumb-upload"
                 style="display:<?= ($category['thumbnail_type'] ?? 'link') === 'upload' ? 'block' : 'none' ?>">
                <input type="file" name="thumbnail_upload" accept="image/*">
                <?php if ($isEdit && ($category['thumbnail_type'] ?? '') === 'upload' && $category['thumbnail_value']): ?>
                    <img src="<?= htmlspecialchars($category['thumbnail_value']) ?>" class="admin-thumb-preview" alt="">
                <?php endif ?>
            </div>
            <div class="toggle-panel" data-panel="thumb-link"
                 style="display:<?= ($category['thumbnail_type'] ?? 'link') !== 'upload' ? 'block' : 'none' ?>">
                <input type="url" name="thumbnail_link"
                       value="<?= htmlspecialchars(($category['thumbnail_type'] ?? '') === 'link' ? ($category['thumbnail_value'] ?? '') : '') ?>"
                       placeholder="https://…">
            </div>
        </fieldset>

        <div class="form-actions">
            <button type="submit" class="admin-btn"><?= $isEdit ? 'Save Changes' : 'Create Category' ?></button>
            <a href="/admin/categories" class="admin-btn admin-btn-ghost">Cancel</a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
