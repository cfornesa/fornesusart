<?php
$pageTitle  = ($artwork['title'] ?? 'Work') . ' ';
$activePage = 'gallery';
$metaTitle = ($artwork['title'] ?: 'Work') . ' — Fornesus Art';
$metaDescription = seo_excerpt($artwork['description'] ?? null, 170)
    ?: trim(($artwork['year'] ? $artwork['year'] . ' · ' : '') . ($artwork['category_name'] ?? 'Artwork from Fornesus Art'));
$ogTitle = $metaTitle;
$ogDescription = $metaDescription;
$metaImage = $artwork['thumbnail_value']
    ?: (($artwork['piece_type'] ?? '') === 'embed' ? null : ($artwork['piece_value'] ?? null));
$metaImageAlt = $artwork['title'] ?: 'Artwork preview';
$canonicalUrl = seo_absolute_url('/work/' . $artwork['slug']);

ob_start();
?>
<div class="work-page">
    <a href="/" class="work-back">&#8592; Return to the archive</a>

    <article class="work-detail" aria-labelledby="work-title">
        <div class="work-piece-wrap">
            <?php if (!($pieceState['valid'] ?? false)): ?>
                <div class="work-piece-fallback" role="status">
                    <strong class="work-piece-fallback-title">Artwork display unavailable</strong>
                    <p><?= htmlspecialchars($pieceState['message'] ?? 'This artwork source could not be rendered.') ?></p>
                </div>
            <?php elseif ($artwork['piece_type'] === 'embed'): ?>
                <div class="work-embed">
                    <?= $artwork['piece_value'] /* embed code is stored verbatim — admin-entered only */ ?>
                </div>
            <?php else: ?>
                <img
                    src="<?= htmlspecialchars($artwork['piece_value']) ?>"
                    alt="<?= htmlspecialchars($artwork['title']) ?>"
                    class="work-image"
                    decoding="async"
                    fetchpriority="high"
                >
            <?php endif ?>
            <div class="work-piece-glow" aria-hidden="true"></div>
        </div>

        <div class="work-info">
            <h1 class="work-title" id="work-title"><?= htmlspecialchars($artwork['title']) ?></h1>
            <div class="work-meta-line">
                <?php if ($artwork['year']): ?>
                    <span class="work-year"><?= htmlspecialchars($artwork['year']) ?></span>
                <?php endif ?>
                <?php if ($artwork['category_name']): ?>
                    <span class="work-cat-sep">·</span>
                    <a href="/category/<?= htmlspecialchars($artwork['category_slug']) ?>" class="work-category">
                        <?= htmlspecialchars($artwork['category_name']) ?>
                    </a>
                <?php endif ?>
            </div>
            <?php if ($artwork['description']): ?>
                <div class="work-description">
                    <?= $artwork['description'] ?>
                </div>
            <?php endif ?>
        </div>
    </article>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
