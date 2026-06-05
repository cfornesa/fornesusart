<?php
$pageTitle = 'Media Library — Fornesus Art Admin';
$mainClass = 'admin-main-wide';
ob_start();
?>
<div class="admin-section media-library-page">
    <div class="admin-section-head">
        <div>
            <h1 class="admin-heading">Media Library</h1>
            <p class="admin-hint media-library-intro">Select an asset to copy its URL or move it out of circulation.</p>
        </div>
        <span class="admin-hint"><?= count($files) ?> file<?= count($files) !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (isset($_GET['uploaded'])): ?>
        <p class="admin-notice">Asset uploaded successfully.</p>
    <?php endif ?>
    <?php if (isset($_GET['error'])): ?>
        <p class="admin-error"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif ?>

    <section class="media-upload-panel">
        <form method="POST" action="/admin/media/upload" enctype="multipart/form-data" class="media-upload-form">
            <input type="file" name="media_file" id="media-upload-input" accept="image/*">
        </form>
        <div class="media-upload-zone" id="media-upload-zone" tabindex="0" role="button" aria-controls="media-upload-input">
            <span class="media-upload-text">Upload or drop an image</span>
            <span class="media-upload-hint">JPEG, PNG, GIF, WEBP, and AVIF files are supported.</span>
        </div>
    </section>

    <div class="media-workspace">
        <section class="media-grid-panel">
            <div class="media-panel-head">
                <h2 class="admin-subheading">Library Grid</h2>
                <span class="admin-hint">Newest uploads appear first.</span>
            </div>

            <?php if (empty($files)): ?>
                <p class="admin-empty">No uploaded files yet.</p>
            <?php else: ?>
                <div class="media-grid">
                    <?php foreach ($files as $f): ?>
                        <button
                             type="button"
                             class="media-card"
                             data-id="<?= (int) $f['id'] ?>"
                             data-mime="<?= htmlspecialchars($f['mime_type'] ?? '') ?>"
                             data-date="<?= date('Y-m-d', strtotime($f['created_at'])) ?>">
                            <span class="media-card-thumb">
                                <img src="/image/<?= (int) $f['id'] ?>"
                                     alt=""
                                     loading="lazy"
                                     onerror="this.parentElement.classList.add('media-thumb-missing')">
                            </span>
                            <span class="media-card-meta">
                                <span class="media-card-id">Asset #<?= (int) $f['id'] ?></span>
                                <span class="media-card-type"><?= htmlspecialchars($f['mime_type'] ?? 'Unknown type') ?></span>
                                <span class="media-card-date"><?= date('Y-m-d', strtotime($f['created_at'])) ?></span>
                            </span>
                        </button>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
        </section>

        <aside class="media-details-panel">
            <div class="media-panel-head">
                <h2 class="admin-subheading">Selected Asset</h2>
                <span class="admin-hint">Preview, copy, or remove.</span>
            </div>
            <div class="media-details-preview">
                <img id="details-preview-img" class="is-hidden" src="" alt="">
            </div>

            <div class="media-details-placeholder" id="details-placeholder">
                Select an asset to view details.
            </div>

            <div class="media-details-content is-hidden" id="details-content-area">
                <div class="media-details-meta">
                    <div class="media-meta-row">
                        <span class="media-meta-label">ID</span>
                        <span class="media-meta-value" id="meta-id">—</span>
                    </div>
                    <div class="media-meta-row">
                        <span class="media-meta-label">Type</span>
                        <span class="media-meta-value" id="meta-mime">—</span>
                    </div>
                    <div class="media-meta-row">
                        <span class="media-meta-label">Uploaded</span>
                        <span class="media-meta-value" id="meta-date">—</span>
                    </div>
                </div>

                <div class="form-row media-details-code">
                    <label for="input-url">Direct URL</label>
                    <div class="media-code-input-wrap">
                        <input type="text" id="input-url" readonly>
                        <button type="button" class="admin-btn media-copy-btn" data-copy-target="input-url">Copy</button>
                    </div>
                </div>

                <div class="form-row media-details-code">
                    <label for="input-html">HTML Embed Code</label>
                    <div class="media-code-input-wrap">
                        <input type="text" id="input-html" readonly>
                        <button type="button" class="admin-btn media-copy-btn" data-copy-target="input-html">Copy</button>
                    </div>
                </div>

                <div class="media-details-actions">
                    <form method="POST" id="action-trash-form">
                        <button type="submit" class="admin-btn">Move to Trash</button>
                    </form>
                    <form method="POST" id="action-destroy-form">
                        <button type="submit" class="admin-btn-danger">Delete Now</button>
                    </form>
                </div>
            </div>
        </aside>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.media-card');
    const previewImg = document.getElementById('details-preview-img');
    const metaId = document.getElementById('meta-id');
    const metaMime = document.getElementById('meta-mime');
    const metaDate = document.getElementById('meta-date');
    const inputUrl = document.getElementById('input-url');
    const inputHtml = document.getElementById('input-html');
    const trashForm = document.getElementById('action-trash-form');
    const destroyForm = document.getElementById('action-destroy-form');
    const placeholderText = document.getElementById('details-placeholder');
    const contentArea = document.getElementById('details-content-area');

    function selectCard(card) {
        cards.forEach(item => item.classList.remove('active'));
        card.classList.add('active');

        const id = card.dataset.id;
        const mime = card.dataset.mime;
        const date = card.dataset.date;
        const imgUrl = `/image/${id}`;

        previewImg.src = imgUrl;
        previewImg.classList.remove('is-hidden');

        metaId.textContent = id;
        metaMime.textContent = mime;
        metaDate.textContent = date;

        inputUrl.value = window.location.origin + imgUrl;
        inputHtml.value = `<img src="${imgUrl}" alt="">`;

        trashForm.action = `/admin/media/${id}/trash`;
        destroyForm.action = `/admin/media/${id}/destroy`;

        placeholderText.classList.add('is-hidden');
        contentArea.classList.remove('is-hidden');
    }

    cards.forEach(card => {
        card.addEventListener('click', () => {
            selectCard(card);
        });
    });

    if (cards.length > 0) {
        selectCard(cards[0]);
    } else {
        placeholderText.textContent = 'No assets in library.';
    }

    trashForm.addEventListener('submit', (e) => {
        if (!confirm('Move this asset to the recycle bin?')) {
            e.preventDefault();
        }
    });

    destroyForm.addEventListener('submit', (e) => {
        if (!confirm('Permanently delete this asset? This cannot be undone.')) {
            e.preventDefault();
        }
    });

    document.querySelectorAll('.media-copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.copyTarget;
            const input = document.getElementById(targetId);
            const originalText = btn.textContent;

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(input.value).catch(() => {
                    input.select();
                    document.execCommand('copy');
                });
            } else {
                input.select();
                document.execCommand('copy');
            }

            btn.textContent = 'Copied!';
            setTimeout(() => {
                btn.textContent = originalText;
            }, 1200);
        });
    });

    const uploadZone = document.getElementById('media-upload-zone');
    const uploadInput = document.getElementById('media-upload-input');
    const uploadForm = document.querySelector('.media-upload-form');

    function submitUpload() {
        if (uploadInput.files.length > 0) {
            uploadForm.submit();
        }
    }

    if (uploadZone && uploadInput && uploadForm) {
        uploadZone.addEventListener('click', () => {
            uploadInput.click();
        });

        uploadZone.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                uploadInput.click();
            }
        });

        uploadInput.addEventListener('change', () => {
            submitUpload();
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                uploadZone.classList.add('is-dragging');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                uploadZone.classList.remove('is-dragging');
            }, false);
        });

        uploadZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                uploadInput.files = files;
                submitUpload();
            }
        });
    }
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
