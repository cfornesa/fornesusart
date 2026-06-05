<?php
$pageTitle   = 'Pages — Fornesus Admin';
$trashedCount = Page::trashedCount();
ob_start();
?>
<div class="admin-section">
    <span id="reorder-status" class="reorder-status" aria-live="polite"></span>
    <div class="admin-section-head">
        <h1 class="admin-heading">Pages</h1>
        <div style="display:flex;gap:0.8rem;align-items:center">
            <?php if ($trashedCount > 0): ?>
                <a href="/admin/pages/trash" class="admin-btn admin-btn-ghost admin-btn-sm">
                    Trash <?= $trashedCount ?>
                </a>
            <?php else: ?>
                <a href="/admin/pages/trash" class="admin-btn admin-btn-ghost admin-btn-sm">Trash</a>
            <?php endif ?>
            <a href="/admin/pages/create" class="admin-btn">+ New Page</a>
        </div>
    </div>

    <?php if (empty($pages)): ?>
        <p class="admin-empty">No pages yet.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Template</th>
                    <th>Status</th>
                    <th>Nav</th>
                    <th></th>
                </tr>
            </thead>
            <tbody data-reorder-url="/admin/pages/reorder">
                <?php foreach ($pages as $page): ?>
                    <tr data-id="<?= (int) $page['id'] ?>">
                        <td class="drag-handle" title="Drag to reorder">&#8597;</td>
                        <td><?= htmlspecialchars($page['title']) ?></td>
                        <td>/<?= htmlspecialchars($page['slug']) ?></td>
                        <td><?= htmlspecialchars($page['template']) ?></td>
                        <td><?= htmlspecialchars($page['status']) ?></td>
                        <td class="page-nav-cell">
                            <button type="button"
                                    class="page-nav-toggle <?= !empty($page['show_in_nav']) ? 'is-visible' : 'is-hidden' ?>"
                                    data-id="<?= (int) $page['id'] ?>"
                                    title="<?= !empty($page['show_in_nav']) ? 'Visible in nav — click to hide' : 'Hidden from nav — click to show' ?>"
                                    aria-pressed="<?= !empty($page['show_in_nav']) ? 'true' : 'false' ?>">
                                <?php if (!empty($page['show_in_nav'])): ?>
                                    <!-- eye open -->
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <?php else: ?>
                                    <!-- eye off -->
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                <?php endif ?>
                            </button>
                            <?php if (!empty($page['show_in_nav'])): ?>
                                <span class="admin-hint"><?= htmlspecialchars($page['nav_label'] ?: $page['title']) ?></span>
                            <?php endif ?>
                        </td>
                        <td class="admin-actions">
                            <a href="/admin/pages/<?= (int) $page['id'] ?>/edit">Edit</a>
                            <form method="POST" action="/admin/pages/<?= (int) $page['id'] ?>/delete"
                                  onsubmit="return confirm('Move this page and all of its sections to the trash?')">
                                <button type="submit" class="admin-del-btn">Move to Trash</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>
</div>

<style>
.page-nav-cell { display: flex; align-items: center; gap: 0.5rem; }
.page-nav-toggle {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.15rem;
    line-height: 0;
    color: var(--amber);
    opacity: 1;
    transition: opacity 0.15s;
}
.page-nav-toggle.is-hidden { color: var(--mid); opacity: 0.45; }
.page-nav-toggle:hover { opacity: 1; }
</style>

<script>
document.querySelectorAll('.page-nav-toggle').forEach(btn => {
    btn.addEventListener('click', async () => {
        const id = btn.dataset.id;
        btn.disabled = true;
        try {
            const res = await fetch(`/admin/pages/${id}/toggle-nav`, { method: 'POST' });
            const data = await res.json();
            if (!data.ok) return;

            const isVisible = data.show_in_nav === 1;
            btn.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
            btn.title = isVisible ? 'Visible in nav — click to hide' : 'Hidden from nav — click to show';
            btn.className = 'page-nav-toggle ' + (isVisible ? 'is-visible' : 'is-hidden');

            const eyeOpen = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
            const eyeOff  = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
            btn.innerHTML = isVisible ? eyeOpen : eyeOff;

            // update nav label hint in the same cell
            const cell = btn.closest('td');
            let hint = cell.querySelector('.admin-hint');
            if (isVisible) {
                if (!hint) {
                    hint = document.createElement('span');
                    hint.className = 'admin-hint';
                    cell.appendChild(hint);
                }
                // fetch updated label from the row title as fallback
                const row = btn.closest('tr');
                hint.textContent = row.querySelector('td:nth-child(2)').textContent;
            } else {
                if (hint) hint.remove();
            }
        } finally {
            btn.disabled = false;
        }
    });
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
