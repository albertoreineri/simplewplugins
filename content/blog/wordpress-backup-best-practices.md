---
title: How to Back Up a WordPress Site (And Make Sure It Actually Works)
date: 2026-09-17
description: WordPress has no built-in backup system. Here's what you actually need to back up, where to keep it, and the one step almost everyone skips.
---

At some point, something is going to go wrong with your WordPress site — a bad plugin update, a hosting outage, a hacked login, or just a careless edit that breaks the homepage. When that happens, your backup is the only thing standing between "five minutes of downtime" and "starting over from scratch." Yet a huge number of sites are running with no real backup strategy at all, or one that was set up once, years ago, and never checked since.

## WordPress doesn't back itself up

There's no setting in WordPress core that saves copies of your site. Nothing happens automatically unless your host does it for you or you've installed something that does. That surprises a lot of people, because everything else about running a WordPress site feels self-contained — themes, plugins, updates, all managed from one dashboard. Backups are the exception: they're entirely on you.

## What you actually need to back up

A WordPress site is really two things, and you need both:

- **The database** — your posts, pages, comments, users, and every setting stored under the hood (including plugin and theme configuration, stored in the `wp_options` table and similar).
- **The files in `wp-content`** — your themes, your plugins, and critically, the `uploads` folder, where every image, PDF, and other media file you've ever added lives.

Back up only the database, and you'll restore a site with broken images and missing files. Back up only the files, and you'll restore a site with no posts, no settings, and no users. You need a full copy of both, taken at roughly the same time, to have something you can actually restore from.

It's also worth keeping a copy of `wp-config.php`, since it holds your database connection details and security keys — useful if you're rebuilding a site from scratch rather than restoring it in place.

## Don't store your only backup on the same server

This is the mistake that turns a bad day into a disaster: a backup that lives in the same hosting account as the site it's backing up. If your server gets compromised, wiped, or simply goes offline for good, your backup disappears with it.

Your backup copies should live somewhere else entirely — cloud storage, a separate server, or a dedicated backup service. Most backup tools support pushing straight to services like Google Drive, Dropbox, or S3-compatible storage for exactly this reason. If your only copy is sitting in the same `wp-content` folder as the rest of your site, it isn't really a backup.

## How often, and how many copies to keep

The right frequency depends on how often your content changes:

- A blog, news site, or anything with daily editorial activity: **daily backups**.
- A brochure or portfolio site that changes rarely: **weekly** is usually enough.
- An online store or membership site with constant orders and user activity: consider more frequent database backups than file backups, since that's where the fast-changing data lives.

Keep more than one restore point. If you only keep last night's backup, you have no way to recover from a problem that's been quietly corrupting content for a few days before you noticed it. A rotating set of backups — a few days, a few weeks — gives you room to go back further if needed.

## The step almost everyone skips: testing the restore

A backup you've never restored is a backup you're only assuming works. Files can be incomplete, database exports can be truncated, storage connections can silently fail one day without an error anyone sees. The only way to know your backup is good is to actually use it.

Every so often — quarterly is a reasonable cadence for most sites — restore your latest backup to a staging site or a local environment and check that it comes up clean: pages load, images render, you can log in. It takes half an hour and it's the difference between trusting your backup and hoping it works when you finally need it.

## A simple checklist

- Back up both the database and `wp-content` (especially `uploads`), on a schedule that matches how often your content changes.
- Store copies off-site, never only on the same server as your site.
- Keep multiple restore points, not just the most recent one.
- Test a real restore periodically, don't just assume the backup succeeded.
- Exclude cache and log files from the backup — they bloat the file size without adding anything worth restoring.

None of this is complicated, but it only pays off if it's actually in place before the day you need it.
