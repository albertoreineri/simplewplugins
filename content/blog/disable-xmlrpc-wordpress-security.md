---
title: Why You Should Disable XML-RPC in WordPress (And How to Do It Safely)
date: 2026-10-22
description: WordPress still ships xmlrpc.php enabled by default, and most sites never use it. Here's what it's for, why bots target it, and the real way to shut it down.
---

If you've ever grepped your server access logs for suspicious traffic, you've probably seen it: hundreds, sometimes thousands, of POST requests to `/xmlrpc.php`, all day, from IPs you've never heard of. Unlike the brute-force attempts against `wp-login.php`, these often go unnoticed, because `xmlrpc.php` isn't something most site owners ever think about — it just sits there, enabled on every WordPress install since version 3.5, whether you use it or not.

## What XML-RPC actually does

XML-RPC is a remote procedure protocol that lets external applications talk to WordPress over HTTP without going through the normal dashboard. In practice, on a modern site it's used for a short list of things:

- Publishing posts from desktop or mobile apps that predate the REST API (the old WordPress mobile apps, some third-party blogging clients like MarsEdit)
- Pingbacks and trackbacks between WordPress sites
- Jetpack, which still relies on XML-RPC for parts of its connection to WordPress.com

If you don't use any of those, `xmlrpc.php` is just an attack surface with no upside. Even the WordPress REST API, introduced in 4.7, has replaced XML-RPC for most modern integrations — but the old endpoint stays enabled for backward compatibility.

## Why it gets targeted

Two specific abuses show up constantly in logs:

- **Brute-force amplification via `system.multicall`.** This single XML-RPC method lets a caller bundle many separate requests into one HTTP call. Attackers use it to try hundreds of username/password combinations against `wp-login.php` in a single POST to `xmlrpc.php`, which is both faster for them and harder to catch than a normal flood of individual login attempts.
- **Pingback abuse for DDoS amplification.** The `pingback.ping` method can be used to make your server send requests to arbitrary third-party URLs on an attacker's behalf, and at scale this has been used to direct traffic floods at other sites — your server ends up as an unwitting participant.

Neither of these requires a known vulnerability or an outdated plugin. They're abuses of functionality that's working exactly as designed — which is why disabling it, rather than "patching" it, is the right call for most sites.

## Check if you're actually being hit

Before changing anything, confirm it's worth doing. Run this from your terminal (adjust the domain) to see whether XML-RPC responds at all:

```bash
curl -i -X POST https://yoursite.com/xmlrpc.php \
  -d '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName><params></params></methodCall>'
```

A `200 OK` with an XML body listing methods means it's live and reachable. Then check how much traffic it's drawing:

```bash
grep "xmlrpc.php" /var/log/apache2/access.log | wc -l
```

(Swap the path for your actual access log — on nginx it's typically `/var/log/nginx/access.log`, on managed hosting you may only get this through your host's log viewer.) It's common to find this file taking more hits than `wp-login.php` itself.

## Before you disable it: check Jetpack and old apps

If you run Jetpack, or publish through an old blogging client or the legacy WordPress mobile app, a full server-level block will break that connection. Jetpack in particular authenticates some of its features through XML-RPC, so test on staging first if you're not sure. If you don't use Jetpack and you publish only through `/wp-admin` or the block editor, you're almost certainly safe to turn it off completely.

## How to actually disable it

### Option 1: a dedicated plugin (easiest, reversible)

If you'd rather not touch code, two free plugins do exactly this:

- **[Disable XML-RPC](https://wordpress.org/plugins/disable-xml-rpc/)** by Philip Erb — no settings screen, no bloat. Activate it and XML-RPC is off; deactivate it and it's back on.
- **[Disable XML-RPC-API](https://wordpress.org/plugins/disable-xml-rpc-api/)** — a more complete option if you want control: it can block `xmlrpc.php` outright, disable just pingbacks/trackbacks while leaving the rest alone, and set an IP allow-list for cases where you still need it from a specific service.

If you already run a security suite like **Wordfence**, check **Wordfence → Login Security → Settings** for a "Disable XML-RPC authentication" option before installing anything else — it may already cover this.

### Option 2: a code snippet (if you manage your own child theme or a site-specific plugin)

WordPress exposes a filter for this:

```php
add_filter( 'xmlrpc_enabled', '__return_false' );
```

Drop that in your theme's `functions.php` or a small site-specific plugin. One important caveat: this filter blocks the *authenticated* XML-RPC methods (the ones brute-forcers want), but it does not necessarily stop every unauthenticated call, like pingbacks, from being processed by WordPress's XML-RPC handler. If you want a hard stop, use the server-level block below as well.

### Option 3: block it at the web server (strongest, no PHP involved)

This stops the request before WordPress even loads, which is both more secure and lighter on your server during an attack.

**Apache (`.htaccess`, in your site root):**

```apache
<Files xmlrpc.php>
    Order Allow,Deny
    Deny from all
</Files>
```

**nginx (inside your `server { }` block, placed *above* the block that routes `.php` files to PHP-FPM):**

```nginx
location = /xmlrpc.php {
    deny all;
}
```

If you need to leave access open for one trusted service (say, a specific Jetpack or monitoring IP), add an `Allow from x.x.x.x` line before `Deny from all` on Apache, or an `allow x.x.x.x;` line before `deny all;` on nginx.

## Which option should you actually use

- Don't use Jetpack or any XML-RPC-based app → block at the server level (Option 3). It's the most complete and the cheapest on resources.
- Use Jetpack or a legacy publishing app, but still get attacked → use a plugin with an IP allow-list (Disable XML-RPC-API) so only the services you trust get through.
- Just want a quick, no-maintenance fix and don't use any of the above → the plain "Disable XML-RPC" plugin or the `xmlrpc_enabled` filter is enough.

![Diagram showing a request to xmlrpc.php intercepted earlier by a web server block versus later by a WordPress PHP filter](/img/blog/disable-xmlrpc-wordpress-security.svg "Where each XML-RPC blocking method intercepts the request")

The earlier you stop the request, the less of your server's resources an attack burns through — which is why, if you don't have a specific reason to keep XML-RPC reachable, a server-level block is the one worth doing first.
