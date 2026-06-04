<?php
$pageTitle  = 'Categories — Fornesus Art';
$activePage = 'categories';

ob_start();
?>
<div class="collection-page">
    <div class="collection-header">
        <h1 class="collection-title">Categories</h1>
    </div>

    <?php if (empty($categories)): ?>
        <p class="gallery-empty">No categories have been created yet.</p>
    <?php else: ?>
        <div class="collection-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="/category/<?= htmlspecialchars($cat['slug']) ?>" class="collection-card">
                    <div class="collection-thumb-wrap">
                        <?php if ($cat['thumbnail_value']): ?>
                            <img
                                src="<?= htmlspecialchars($cat['thumbnail_value']) ?>"
                                alt="<?= htmlspecialchars($cat['name']) ?>"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <div class="collection-thumb-placeholder"></div>
                        <?php endif ?>
                        <div class="artwork-glow"></div>
                    </div>
                    <div class="collection-card-meta">
                        <span class="collection-card-name"><?= htmlspecialchars($cat['name']) ?></span>
                        <?php if ($cat['description']): ?>
                            <span class="collection-card-desc">
                                <?= htmlspecialchars(mb_substr($cat['description'], 0, 100)) ?><?= mb_strlen($cat['description']) > 100 ? '…' : '' ?>
                            </span>
                        <?php endif ?>
                    </div>
                </a>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
