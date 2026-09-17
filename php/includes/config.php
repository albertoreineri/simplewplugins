<?php
/**
 * Site-wide configuration: brand, nav, and the plugin catalog.
 * Update `wporg` to the real wp.org slug once a plugin is approved and live;
 * until then it stays null and the card links to the in-site detail anchor instead.
 */

define('SITE_NAME', 'SimpleWPlugins');
define('SITE_TAGLINE', 'Small, focused WordPress plugins that do one thing well.');
define('SITE_URL', 'https://simplewplugins.com');

$SW_PLUGINS = [
    [
        'slug'    => 'simple-upload-weight-limit',
        'name'    => 'Simple Upload Weight Limit',
        'tagline' => 'Cap the maximum upload size for specific user roles, keep your server lean.',
        'wporg'   => 'simple-upload-weight-limit',
    ],
    [
        'slug'    => 'simple-auto-image-optimizer',
        'name'    => 'Simple Auto Image Optimizer',
        'tagline' => 'Auto-resize, compress and generate WebP sidecars on every upload.',
        'wporg'   => null,
    ],
    [
        'slug'    => 'simple-maintenance-mode',
        'name'    => 'Simple Maintenance Mode',
        'tagline' => 'An elegant coming-soon page with countdown, logo and color controls.',
        'wporg'   => null,
    ],
    [
        'slug'    => 'simple-smtp-lite',
        'name'    => 'Simple SMTP Lite',
        'tagline' => 'Lightweight SMTP delivery, credentials encrypted at rest. No bloat.',
        'wporg'   => null,
    ],
    [
        'slug'    => 'simple-external-links',
        'name'    => 'Simple External Links',
        'tagline' => 'Open outbound links in a new tab and add nofollow, automatically.',
        'wporg'   => null,
    ],
    [
        'slug'    => 'simple-excerpt-control',
        'name'    => 'Simple Excerpt Control',
        'tagline' => 'Set your excerpt length and "Read More" text with a clean slider.',
        'wporg'   => null,
    ],
    [
        'slug'    => 'simple-code-injector',
        'name'    => 'Simple Code Injector',
        'tagline' => 'Paste tracking codes and scripts into your header/footer. No theme editing.',
        'wporg'   => null,
    ],
    [
        'slug'    => 'simple-svg-enabler',
        'name'    => 'Simple SVG Enabler',
        'tagline' => 'Enable SVG uploads safely, with real allow-list sanitization.',
        'wporg'   => null,
    ],
];

/**
 * Simple nav used on every page.
 */
$SW_NAV = [
    '/'        => 'Home',
    '/blog/'   => 'Blog',
    '/about'   => 'About',
    '/contact' => 'Contact',
];

define('SITE_EMAIL', 'hello@simplewplugins.com');
