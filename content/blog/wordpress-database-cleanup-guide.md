---
title: How to Clean Up a Bloated WordPress Database Without Breaking Anything
date: 2026-11-05
description: WordPress never deletes most of what it writes to the database. Here's what actually piles up, what's safe to remove, and the real commands to do it.
---

Open phpMyAdmin on a WordPress site that's a few years old and has had a handful of plugins come and go, and `wp_options` is usually the first thing that looks wrong — thousands of rows, most of them meaningless to you, some left behind by plugins you uninstalled two years ago. Next to it, `wp_posts` is padded out with hundreds of autosaved revisions of pages nobody remembers editing, and `wp_postmeta` has an orphaned row for every one of them.

None of this happened because something broke. WordPress is designed to write a revision on every autosave, store a transient for every scheduled cache, and leave plugin settings behind when you deactivate something, because removing them automatically would risk removing data you still want. The tradeoff is that nothing gets cleaned up unless you do it, and on a site that's been live for a while, that adds up to a database that's slower to query than it needs to be.

## What's actually piling up

A handful of WordPress behaviors account for almost all of the bloat:

- **Post revisions.** Every time a post or page autosaves, WordPress writes a full new row to `wp_posts`. A page that's been edited 40 times over a year can carry 40 revision rows, each with its own `wp_postmeta` entries.
- **Expired transients.** Transients are WordPress's built-in temporary cache (used by plugins and core for things like API responses or feed data). They're supposed to expire and get cleared automatically, but WordPress only deletes an expired transient the next time something tries to *read* it — if nothing reads it again, it sits in `wp_options` forever.
- **Orphaned postmeta and leftover plugin options.** Deleting a plugin removes its files, not its database rows, unless the plugin specifically cleans up after itself on uninstall (most don't by default).
- **Spam and trashed comments.** Akismet and similar tools catch spam, but by default WordPress keeps it in the `wp_comments` table rather than deleting it outright.

Of these, the one that actually affects performance the most is the one people check least: **autoloaded options**.

## The real bottleneck: autoloaded options, not table size

A `wp_options` table with 50,000 rows isn't automatically a problem. What matters is how many of those rows are marked `autoload = 'yes'` — because WordPress loads every single one of them into memory on *every single page load*, before it runs a single line of your theme or plugin code. A handful of plugins that each leave a few hundred KB of autoloaded JSON behind can add up to a multi-megabyte payload loaded on every request, independent of caching, independent of your hosting plan.

WordPress's own Site Health tool flags this directly: go to **Tools → Site Health → Info → Server**, and look for the "Autoloaded options" figure. As a rule of thumb, you want that number under roughly 800 KB; Site Health marks it as a problem once it gets meaningfully larger than that.

To find out which options are actually responsible, run this from your site's root via WP-CLI (if your host gives you SSH access):

```
wp db query "SELECT option_name, LENGTH(option_value) AS size_bytes FROM wp_options WHERE autoload = 'yes' ORDER BY size_bytes DESC LIMIT 20;"
```

That gives you a ranked list. It's very common to find the top few entries belong to a plugin you no longer use — at that point you can safely delete that specific row with `wp option delete <option_name>`, rather than guessing at a full cleanup.

## Back up before you touch anything

This is the one step that actually matters more than the cleanup itself. Before running any delete or optimize command, export a copy of the database:

```
wp db export wp-backup-$(date +%F).sql
```

That writes a plain SQL dump you can restore from if something you delete turns out to matter. If you don't have WP-CLI access, your hosting control panel almost always has a "backup database" or phpMyAdmin "Export" option that does the same thing — use it first, every time.

## Cleaning it up with WP-CLI

If you have shell access, these are the actual commands, run in order:

**Delete expired transients:**
```
wp transient delete --expired
```

**Delete post revisions.** WordPress doesn't ship a one-line command for this, but you can list every revision and delete it:
```
wp post list --post_type='revision' --format=ids | xargs wp post delete --force
```
(`--force` skips the trash and deletes permanently — make sure your backup from the previous step already ran.)

**Empty spam and trashed comments:**
```
wp comment delete $(wp comment list --status=spam --format=ids)
wp comment delete $(wp comment list --status=trash --format=ids)
```

**Optimize the tables after deleting rows.** Deleting rows doesn't shrink the actual table file on disk — `OPTIMIZE TABLE` reclaims that space:
```
wp db optimize
```
This runs `mysqlcheck` with the optimize flag across your tables. Note it's not the same as `wp db repair`, which only works on MyISAM/ARCHIVE/CSV tables — most modern WordPress installs run InnoDB, so repair rarely applies, but optimize does.

## Doing it without the command line

If SSH access isn't available, a plugin is the practical option. Three real ones, with real differences:

- **WP-Optimize** (free, with paid tiers) — the most all-in-one option: cleans revisions, transients, and spam comments, runs table optimization, and bundles page caching and image compression in the same plugin. Good if you want one tool instead of several.
- **Advanced Database Cleaner** (free, with paid tiers) — more granular. It lists out every category it can clean (revisions, auto-drafts, orphaned postmeta, transients, and more) individually and lets you schedule recurring cleanups, e.g. "delete revisions older than 30 days, every week."
- **WP-Sweep** (free) — the simplest of the three. It uses WordPress's own delete functions rather than raw SQL queries, which is a safer approach at the cost of fewer scheduling and automation options.

Whichever you use, run its backup step (most offer one) or do a manual export first, and start with a dry run or preview if the plugin offers one before you commit to deleting anything.

![Diagram showing WordPress revisions, transients, and leftover plugin options accumulating in the database, cleaned up through either WP-CLI commands or a database cleanup plugin](/img/blog/wordpress-database-cleanup-guide.svg "What bloats a WordPress database, and the two ways to clean it")

## How often you actually need to do this

There's no fixed schedule that fits every site, but as a practical baseline:

- A low-traffic brochure site with one or two editors: checking autoloaded options and revisions every few months is plenty.
- A multi-author site or one running WooCommerce, where transients and postmeta grow fast: monthly, ideally automated via a scheduled WP-CLI command or the cleaner plugin's own scheduler.
- Right after removing a plugin: always worth a quick check of `wp_options` for anything it left behind, since that's the single most common source of long-term bloat.

The goal isn't an empty database — revisions and transients exist for good reasons while they're in use. It's making sure nothing keeps consuming memory and query time long after the plugin or edit that created it is gone.
