<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$page_title       = SITE_NAME . ' — Small WordPress plugins that do one thing well';
$page_description = SITE_TAGLINE;

require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="wrap">
        <h1>Small, focused WordPress plugins.</h1>
        <p><?php echo esc(SITE_TAGLINE); ?> No bloat, no upsells buried in your dashboard — just the one feature you installed it for.</p>
    </div>
</section>

<section class="wrap">
    <div class="plugin-grid">
        <?php foreach ($SW_PLUGINS as $p): ?>
            <?php
            $href = $p['wporg']
                ? 'https://wordpress.org/plugins/' . $p['wporg'] . '/'
                : '#' . $p['slug'];
            ?>
            <a class="plugin-card" href="<?php echo esc($href); ?>" <?php echo $p['wporg'] ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                <img src="/assets/img/plugins/<?php echo esc($p['slug']); ?>.png" alt="<?php echo esc($p['name']); ?>">
                <h3><?php echo esc($p['name']); ?></h3>
                <p><?php echo esc($p['tagline']); ?></p>
                <span class="badge <?php echo $p['wporg'] ? 'live' : 'soon'; ?>">
                    <?php echo $p['wporg'] ? 'Get it on WordPress.org' : 'Coming soon'; ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php
require __DIR__ . '/includes/footer.php';
