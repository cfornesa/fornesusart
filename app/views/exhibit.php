<?php
$pageTitle  = htmlspecialchars($exhibit['name']) . ' — Fornesus Art';
$activePage = 'gallery';

ob_start();
?>
<div class="collection-detail-page">
    <a href="/" class="work-back">&#8592; Return to the archive</a>

    <div class="collection-detail-header">
        <?php if ($exhibit['thumbnail_value']): ?>
            <div class="collection-detail-thumb">
                <img
                    src="<?= htmlspecialchars($exhibit['thumbnail_value']) ?>"
                    alt="<?= htmlspecialchars($exhibit['name']) ?>"
                >
                <div class="work-piece-glow"></div>
            </div>
        <?php endif ?>
        <div class="collection-detail-info">
            <h1 class="collection-detail-title"><?= htmlspecialchars($exhibit['name']) ?></h1>
            <?php if ($exhibit['description']): ?>
                <div class="collection-detail-desc">
                    <?= nl2br(htmlspecialchars($exhibit['description'])) ?>
                </div>
            <?php endif ?>
        </div>
    </div>

    <?php if (empty($artworks)): ?>
        <p class="gallery-empty">No works in this exhibit yet.</p>
    <?php else: ?>
        <div class="artwork-grid" style="margin-top:3rem">
            <?php foreach ($artworks as $i => $work): ?>
                <?php
                $sizeClass = match ($i % 7) {
                    0       => 'size-large',
                    1, 2    => 'size-small',
                    3       => 'size-medium',
                    4       => 'size-wide',
                    5, 6    => 'size-small',
                    default => 'size-medium',
                };
                ?>
                <a href="/work/<?= htmlspecialchars($work['slug']) ?>"
                   class="artwork-card <?= $sizeClass ?>">
                    <div class="artwork-thumb-wrap">
                        <?php if ($work['thumbnail_value']): ?>
                            <img
                                src="<?= htmlspecialchars($work['thumbnail_value']) ?>"
                                alt="<?= htmlspecialchars($work['title']) ?>"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <div class="collection-thumb-placeholder"></div>
                        <?php endif ?>
                        <div class="artwork-glow"></div>
                    </div>
                    <div class="artwork-meta">
                        <span class="artwork-title"><?= htmlspecialchars($work['title']) ?></span>
                        <?php if ($work['year']): ?>
                            <span class="artwork-year"><?= htmlspecialchars($work['year']) ?></span>
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
