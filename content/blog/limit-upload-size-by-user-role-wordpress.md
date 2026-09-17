---
title: How to Limit Upload Size by User Role in WordPress
date: 2026-09-16
excerpt: WordPress lets you cap the maximum upload size site-wide via php.ini, but not per user role out of the box. Here's how to actually do it.
plugin: simple-upload-weight-limit
---

If you run a multi-author site, a client site with several editors, or any WordPress install where more than one person can upload files, you've probably run into this: someone uploads a 12MB PNG straight out of their phone's camera roll, and suddenly your Media Library — and your hosting bill — start creeping up.

## Why the default upload limit isn't enough

WordPress does enforce a maximum upload size, but it's a single, site-wide number controlled by your hosting environment (`upload_max_filesize` and `post_max_size` in PHP, sometimes overridden by your host or your `.htaccess`). That number applies to *everyone* — your trusted admin uploading a hero image at the right size, and a brand-new contributor who has never heard of image compression, alike.

There's no built-in way to say "Administrators can upload anything, but Authors and Contributors are capped at 1MB." If you want that kind of role-based control, you need a plugin — or a few lines of code in your theme's `functions.php`, which is fine until a theme update wipes it out.

## The role-based approach

The logic you actually want is simple:

1. Pick a maximum file size (in KB) that makes sense for your site.
2. Let Administrators bypass it — they're the ones who actually need to upload full-resolution assets sometimes.
3. Give a clear error message to anyone else who tries to upload something oversized, so it doesn't look like a broken upload.

That's exactly what [Simple Upload Weight Limit](/#simple-upload-weight-limit) does: one settings screen, one number, one checkbox if you want the limit to apply to admins too. No new database tables, no background processes — it hooks into WordPress's own upload filter and checks the file size before it ever touches your disk.

## A quick gut-check before you set a number

A good starting point for most blog/content sites is **1024 KB (1MB)** — comfortably enough for a well-compressed JPEG or WebP image, tight enough to stop unedited camera originals from piling up. If your site is more visual (photography portfolios, real estate listings), you'll want to go higher; pair it with an image optimizer that automatically resizes and compresses on upload, so your contributors don't have to think about it at all.

That pairing — a sane upload cap plus automatic optimization — is the difference between a Media Library that stays healthy on its own and one you have to clean up by hand every few months.
