<?php
/**
 * Shared header. Expects $page_title (string) and optionally $page_description
 * to be set by the including page before requiring this file.
 */
$page_title       = $page_title ?? SITE_NAME;
$page_description = $page_description ?? SITE_TAGLINE;
$current_path     = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$canonical_url    = rtrim(SITE_URL, '/') . $current_path;
$og_image         = SITE_URL . '/assets/img/og-image.png';
$og_type          = isset($post) ? 'article' : 'website';

/**
 * JSON-LD graph: Organization + WebSite on every page, plus a BlogPosting
 * on post pages and an ItemList of the plugins on the homepage.
 */
$ld_graph = array_values(array_filter([
    [
        '@type' => 'Organization',
        '@id'   => SITE_URL . '/#organization',
        'name'  => SITE_NAME,
        'url'   => SITE_URL,
        'logo'  => $og_image,
    ],
    [
        '@type'     => 'WebSite',
        '@id'       => SITE_URL . '/#website',
        'name'      => SITE_NAME,
        'url'       => SITE_URL,
        'publisher' => ['@id' => SITE_URL . '/#organization'],
    ],
    isset($post) ? [
        '@type'         => 'BlogPosting',
        'headline'      => $post['title'],
        'description'   => $post['excerpt'] ?? SITE_TAGLINE,
        'datePublished' => $post['date'] ?? null,
        'url'           => $canonical_url,
        'author'        => ['@id' => SITE_URL . '/#organization'],
        'publisher'     => ['@id' => SITE_URL . '/#organization'],
    ] : null,
    ($current_path === '/' && isset($SW_PLUGINS)) ? [
        '@type'           => 'ItemList',
        'itemListElement' => array_values(array_map(function ($p, $i) {
            return [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'item'     => [
                    '@type'               => 'SoftwareApplication',
                    'name'                => $p['name'],
                    'description'         => $p['tagline'],
                    'applicationCategory' => 'WordPress Plugin',
                    'operatingSystem'     => 'WordPress',
                    'url'                 => $p['wporg']
                        ? 'https://wordpress.org/plugins/' . $p['wporg'] . '/'
                        : SITE_URL . '/#' . $p['slug'],
                ],
            ];
        }, $SW_PLUGINS, array_keys($SW_PLUGINS))),
    ] : null,
]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google tag (gtag.js) — gated by Consent Mode v2; see cookie banner in footer.php -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-T0WPTSSXM3"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}

      (function () {
        var granted = false;
        try { granted = localStorage.getItem('sw_consent') === 'granted'; } catch (e) {}
        var state = granted ? 'granted' : 'denied';
        gtag('consent', 'default', {
          'analytics_storage': state,
          'ad_storage': state,
          'ad_user_data': state,
          'ad_personalization': state
        });
      })();

      gtag('js', new Date());
      gtag('config', 'G-T0WPTSSXM3');
    </script>

    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <link rel="canonical" href="<?php echo esc($canonical_url); ?>">

    <meta property="og:type" content="<?php echo esc($og_type); ?>">
    <meta property="og:site_name" content="<?php echo esc(SITE_NAME); ?>">
    <meta property="og:title" content="<?php echo esc($page_title); ?>">
    <meta property="og:description" content="<?php echo esc($page_description); ?>">
    <meta property="og:url" content="<?php echo esc($canonical_url); ?>">
    <meta property="og:image" content="<?php echo esc($og_image); ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc($page_title); ?>">
    <meta name="twitter:description" content="<?php echo esc($page_description); ?>">
    <meta name="twitter:image" content="<?php echo esc($og_image); ?>">

    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/img/favicon.png">
    <link rel="stylesheet" href="/assets/css/style.css">

    <script type="application/ld+json"><?php echo json_encode(['@context' => 'https://schema.org', '@graph' => $ld_graph], JSON_UNESCAPED_SLASHES); ?></script>
</head>
<body>
    <header class="site-header">
        <div class="wrap site-header-inner">
            <a href="/" class="brand">
                <span class="brand-dot"></span>SimpleWPlugins
            </a>
            <nav>
                <?php foreach ($SW_NAV as $href => $label): ?>
                    <a href="<?php echo esc($href); ?>" class="<?php echo $current_path === $href ? 'active' : ''; ?>"><?php echo esc($label); ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
    </header>
    <main>
