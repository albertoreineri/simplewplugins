<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$page_title       = 'Contact — ' . SITE_NAME;
$page_description = 'Get in touch with SimpleWPlugins.';

require __DIR__ . '/includes/header.php';
?>
<article class="post-single wrap">
    <h1>Contact</h1>

    <div class="post-body">
        <p>Questions, plugin ideas, partnership inquiries, or just want to say hi — email us directly:</p>

        <p style="font-size:1.4rem;font-weight:700;margin:24px 0;">
            <a href="mailto:<?php echo esc(SITE_EMAIL); ?>"><?php echo esc(SITE_EMAIL); ?></a>
        </p>

        <h2>Looking for support on a specific plugin?</h2>
        <p>For bug reports or help with a plugin you've installed, the fastest way to reach us is the <strong>Support</strong> tab on that plugin's WordPress.org page — we monitor those directly, and it keeps a public record other users can search before asking the same question.</p>
    </div>
</article>
<?php
require __DIR__ . '/includes/footer.php';
