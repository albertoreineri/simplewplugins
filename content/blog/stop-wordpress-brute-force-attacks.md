---
title: How to Stop Brute-Force Login Attacks on WordPress
date: 2026-09-24
description: WordPress won't lock out a bot that hammers your login page all night. Here's how to actually rate-limit, hide, and lock down wp-login.php.
---

If you've ever checked your server logs and found thousands of POST requests to `wp-login.php` from IP addresses you've never heard of, you've already met this problem. It's not a targeted hack — it's automated. Bots work through lists of common usernames and leaked passwords against every WordPress site they can find, all day, every day, whether your site is popular or not.

## Why the login page is a constant target

`wp-login.php` sits at a predictable URL on every default WordPress install. That predictability is exactly what makes it attractive: an attacker doesn't need to know anything about your specific site to try logging into it. A script can hit thousands of WordPress sites with the same list of usernames (`admin`, `administrator`, your domain name) and passwords, and it costs the attacker almost nothing to keep trying.

If even one of those attempts succeeds, the attacker doesn't need to exploit a vulnerability — they log in exactly like you would, with a real username and password, and from there they can install anything they want.

## WordPress has no built-in rate limiting

This is the part that surprises people: WordPress core does not lock an account, throttle, or even log failed login attempts by default. You can fail a login a thousand times in a row, from the same IP, in under a minute, and WordPress will happily keep evaluating each one. There's no setting to turn on — the feature simply doesn't exist in core, which is why every serious guide to WordPress security recommends adding it yourself.

## The defenses that actually matter

Three things reliably cut down brute-force attempts, and they stack:

- **Rate-limit and lock out repeated failures** — after a handful of wrong passwords from the same IP or username, block further attempts for a while.
- **Make the login URL non-obvious** — if `/wp-login.php` and `/wp-admin` don't resolve to your login form anymore, generic bots scanning for that path never even reach it.
- **Turn off XML-RPC if you don't use it** — `xmlrpc.php` exposes a `system.multicall` method that lets an attacker bundle hundreds of username/password guesses into a single HTTP request, which is far more efficient for them than hitting the login form one attempt at a time.

Here's roughly how those layers fit together, from a bot's first request to your dashboard:

![Diagram showing a bot's login attempt being blocked in sequence by a hidden login URL, then rate limiting with account lockout, then two-factor authentication, before reaching the WordPress dashboard](/img/blog/stop-wordpress-brute-force-attacks.svg "Layered defenses against WordPress brute-force login attempts")

## How to actually set this up

**Rate-limit failed logins with a plugin.** [Limit Login Attempts Reloaded](https://wordpress.org/plugins/limit-login-attempts-reloaded/) is free and does exactly what the name says: after a set number of failed attempts (4 by default) it locks out that IP for a period of time, doubling the lockout on repeat offenders. Install it, go to **Settings > Limit Login Attempts**, and the defaults are already reasonable — you mainly want to confirm the app is picking up the real visitor IP correctly if your site sits behind Cloudflare or another proxy (there's a setting for that on the same screen). [Wordfence](https://www.wordfence.com/) is a heavier, paid-tier-capable alternative that bundles the same brute-force rate limiting with a firewall and malware scanning, worth it if you want one plugin instead of several.

If you'd rather do it from the command line, you can install and activate either plugin with WP-CLI without touching the dashboard:

```bash
wp plugin install limit-login-attempts-reloaded --activate
```

**Hide the login URL.** [WPS Hide Login](https://wordpress.org/plugins/wps-hide-login/) is a small, free plugin that makes `/wp-login.php` and `/wp-admin` return a plain 404 to anyone who isn't logged in, and moves the real login form to a URL slug you choose. After activating it, go to **Settings > WPS Hide Login** and set your custom login slug (e.g. `/my-team-login` instead of `/wp-login.php`). Bookmark the new URL before you log out — if you forget it, you can still recover access via FTP by renaming the plugin's folder in `wp-content/plugins/` to deactivate it.

**Disable XML-RPC if you don't need it.** Most sites don't use XML-RPC at all — it exists mainly for the old WordPress mobile app and some third-party publishing tools like the Jetpack service. If you're not using any of those, you can turn it off with a small snippet. Add this via a code snippets plugin like [WPCode](https://wordpress.org/plugins/insert-headers-and-footers/) (safer than editing `functions.php` directly, since a typo there can take the whole site down) or paste it into your theme's `functions.php` if you're comfortable with FTP as a fallback:

```php
add_filter( 'xmlrpc_enabled', '__return_false' );
```

If your site runs on Apache, you can block requests to the file entirely at the server level instead, which is even more effective since it stops the request before WordPress even loads. Add this to your `.htaccess`:

```apache
<Files xmlrpc.php>
    Require all denied
</Files>
```

Only do this if you don't rely on XML-RPC — check with your host or theme/plugin vendors first if you use Jetpack or a mobile publishing app, since those depend on it.

## Add two-factor authentication if you can

Rate limiting and a hidden login URL stop the automated, high-volume attacks. They don't help if an attacker gets your actual password some other way — a data breach, a phishing email, a password reused from another site. Two-factor authentication closes that gap: even with the right password, logging in also requires a one-time code from an app on your phone. The free [Two-Factor](https://wordpress.org/plugins/two-factor/) plugin, maintained by WordPress core contributors, adds this with no paid tier required, and both Wordfence and Limit Login Attempts Reloaded also offer it as a built-in option.

## A simple checklist

- Install a login rate-limiting plugin so failed attempts actually get locked out, not just repeated forever.
- Move your login form off the default `/wp-login.php` URL.
- Disable XML-RPC if nothing on your site depends on it.
- Turn on two-factor authentication, at least for admin accounts.
- Never use `admin` as a username — if an old install still does, create a new administrator account with a different username and delete the old one.

None of these steps takes more than a few minutes, and together they take your login page from "wide open to any script that finds it" to a genuinely small target.
