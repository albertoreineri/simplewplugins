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

## How to actually do it: three ways to back up WordPress

**With a free plugin.** [UpdraftPlus](https://wordpress.org/plugins/updraftplus/) is the most-installed backup plugin on WordPress.org, and the free version already covers the basics: install it, go to **Settings > UpdraftPlus Backups > Settings**, pick a remote storage destination (Google Drive, Dropbox, and S3 all work on the free tier), set a schedule for both files and database, then click **Backup Now** once to confirm it actually completes and lands in that remote storage. [Duplicator](https://wordpress.org/plugins/duplicator/) is a solid free alternative, built more around packaging a full copy of your site — database and files in one bundle — that doubles as a migration tool, not just a restore point.

**With a paid plugin or service.** Once a site matters enough that you don't want to think about backups at all, paying for one removes the manual setup: [UpdraftPlus Premium](https://updraftplus.com/), [WPvivid Backup Pro](https://wpvivid.com/), and [BlogVault](https://blogvault.net/) all add automatic off-site storage included in the price, incremental backups (so a 5GB site doesn't re-upload 5GB every night), and one-click restores without needing SSH or phpMyAdmin. BlogVault in particular runs the backup process on its own servers instead of your host's, which matters if your hosting is already struggling under load.

**By hand, with WP-CLI.** If you're comfortable in a terminal and just want a quick manual backup — before a risky update, for example — you don't need a plugin at all:

```bash
# Export the database
wp db export backup-db.sql

# Archive the wp-content folder (themes, plugins, uploads)
tar -czf backup-wp-content.tar.gz wp-content/

# Bundle both into one file to move off-server
zip backup-full.zip backup-db.sql backup-wp-content.tar.gz
```

Then get `backup-full.zip` off the server — download it over SFTP, or `scp` it straight to another machine. This isn't something you'd run manually every day, but it's the fastest way to have a real, working backup in hand in under a minute.

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
