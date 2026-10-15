---
title: Why WordPress Scheduled Tasks Don't Run On Time (And How to Fix WP-Cron)
date: 2026-10-15
description: WordPress doesn't have a real cron job — it fakes one on page loads. Here's why scheduled tasks drift or silently stop, and how to fix it properly.
---

Your scheduled post didn't publish at 9am. Your backup plugin skipped last night's run. A subscription renewal email went out six hours late. If any of that sounds familiar, the cause is almost never the plugin you're blaming — it's how WordPress decides *when* to run scheduled tasks in the first place.

## WordPress doesn't have a real cron job

Linux has `cron`, a system scheduler that runs tasks at exact times whether anyone is using the server or not. WordPress has something that looks like cron and is even named after it, but works completely differently: **WP-Cron**. It's not a background process running continuously — it's a PHP script, `wp-cron.php`, that only runs when something triggers it.

And the thing that triggers it, by default, is a visitor loading your site.

## How wp-cron.php actually works

Every time someone requests a page on your site, WordPress checks: is any scheduled task overdue, and has it been at least 60 seconds since the last check? If both are true, it fires off a non-blocking HTTP request to `wp-cron.php` in the background, which then runs whatever tasks are due — publishing scheduled posts, checking for plugin/theme updates, firing a backup plugin's nightly job, cleaning up transients, and so on.

This works fine as long as your site gets a steady stream of visitors. It falls apart the moment it doesn't.

## Why it breaks: low traffic, caching, and shared hosting

A few specific situations make WP-Cron unreliable:

- **Low-traffic sites.** No visitor, no page load, no trigger. A task scheduled for 3am might not actually fire until the first visitor shows up at 9am — or later.
- **Full-page caching.** If a caching plugin or your host serves a cached HTML page without WordPress ever bootstrapping, the WP-Cron check never runs. Busy sites with aggressive caching can paradoxically have *worse* cron reliability than quiet ones.
- **A single slow or failed request.** Because the trigger request has to actually complete, a timeout, a fatal error in an unrelated plugin, or a server resource limit can silently drop a cron run with nothing logged anywhere.

None of this throws an error you'd notice. Scheduled posts just publish late. Backups just don't happen. You usually only find out when something downstream — a missed email, a stale cache, a post stuck at "Scheduled" — makes it obvious.

![Diagram comparing unreliable WordPress pseudo-cron triggered by visitor page loads with a reliable real server cron job calling wp-cron.php or WP-CLI on a fixed schedule](/img/blog/wordpress-scheduled-tasks-not-running.svg "Visitor-triggered WP-Cron vs. a real server cron job")

## Check whether your cron is actually running

Before fixing anything, confirm there's a problem. If you have shell access, WP-CLI gives you a direct answer:

```bash
# List every scheduled event, next run time, and recurrence
wp cron event list

# Just the hook names and next run time, as JSON
wp cron event list --fields=hook,next_run --format=json

# Checks whether WP-Cron spawning works at all on this install
wp cron test
```

If `wp cron event list` shows events with a `next_run` time that's already well in the past, WP-Cron isn't firing on schedule — it's waiting for a page load that hasn't come, or isn't completing.

No shell access? Skip ahead to WP Crontrol below — it shows you the same information from the WordPress admin.

## The fix: turn off the page-load trigger, set up a real one

The reliable fix is to stop WordPress from guessing when to run cron based on visitors, and instead trigger it on a fixed schedule from the server itself, the way `cron` was always meant to work.

**Step 1 — Tell WordPress to stop firing cron on page loads.** Add this line to `wp-config.php`, above the `/* That's all, stop editing! */` line:

```php
define( 'DISABLE_WP_CRON', true );
```

This only stops the automatic page-load check. It doesn't block anyone from requesting `wp-cron.php` directly, and on its own it leaves you with no cron running at all — you still need step 2.

**Step 2 — Add a real cron job on the server.** If your host gives you cPanel, look for **Cron Jobs** in the dashboard and add an entry to run every 5 minutes. If you have SSH access, edit the crontab directly:

```bash
crontab -e
```

Then add one of these lines. If WP-CLI is installed on the server (most managed WordPress hosts have it):

```bash
*/5 * * * * cd /path/to/wordpress && wp cron event run --due-now >/dev/null 2>&1
```

If it isn't, hit the file over HTTP instead with `curl` or `wget`:

```bash
*/5 * * * * curl -s https://yourdomain.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1
```

Every 5 minutes is a reasonable default for most sites; drop to every minute only if you have tasks that genuinely need near-exact timing.

**Step 3 (optional, server-level) — Block outside requests to wp-cron.php.** `DISABLE_WP_CRON` doesn't stop someone from loading `wp-cron.php` in a browser. If you're calling it via WP-CLI directly (which bypasses HTTP entirely) rather than over HTTP, you can close that door with an Nginx rule:

```nginx
location = /wp-cron.php {
    allow 127.0.0.1;
    deny all;
}
```

Skip this step if your server cron job hits `wp-cron.php` over HTTP rather than through WP-CLI — you'd be blocking your own cron job too.

## A plugin that makes this visible: WP Crontrol

If editing `wp-config.php` and crontab isn't something you want to do by hand, or you just want visibility into what's scheduled without SSH, [WP Crontrol](https://wordpress.org/plugins/wp-crontrol/) is a free plugin built exactly for this. After activating it:

- **Tools > Cron Events** lists every scheduled event, its next run time, and recurrence — the same information `wp cron event list` gives you, from the dashboard. You can edit, pause, delete, or run any event immediately.
- **Settings > Cron Schedules** lets you see and add custom intervals beyond WordPress's default hourly/twice-daily/daily options.
- It warns you directly in the admin when the cron system looks like it isn't actually running — the same drift you'd otherwise only notice indirectly.

It doesn't replace setting up a real server cron job — it's a dashboard for whichever cron mechanism (pseudo or real) is currently running — but it turns "is this actually working?" from a WP-CLI question into a one-click check for anyone managing the site.

## What this isn't a fix for

Moving to a real server cron job fixes *timing*. It doesn't fix a task that's failing for an unrelated reason — a backup plugin that can't reach its storage destination, or a scheduled post stuck because of a broken block will still fail on a perfect cron schedule. Use `wp cron event list` or WP Crontrol to confirm the event is actually firing at the right time first; if it is and the task still doesn't complete, the problem has moved from WP-Cron to whatever that specific task is trying to do.

## A simple checklist

- Confirm there's a real problem first — `wp cron event list` or WP Crontrol will show you events with a `next_run` time stuck in the past.
- Add `DISABLE_WP_CRON` to `wp-config.php` so WordPress stops guessing based on page loads.
- Set up a real cron job in cPanel or crontab, every 5 minutes, using WP-CLI or a direct `curl`/`wget` request to `wp-cron.php`.
- If you're triggering cron through WP-CLI only, block direct HTTP access to `wp-cron.php` at the server level.
- Once timing is fixed, re-check: a task that still doesn't run has a separate, unrelated problem worth chasing down on its own.
