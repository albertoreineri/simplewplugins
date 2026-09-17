<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$page_title       = 'About — ' . SITE_NAME;
$page_description = 'Why SimpleWPlugins exists and how we build.';

require __DIR__ . '/includes/header.php';
?>
<article class="post-single wrap">
    <h1>About SimpleWPlugins</h1>

    <div class="post-body">
        <p>Most WordPress plugins try to do everything. You install something to resize images, and it also wants to manage your SEO, sell you a "Pro" upgrade in the dashboard, and add three new database tables you'll never clean up.</p>

        <p>SimpleWPlugins is the opposite bet: every plugin we ship does <strong>one thing</strong>, does it well, and gets out of your way. No upsells buried in the settings screen, no tracking, no bloat you didn't ask for. If a plugin needs a setting, it gets one screen. If it doesn't need a database table, it doesn't get one.</p>

        <h2>What that looks like in practice</h2>
        <p>Every plugin we publish is built around a few rules:</p>
        <ul>
            <li><strong>Zero-config where possible.</strong> Sensible defaults, so most sites can install and forget.</li>
            <li><strong>No dark patterns.</strong> We don't nag you into a paid tier you don't need, and we don't hide a "disable" button behind three menus.</li>
            <li><strong>Security first.</strong> Every input is sanitized, every output is escaped — the boring, unglamorous stuff that keeps your site safe.</li>
            <li><strong>Small footprint.</strong> If it doesn't need to run on every page load, it doesn't.</li>
        </ul>

        <h2>Who's behind it</h2>
        <p>SimpleWPlugins is an independent project — we build the plugins we wish existed for our own client work, then clean them up and share them with everyone else running WordPress.</p>

        <p>Have a plugin idea that fits this philosophy, or found a bug? <a href="/contact">Get in touch</a>.</p>
    </div>
</article>
<?php
require __DIR__ . '/includes/footer.php';
