<?php
$pageTitle  = 'About — Fornesus Art';
$activePage = 'about';

ob_start();
?>
<div class="about-page">
    <div class="about-content">
        <?php foreach ($sections as $section): ?>
            <div class="bio-section">
                <?php if ($section['heading']): ?>
                    <h2 class="bio-heading"><?= htmlspecialchars($section['heading']) ?></h2>
                <?php endif ?>
                <div class="bio-text">
                    <?= nl2br(htmlspecialchars($section['content'])) ?>
                </div>
            </div>
        <?php endforeach ?>

        <?php if (empty($sections)): ?>
            <div class="bio-section">
                <div class="bio-text bio-placeholder">No biography has been written yet.</div>
            </div>
        <?php endif ?>

        <div class="contact-section">
            <h2 class="bio-heading">Correspondence</h2>

            <?php if ($contactSent): ?>
                <p class="contact-sent">Your message has been received.</p>
            <?php else: ?>
                <?php if ($contactError ?? null): ?>
                    <p class="contact-error"><?= htmlspecialchars($contactError) ?></p>
                <?php endif ?>
                <form class="contact-form" method="POST" action="/about">
                    <div class="field-row">
                        <label for="contact-name">Name</label>
                        <input
                            type="text"
                            id="contact-name"
                            name="name"
                            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                            autocomplete="name"
                        >
                    </div>
                    <div class="field-row">
                        <label for="contact-email">Email</label>
                        <input
                            type="email"
                            id="contact-email"
                            name="email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            autocomplete="email"
                        >
                    </div>
                    <div class="field-row">
                        <label for="contact-message">Message</label>
                        <textarea
                            id="contact-message"
                            name="message"
                            rows="6"
                        ><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="contact-submit">Send</button>
                </form>
            <?php endif ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
