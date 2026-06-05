<?php
$pageTitle = 'Navigation — Fornesus Admin';
$bodyClass = 'admin-body';
$mainClass = 'admin-main admin-main-wide';
ob_start();
?>
<div class="admin-section nav-admin">
    <div class="admin-section-head">
        <div>
            <h1 class="admin-heading">Navigation</h1>
            <p class="admin-copy">Manage the public navigation order, visibility, and external links from one place.</p>
        </div>
    </div>

    <?php if ($navigationError === 'missing'): ?>
        <p class="admin-error">External links need both a label and a URL.</p>
    <?php elseif ($navigationError === 'url'): ?>
        <p class="admin-error">Please enter a valid external URL, including `https://`.</p>
    <?php elseif ($navigationError === 'migration'): ?>
        <p class="admin-error">Navigation management is not ready yet because the `navigation_items` table has not been migrated into this database.</p>
    <?php endif ?>

    <section class="nav-admin-form-shell" aria-labelledby="nav-external-heading">
        <div>
            <h2 class="admin-subheading" id="nav-external-heading">Add External Link</h2>
            <p class="admin-copy">External links can be shown, hidden, reordered, or deleted outright from this screen.</p>
        </div>
        <?php if (!$navigationReady): ?>
            <p class="admin-copy">This screen will become active after the navigation migration is applied. Public pages will continue using the legacy navigation until then.</p>
        <?php endif ?>
        <form method="POST" action="/admin/navigation/external" class="admin-form nav-admin-form">
            <div class="form-row">
                <label for="nav-external-label">Label *</label>
                <input id="nav-external-label" type="text" name="label" required <?= !$navigationReady ? 'disabled' : '' ?>>
            </div>
            <div class="form-row">
                <label for="nav-external-url">URL *</label>
                <input id="nav-external-url" type="url" name="url" placeholder="https://example.com" required <?= !$navigationReady ? 'disabled' : '' ?>>
            </div>
            <div class="form-row">
                <label for="nav-external-visibility">Initial Section</label>
                <select id="nav-external-visibility" name="visibility" <?= !$navigationReady ? 'disabled' : '' ?>>
                    <option value="visible">Visible</option>
                    <option value="hidden">Hidden</option>
                </select>
            </div>
            <label class="toggle-opt">
                <input type="checkbox" name="open_in_new_tab" value="1" <?= !$navigationReady ? 'disabled' : '' ?>>
                Open in a new tab
            </label>
            <div class="form-actions">
                <button type="submit" class="admin-btn" <?= !$navigationReady ? 'disabled' : '' ?>>Add Link</button>
            </div>
        </form>
    </section>

    <section class="nav-admin-board" aria-labelledby="nav-visible-heading">
        <div class="admin-section-head">
            <div>
                <h2 class="admin-subheading" id="nav-visible-heading">Visible</h2>
                <p class="admin-copy">These items currently appear in the public navigation. Drag to reorder them.</p>
            </div>
            <span id="reorder-status-visible" class="reorder-status" aria-live="polite"></span>
        </div>

        <?php if (empty($visibleItems)): ?>
            <p class="admin-empty">No visible navigation items yet.</p>
        <?php else: ?>
            <table class="admin-table nav-admin-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Label</th>
                        <th>Destination</th>
                        <th>Type</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody data-reorder-url="/admin/navigation/reorder" data-reorder-visibility="visible" data-reorder-status="reorder-status-visible">
                    <?php foreach ($visibleItems as $item): ?>
                        <tr data-id="<?= (int) $item['id'] ?>">
                            <td class="drag-handle" title="Drag to reorder">&#8597;</td>
                            <td><?= htmlspecialchars($item['label']) ?></td>
                            <td class="nav-admin-destination">
                                <span><?= htmlspecialchars($item['url']) ?></span>
                                <?php if ($item['target'] === '_blank'): ?>
                                    <span class="admin-hint">opens in new tab</span>
                                <?php endif ?>
                            </td>
                            <td><span class="nav-admin-type nav-admin-type-<?= htmlspecialchars($item['source_type']) ?>"><?= htmlspecialchars(ucfirst($item['source_type'])) ?></span></td>
                            <td class="admin-actions nav-admin-actions">
                                <form method="POST" action="/admin/navigation/<?= (int) $item['id'] ?>/toggle">
                                    <button type="submit" class="page-nav-toggle is-visible" title="Hide this navigation item" aria-label="Hide <?= htmlspecialchars($item['label']) ?>">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </form>
                                <?php if ($item['can_delete']): ?>
                                    <form method="POST" action="/admin/navigation/<?= (int) $item['id'] ?>/delete" onsubmit="return confirm('Delete this external link permanently?')">
                                        <button type="submit" class="nav-delete-btn" title="Delete this external link" aria-label="Delete <?= htmlspecialchars($item['label']) ?>">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        </button>
                                    </form>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php endif ?>
    </section>

    <section class="nav-admin-board" aria-labelledby="nav-hidden-heading">
        <div class="admin-section-head">
            <div>
                <h2 class="admin-subheading" id="nav-hidden-heading">Hidden</h2>
                <p class="admin-copy">Hidden items stay available here so they can be restored without using Trash.</p>
            </div>
            <span id="reorder-status-hidden" class="reorder-status" aria-live="polite"></span>
        </div>

        <?php if (empty($hiddenItems)): ?>
            <p class="admin-empty">No hidden navigation items.</p>
        <?php else: ?>
            <table class="admin-table nav-admin-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Label</th>
                        <th>Destination</th>
                        <th>Type</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody data-reorder-url="/admin/navigation/reorder" data-reorder-visibility="hidden" data-reorder-status="reorder-status-hidden">
                    <?php foreach ($hiddenItems as $item): ?>
                        <tr data-id="<?= (int) $item['id'] ?>">
                            <td class="drag-handle" title="Drag to reorder">&#8597;</td>
                            <td><?= htmlspecialchars($item['label']) ?></td>
                            <td class="nav-admin-destination">
                                <span><?= htmlspecialchars($item['url']) ?></span>
                                <?php if ($item['page_status'] === 'draft'): ?>
                                    <span class="admin-hint">page draft</span>
                                <?php elseif ($item['target'] === '_blank'): ?>
                                    <span class="admin-hint">opens in new tab</span>
                                <?php endif ?>
                            </td>
                            <td><span class="nav-admin-type nav-admin-type-<?= htmlspecialchars($item['source_type']) ?>"><?= htmlspecialchars(ucfirst($item['source_type'])) ?></span></td>
                            <td class="admin-actions nav-admin-actions">
                                <form method="POST" action="/admin/navigation/<?= (int) $item['id'] ?>/toggle">
                                    <button type="submit" class="page-nav-toggle is-hidden" title="Show this navigation item" aria-label="Show <?= htmlspecialchars($item['label']) ?>">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </form>
                                <?php if ($item['can_delete']): ?>
                                    <form method="POST" action="/admin/navigation/<?= (int) $item['id'] ?>/delete" onsubmit="return confirm('Delete this external link permanently?')">
                                        <button type="submit" class="nav-delete-btn" title="Delete this external link" aria-label="Delete <?= htmlspecialchars($item['label']) ?>">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        </button>
                                    </form>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php endif ?>
    </section>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
