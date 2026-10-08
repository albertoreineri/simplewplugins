---
title: How to Optimize Images in WordPress Without Losing Quality
date: 2026-10-08
description: WordPress doesn't compress or convert your images on upload. Here's what it actually does, what it doesn't, and how to fix the gap with real tools.
---

Open your Media Library and sort by file size. On most sites, the top of that list is full of camera-original JPEGs and PNGs exported straight from a design tool — 4, 6, sometimes 10MB each. Every one of those gets downloaded by every visitor, on every page that uses it, and it's usually the single biggest reason a WordPress site feels slow.

## What WordPress actually does with your images

When you upload a file, WordPress doesn't just store it and move on. It generates several resized copies — thumbnail, medium, large, and whatever other sizes your theme or plugins have registered — so pages don't have to load the full original everywhere. You can see exactly which sizes are registered on your install with WP-CLI:

```bash
wp media image-size
```

That's genuinely useful, but it's also where WordPress core stops. It doesn't compress the original file beyond whatever quality setting it re-encodes JPEGs at (82, by default, for the generated sizes — the original you uploaded is kept untouched). It doesn't convert anything to a more efficient format automatically. And it doesn't strip the extra weight a lot of uploaded images carry, like embedded color profiles or camera metadata.

Since WordPress 5.8, you *can* upload `.webp` files directly and WordPress will handle them like any other image — but nothing converts your existing JPEGs and PNGs to WebP for you. That conversion step is still something you, or a plugin, have to do.

## The three things "optimizing an image" actually means

It's worth being specific, because "optimize your images" gets thrown around as a single piece of advice when it's really three separate jobs:

1. **Resize to the dimensions it's actually displayed at.** A 4000px-wide photo shown in a 800px-wide blog post is wasting most of its own data. WordPress's registered sizes handle this *if* your theme requests the right one — but an uploaded original that's far larger than any registered size still sits in your Media Library and can still get served if a theme or block requests "full size."
2. **Compress it.** Lossy compression (re-encoding a JPEG at a lower quality setting) can cut file size by 50-80% with no visible difference at normal viewing sizes. Lossless compression (stripping metadata, optimizing the file's internal structure) gives a smaller, guaranteed-no-quality-loss reduction on top.
3. **Serve it in a modern format.** WebP, and increasingly AVIF, produce meaningfully smaller files than JPEG or PNG at the same visual quality. Browsers that don't support them fall back to the original automatically when a plugin serves them correctly.

None of the three happens on its own. All three together are what actually move the needle on page weight.

![Diagram comparing an unoptimized image path, where a large camera-original JPEG is served as-is and loads slowly, with an optimized path where the same image is resized, compressed and converted to WebP before it reaches the visitor](/img/blog/wordpress-image-optimization-guide.svg "Unoptimized vs. optimized image delivery in WordPress")

## How to actually do it

**With a free-tier plugin.** [ShortPixel Image Optimizer](https://wordpress.org/plugins/shortpixel-image-optimiser/) handles JPG, PNG, GIF, PDF, WebP, AVIF and HEIC, with three compression modes (lossy, glossy, lossless) and bulk optimization for everything already in your Media Library, not just new uploads. Install it, go to **Settings > ShortPixel**, pick a compression mode, turn on WebP (or AVIF) delivery, then run the bulk optimizer once from the same screen to work through your existing images. [Imagify](https://wordpress.org/plugins/imagify/) (same company as WP Rocket) works the same way with simpler quality presets — Normal, Aggressive, Ultra — under **Media > Imagify**. [Smush](https://wordpress.org/plugins/wp-smush/) is the most beginner-friendly of the three, with one-click bulk compression under **Media > Smush**, though its free tier caps the file size it will touch.

**If you want compression to happen on your own server instead of a cloud API.** [EWWW Image Optimizer](https://wordpress.org/plugins/ewww-image-optimizer/) can compress images locally using tools it installs on your server, with no image ever leaving your hosting account — worth knowing if you're dealing with client images you can't send to a third-party service. It also supports WebP and AVIF conversion, configured under **Settings > EWWW Image Optimizer**.

**From the command line, after adding new theme sizes or changing settings.** If you've just registered a new image size (or changed a theme) and need existing uploads to get that new size without re-uploading everything:

```bash
# Only generate sizes that don't exist yet - fast, safe to re-run
wp media regenerate --only-missing

# Regenerate everything, including sizes that already exist
wp media regenerate --yes
```

WP-CLI itself doesn't compress or convert formats — it only rebuilds the resized copies from your original using whatever WordPress already knows how to do. For actual compression and WebP/AVIF conversion from the command line, the plugins above also expose WP-CLI commands once installed (for example, `wp shortpixel bulk` after installing ShortPixel); check the specific plugin's documentation for its exact command.

## Don't ignore lazy loading — but check your hero image

WordPress has added `loading="lazy"` to content images automatically since version 5.5, which is already a meaningful win: images below the fold don't download until the visitor is about to scroll to them. You don't need a plugin for this part — it's on by default.

The catch is the *first* image on the page — a hero banner or featured image — which you don't want lazy-loaded, since delaying it hurts your Largest Contentful Paint (LCP) score instead of helping it. WordPress 6.3 added automatic handling for this: core will skip lazy-loading and add `fetchpriority="high"` to one large image per page if it looks like the main above-the-fold image. It's a heuristic, though, and some themes or page builders can still get it wrong. If your LCP image is still being lazy-loaded according to a tool like PageSpeed Insights, you can check your theme's handling of `wp_get_loading_optimization_attributes()` or ask your theme developer.

## What to prioritize if you only do one thing

If your Media Library has years of uploads and you don't want to tackle all of it at once:

- Run a bulk optimization pass with one of the plugins above first — this alone usually cuts total image weight by half or more with zero visible quality change.
- Then go manually re-check the handful of images that get the most traffic: your homepage hero, your most-visited blog posts, your product photos if you run a store. Those are worth a closer look at compression quality, not just the default setting.
- Going forward, set a sensible upload size expectation for anyone who adds content to the site, so new uploads don't quietly undo the work — unedited phone photos and screen exports are almost always larger than they need to be.

## A simple checklist

- Install a bulk image optimization plugin (ShortPixel, Imagify, Smush, or EWWW if you need on-server compression) and run it once against your existing Media Library, not just new uploads.
- Turn on WebP or AVIF delivery in whichever plugin you pick — the format change alone is often the biggest single win.
- Leave native lazy loading on; it's already working for you since WordPress 5.5.
- Double-check your actual LCP image isn't being lazy-loaded, especially on a heavily customized homepage.
- Re-run the bulk optimizer periodically — it only catches images that existed when you last ran it.
