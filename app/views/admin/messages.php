<?php
$pageTitle = 'Messages — Fornesus Art Admin';
ob_start();
?>
<div class="admin-section">
    <h1 class="admin-heading">Correspondence</h1>
    <?php if (empty($messages)): ?>
        <p class="admin-empty">No messages yet.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Message</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['name']) ?></td>
                        <td><?= htmlspecialchars($m['email']) ?></td>
                        <td><?= nl2br(htmlspecialchars($m['message'])) ?></td>
                        <td><?= htmlspecialchars($m['created_at']) ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
