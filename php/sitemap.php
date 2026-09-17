<?php
/**
 * Generates the XML sitemap on the fly from the static pages and the
 * blog posts under content/blog, so it never drifts out of sync with them.
 * Served at /sitemap.xml via the rewrite rule in .htaccess.
 */
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=UTF-8');

$urls = [
    ['loc' => SITE_URL . '/',        'changefreq' => 'monthly', 'priority' => '1.0'],
    ['loc' => SITE_URL . '/blog/',   'changefreq' => 'weekly',  'priority' => '0.8'],
    ['loc' => SITE_URL . '/about',   'changefreq' => 'yearly',  'priority' => '0.5'],
    ['loc' => SITE_URL . '/contact', 'changefreq' => 'yearly',  'priority' => '0.5'],
    ['loc' => SITE_URL . '/privacy', 'changefreq' => 'yearly',  'priority' => '0.3'],
];

foreach (sw_get_posts() as $post) {
    $urls[] = [
        'loc'        => SITE_URL . '/blog/' . $post['slug'],
        'lastmod'    => date('Y-m-d', strtotime($post['date'] ?? 'now')),
        'changefreq' => 'monthly',
        'priority'   => '0.6',
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
    <url>
        <loc><?php echo esc($u['loc']); ?></loc>
        <?php if (!empty($u['lastmod'])): ?><lastmod><?php echo esc($u['lastmod']); ?></lastmod><?php endif; ?>
        <changefreq><?php echo esc($u['changefreq']); ?></changefreq>
        <priority><?php echo esc($u['priority']); ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
