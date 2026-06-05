<?php
$pageTitle = 'Media Library — Fornesus Art Admin';
ob_start();
?>
<style>
/* Overrides standard admin layout maximum width specifically for the media library to feel spacious */
.admin-main {
    max-width: 1200px;
}
</style>

<div class="admin-section media-workspace-root">
    <div class="admin-section-head">
        <h1 class="admin-heading">Media Library</h1>
        <span class="admin-hint"><?= count($files) ?> file<?= count($files) !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (isset($_GET['uploaded'])): ?>
        <p class="admin-hint" style="color: var(--amber); font-style: normal; margin-top: -1rem; margin-bottom: 1rem;">Asset uploaded successfully.</p>
    <?php endif ?>
    <?php if (isset($_GET['error'])): ?>
        <p class="admin-error" style="margin-top: -1rem; margin-bottom: 1rem;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif ?>

    <!-- Upload Zone -->
    <div class="media-upload-zone" id="media-upload-zone">
        <form method="POST" action="/admin/media/upload" enctype="multipart/form-data" style="display:none;">
            <input type="file" name="media_file" id="media-upload-input" accept="image/*">
        </form>
        <span class="media-upload-text">Drag & drop an image here, or click to browse</span>
        <span class="media-upload-hint">Supports JPEG, PNG, GIF, WEBP, AVIF</span>
    </div>

    <!-- Main Split Workspace -->
    <div class="media-workspace">
        <div class="media-grid-container">
            <?php if (empty($files)): ?>
                <p class="admin-empty">No uploaded files yet.</p>
            <?php else: ?>
                <div class="media-grid-compact">
                    <?php foreach ($files as $f): ?>
                        <div class="media-tile"
                             data-id="<?= (int) $f['id'] ?>"
                             data-mime="<?= htmlspecialchars($f['mime_type'] ?? '') ?>"
                             data-date="<?= date('Y-m-d', strtotime($f['created_at'])) ?>">
                            <div class="media-tile-thumb">
                                <img src="/image/<?= (int) $f['id'] ?>"
                                     alt=""
                                     loading="lazy"
                                     onerror="this.parentElement.classList.add('media-thumb-missing')">
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
        </div>

        <!-- Sticky Details Panel -->
        <div class="media-details-panel">
            <div class="media-details-preview">
                <img id="details-preview-img" src="" alt="" style="display: none;">
            </div>
            
            <div class="media-details-placeholder" id="details-placeholder">
                Select an asset to view details.
            </div>

            <div class="media-details-content" id="details-content-area" style="display: none; flex-direction: column;">
                <h3 class="media-details-title">Asset Details</h3>
                
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

                <div class="media-details-code">
                    <label>Direct URL</label>
                    <div class="media-code-input-wrap">
                        <input type="text" id="input-url" readonly>
                        <button type="button" class="copy-btn" data-copy-target="input-url">Copy</button>
                    </div>
                </div>

                <div class="media-details-code">
                    <label>HTML Embed Code</label>
                    <div class="media-code-input-wrap">
                        <input type="text" id="input-html" readonly>
                        <button type="button" class="copy-btn" data-copy-target="input-html">Copy</button>
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
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tiles = document.querySelectorAll('.media-tile');
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

    function selectTile(tile) {
        tiles.forEach(t => t.classList.remove('active'));
        tile.classList.add('active');

        const id = tile.dataset.id;
        const mime = tile.dataset.mime;
        const date = tile.dataset.date;
        const imgUrl = `/image/${id}`;

        // Update preview image
        previewImg.src = imgUrl;
        previewImg.style.display = 'block';

        // Update metadata
        metaId.textContent = id;
        metaMime.textContent = mime;
        metaDate.textContent = date;

        // Update code snippets
        inputUrl.value = window.location.origin + imgUrl;
        inputHtml.value = `<img src="${imgUrl}" alt="">`;

        // Update form actions
        trashForm.action = `/admin/media/${id}/trash`;
        destroyForm.action = `/admin/media/${id}/destroy`;

        // Show details content, hide placeholder
        placeholderText.style.display = 'none';
        contentArea.style.display = 'flex';
    }

    tiles.forEach(tile => {
        tile.addEventListener('click', () => {
            selectTile(tile);
        });
    });

    // Auto-select first tile if available
    if (tiles.length > 0) {
        selectTile(tiles[0]);
    } else {
        placeholderText.textContent = 'No assets in library.';
    }

    // Confirm dialogs
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

    // Copy buttons
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const targetId = btn.dataset.copyTarget;
            const input = document.getElementById(targetId);
            input.select();
            document.execCommand('copy');
            
            const originalText = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(() => {
                btn.textContent = originalText;
            }, 1200);
        });
    });

    // Drag and drop upload zone trigger
    const uploadZone = document.getElementById('media-upload-zone');
    const uploadInput = document.getElementById('media-upload-input');

    if (uploadZone && uploadInput) {
        uploadZone.addEventListener('click', () => {
            uploadInput.click();
        });

        uploadInput.addEventListener('change', () => {
            if (uploadInput.files.length > 0) {
                uploadZone.querySelector('form').submit();
            }
        });

        // Highlight drag and drop
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                uploadZone.style.borderColor = 'var(--amber)';
                uploadZone.style.background = 'var(--amber-glow)';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                uploadZone.style.borderColor = '';
                uploadZone.style.background = '';
            }, false);
        });

        uploadZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                uploadInput.files = files;
                uploadZone.querySelector('form').submit();
            }
        });
    }
});
</script>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layout.php';
