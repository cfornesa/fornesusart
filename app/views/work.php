<?php
$pageTitle  = htmlspecialchars($artwork['title']) . ' ';
$activePage = 'gallery';

ob_start();
?>
<div class="work-page">
    <a href="/" class="work-back">&#8592; Return to the archive</a>

    <article class="work-detail">
        <div class="work-piece-wrap">
            <?php if ($artwork['piece_type'] === 'embed'): ?>
                <div class="work-embed">
                    <?= $artwork['piece_value'] /* embed code is stored verbatim — admin-entered only */ ?>
                </div>
            <?php else: ?>
                <img
                    src="<?= htmlspecialchars($artwork['piece_value']) ?>"
                    alt="<?= htmlspecialchars($artwork['title']) ?>"
                    class="work-image"
                >
            <?php endif ?>
            <div class="work-piece-glow"></div>
        </div>

        <div class="work-info">
            <h1 class="work-title"><?= htmlspecialchars($artwork['title']) ?></h1>
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
                <?php $desc = $artwork['description']; $long = mb_strlen($desc) > 255; ?>
                <div class="work-description">
                    <?php if ($long): ?>
                        <span class="desc-short"><?= nl2br(htmlspecialchars(mb_substr($desc, 0, 255))) ?>&#8230; <button class="desc-read-more" aria-expanded="false">Read more</button></span>
                        <span class="desc-full" hidden><?= nl2br(htmlspecialchars($desc)) ?> <button class="desc-read-less" aria-expanded="true">Read less</button></span>
                    <?php else: ?>
                        <?= nl2br(htmlspecialchars($desc)) ?>
                    <?php endif ?>
                </div>
            <?php endif ?>
        </div>
    </article>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
