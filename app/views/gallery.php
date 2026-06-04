<?php
$pageTitle  = 'Fornesus';
$activePage = 'gallery';

ob_start();
?>
<div class="gallery-page">

    <?php if (!empty($exhibits)): ?>
        <section class="gallery-section exhibits-strip">
            <div class="gallery-section-header">
                <h2 class="category-name">Exhibits</h2>
                <span class="section-rule"></span>
            </div>
            <div class="exhibits-grid">
                <?php foreach ($exhibits as $ex): ?>
                    <a href="/exhibit/<?= htmlspecialchars($ex['slug']) ?>" class="exhibit-card">
                        <div class="artwork-thumb-wrap">
                            <?php if ($ex['thumbnail_value']): ?>
                                <img
                                    src="<?= htmlspecialchars($ex['thumbnail_value']) ?>"
                                    alt="<?= htmlspecialchars($ex['name']) ?>"
                                    loading="lazy"
                                >
                            <?php else: ?>
                                <div class="collection-thumb-placeholder"></div>
                            <?php endif ?>
                            <div class="artwork-glow"></div>
                        </div>
                        <div class="artwork-meta">
                            <span class="artwork-title"><?= htmlspecialchars($ex['name']) ?></span>
                        </div>
                    </a>
                <?php endforeach ?>
            </div>
        </section>
    <?php endif ?>

    <section class="gallery-section">
        <div class="gallery-section-header">
            <h2 class="category-name">Works</h2>
            <span class="section-rule"></span>
        </div>

        <?php if (empty($artworks)): ?>
            <p class="gallery-empty">No works have been added yet.</p>
        <?php else: ?>
            <div class="artwork-grid">
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
                    $hidden = $i >= 3 ? 'gallery-work-overflow' : '';
                    ?>
                    <a href="/work/<?= htmlspecialchars($work['slug']) ?>"
                       class="artwork-card <?= $sizeClass ?> <?= $hidden ?>">
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
            <?php if (count($artworks) > 3): ?>
                <button class="see-more-btn" id="works-see-more">See More</button>
            <?php endif ?>
        <?php endif ?>
    </section>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
