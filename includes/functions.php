<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Shorthand HTML-escaping helper used throughout the templates.
 */
function esc($str) {
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}

/**
 * Splits a markdown file into [meta array, remaining markdown body].
 * Front matter looks like:
 *   ---
 *   title: Post title
 *   date: 2026-09-16
 *   excerpt: One line summary.
 *   plugin: simple-upload-weight-limit
 *   ---
 *   Body in markdown starts here.
 */
function sw_parse_frontmatter($raw) {
    $meta = [];
    if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $raw, $m)) {
        foreach (explode("\n", trim($m[1])) as $line) {
            if (strpos($line, ':') === false) {
                continue;
            }
            [$key, $value] = array_map('trim', explode(':', $line, 2));
            $meta[$key] = $value;
        }
        return [$meta, $m[2]];
    }
    return [$meta, $raw];
}

/**
 * Returns every blog post's metadata (not the parsed body, for speed on the
 * listing page), sorted by date descending.
 */
function sw_get_posts() {
    $dir   = __DIR__ . '/../content/blog';
    $posts = [];

    foreach (glob($dir . '/*.md') as $file) {
        $raw = file_get_contents($file);
        [$meta] = sw_parse_frontmatter($raw);
        $meta['slug'] = basename($file, '.md');
        $posts[] = $meta;
    }

    usort($posts, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
    return $posts;
}

/**
 * Returns one post's full metadata + rendered HTML body, or null if the
 * slug doesn't exist. $slug is validated against a strict whitelist pattern
 * before ever touching the filesystem.
 */
function sw_get_post($slug) {
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        return null;
    }

    $file = __DIR__ . '/../content/blog/' . $slug . '.md';
    if (!file_exists($file)) {
        return null;
    }

    $raw = file_get_contents($file);
    [$meta, $body] = sw_parse_frontmatter($raw);
    $meta['slug'] = $slug;

    $parsedown = new Parsedown();
    $meta['html'] = $parsedown->text($body);

    return $meta;
}

/**
 * Looks up a plugin's catalog entry by slug.
 */
function sw_find_plugin($slug) {
    global $SW_PLUGINS;
    foreach ($SW_PLUGINS as $p) {
        if ($p['slug'] === $slug) {
            return $p;
        }
    }
    return null;
}
