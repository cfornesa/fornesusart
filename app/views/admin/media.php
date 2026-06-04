<?php
$pageTitle = 'Media Library — Fornesus Art Admin';
ob_start();
?>
<div class="admin-section">
    <div class="admin-section-head">
        <h1 class="admin-heading">Media Library</h1>
        <span class="admin-hint"><?= count($files) ?> file<?= count($files) !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($files)): ?>
        <p class="admin-empty">No uploaded files yet.</p>
    <?php else: ?>
        <div class="media-grid">
            <?php foreach ($files as $f): ?>
                <div class="media-card">
                    <div class="media-thumb-wrap">
                        <img src="/image/<?= (int) $f['id'] ?>"
                             alt=""
                             loading="lazy"
                             onerror="this.parentElement.classList.add('media-thumb-missing')">
                    </div>
                    <div class="media-card-meta">
                        <span class="media-filename">ID <?= (int) $f['id'] ?></span>
                        <span class="media-subfolder"><?= htmlspecialchars($f['mime_type'] ?? '') ?></span>
                        <span class="media-date"><?= date('Y-m-d', strtotime($f['created_at'])) ?></span>
                    </div>
                    <div class="media-card-actions">
                        <form method="POST" action="/admin/media/<?= (int) $f['id'] ?>/trash"
                              onsubmit="return confirm('Move this file to the recycle bin?')">
                            <button type="submit" class="admin-btn admin-btn-sm">Move to trash</button>
                        </form>
                        <form method="POST" action="/admin/media/<?= (int) $f['id'] ?>/destroy"
                              onsubmit="return confirm('Permanently delete this file from disk? This cannot be undone.')">
                            <button type="submit" class="admin-del-btn admin-destroy-btn">Delete now</button>
                        </form>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layout.php';
