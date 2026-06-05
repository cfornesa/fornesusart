<?php
$pageTitle        = ($page['meta_title'] ?: $page['title']) . ' — Fornesus Art';
$activePage       = $page['slug'];
$metaTitle        = $page['meta_title'] ?: $page['title'] . ' — Fornesus Art';
$metaDescription  = $page['meta_description'] ?: seo_excerpt($sections[0]['content'] ?? '', 170) ?: 'A page from the Fornesus Art archive.';
$ogTitle          = $page['og_title'] ?: $metaTitle;
$ogDescription    = $page['og_description'] ?: $metaDescription;
$metaImage        = $page['og_image'] ?: null;
$metaImageAlt     = $page['title'] ?: 'Page preview';
$canonicalUrl     = seo_absolute_url('/' . $page['slug']);

ob_start();
?>
<div class="page-shell<?= $page['template'] === 'contact' ? ' page-shell-contact' : '' ?>">
    <header class="page-header">
        <h1 class="page-title"><?= htmlspecialchars($page['title']) ?></h1>
    </header>

    <div class="page-content">
        <?php foreach ($sections as $section): ?>
            <section class="page-section"<?= $section['heading'] ? ' aria-labelledby="page-section-' . (int) $section['id'] . '"' : '' ?>>
                <?php if ($section['heading']): ?>
                    <h2 class="page-section-heading" id="page-section-<?= (int) $section['id'] ?>"><?= htmlspecialchars($section['heading']) ?></h2>
                <?php endif ?>
                <div class="page-section-body">
                    <?= $section['content'] ?>
                </div>
            </section>
        <?php endforeach ?>

        <?php if (empty($sections)): ?>
            <section class="page-section">
                <div class="page-section-body page-placeholder">This page has not been written yet.</div>
            </section>
        <?php endif ?>

        <?php if ($page['template'] === 'contact'): ?>
            <section class="contact-section" aria-labelledby="contact-section-heading">
                <h2 class="page-section-heading" id="contact-section-heading">Correspondence</h2>

                <?php if ($contactSent): ?>
                    <p class="contact-sent" role="status">Your message has been received.</p>
                <?php else: ?>
                    <?php if ($contactError ?? null): ?>
                        <p class="contact-error" role="alert"><?= htmlspecialchars($contactError) ?></p>
                    <?php endif ?>
                    <form class="contact-form" method="POST" action="/contact">
                        <div class="field-row">
                            <label for="contact-name">Name</label>
                            <input
                                type="text"
                                id="contact-name"
                                name="name"
                                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                autocomplete="name"
                                autocapitalize="words"
                                spellcheck="false"
                                required
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
                                inputmode="email"
                                autocapitalize="off"
                                spellcheck="false"
                                required
                            >
                        </div>
                        <div class="field-row">
                            <label for="contact-message">Message</label>
                            <textarea
                                id="contact-message"
                                name="message"
                                rows="6"
                                autocomplete="on"
                                autocapitalize="sentences"
                                spellcheck="true"
                                enterkeyhint="send"
                                required
                            ><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="contact-submit">Send</button>
                    </form>
                <?php endif ?>
            </section>
        <?php endif ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
