<?php
$pageTitle = 'Pages — Fornesus Admin';
ob_start();
?>
<div class="admin-section">
    <span id="reorder-status" class="reorder-status" aria-live="polite"></span>
    <div class="admin-section-head">
        <h1 class="admin-heading">Pages</h1>
        <a href="/admin/pages/create" class="admin-btn">+ New Page</a>
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
                        <td><?= !empty($page['show_in_nav']) ? htmlspecialchars($page['nav_label'] ?: $page['title']) : '—' ?></td>
                        <td class="admin-actions">
                            <a href="/admin/pages/<?= (int) $page['id'] ?>/edit">Edit</a>
                            <form method="POST" action="/admin/pages/<?= (int) $page['id'] ?>/delete"
                                  onsubmit="return confirm('Delete this page and all of its sections?')">
                                <button type="submit" class="admin-del-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
