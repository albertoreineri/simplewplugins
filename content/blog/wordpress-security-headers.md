---
title: How to Add Security Headers to a WordPress Site (And Why Most Don't Have Any)
date: 2026-11-12
description: WordPress sends almost no security headers out of the box. Here's what X-Frame-Options, HSTS and CSP actually do, and the real code and plugins to set them.
---

Open your browser's dev tools on almost any WordPress site, switch to the Network tab, and look at the response headers for the homepage. On most installs you'll find close to nothing: no `Strict-Transport-Security`, no `X-Content-Type-Options`, no `Content-Security-Policy`. WordPress itself barely sets any of these by default, and the average shared hosting setup doesn't add them either, so the gap just sits there until something — or someone — takes advantage of it.

## What security headers actually do

Security headers don't patch a vulnerability in your code. They tell the visitor's browser how to behave defensively around your site, closing off entire classes of attack before they get a chance to run:

- `X-Content-Type-Options: nosniff` stops the browser from guessing a file's type from its content instead of trusting the declared `Content-Type`, which is one of the tricks behind file-upload attacks that smuggle a script past a filter that only checks the extension.
- `X-Frame-Options: SAMEORIGIN` stops other sites from loading your pages inside an `<iframe>`, which is the basis of clickjacking attacks.
- `Referrer-Policy: strict-origin-when-cross-origin` limits how much of your page's URL leaks to the sites your outbound links point to.
- `Permissions-Policy` lets you switch off browser features — camera, microphone, geolocation — that your site has no business asking a visitor's browser for.
- `Strict-Transport-Security` (HSTS) tells the browser to only ever talk to your domain over HTTPS, closing the window a man-in-the-middle attacker gets during that first HTTP request before your redirect to HTTPS fires.
- `Content-Security-Policy` (CSP) is the heaviest one: it whitelists where scripts, styles and other resources are allowed to load from, and it's one of the strongest defenses against cross-site scripting — including the kind a compromised plugin, a bad ad script, or an unsanitized file upload can introduce.

## What WordPress already sends (and what it doesn't)

WordPress core isn't completely silent. Since version 3.1.3 it has hooked `send_frame_options_header()` into `login_init` and `admin_init`, which sends `X-Frame-Options: SAMEORIGIN` on the login screen and throughout `/wp-admin/` — but only there. The public pages your visitors actually read get nothing from core: no HSTS, no CSP, no `X-Content-Type-Options`, no `Referrer-Policy`, no `Permissions-Policy`. All of that is left to you, your host, or a plugin.

## Check what you're starting with

Before changing anything, see what your site currently sends. From a terminal:

```
curl -sI https://yoursite.com
```

Read through the response headers, or run the same check visually at [securityheaders.com](https://securityheaders.com) — enter your domain, hit scan, and it grades the response from A+ to F with a breakdown of what's present, missing, or misconfigured. Most WordPress sites that haven't touched this score a D or F on the first try.

## Three ways to actually set them

### 1. Through a plugin

If you'd rather not edit server config or PHP files directly, two real options on wordpress.org:

- **HTTP Headers** (free) — a settings-screen UI for adding, editing or removing arbitrary response headers, including HSTS, `Referrer-Policy`, `X-Frame-Options` and CSP, with no file editing involved.
- **Really Simple Security** (the plugin formerly called Really Simple SSL) — its Pro tier has a one-click "recommended security headers" set, and its CSP builder ships with a report-only "learning mode" so you can see what would get blocked before you switch it to enforce.

Either way, once you've settled on a final header set you can lift it out of the plugin and into `.htaccess` or your server config, then deactivate the plugin — one less moving part sitting in front of every request.

### 2. Through .htaccess (Apache)

If you're on Apache, which covers most shared hosting, add this near the top of your site's `.htaccess`, above the WordPress rewrite block:

```apache
<IfModule mod_headers.c>
  Header always set X-Content-Type-Options "nosniff"
  Header always set X-Frame-Options "SAMEORIGIN"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
  Header always set Permissions-Policy "camera=(), microphone=(), geolocation=(self)"
  Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>
```

This needs `mod_headers`, which is enabled on nearly every hosting account. Use `Header always set`, not `Header set` — `always` applies the header even on error responses (4xx/5xx), which plain `set` doesn't guarantee.

### 3. Through functions.php or a small plugin (any server)

If you're on Nginx, don't have `.htaccess` access, or just don't want to touch server config, hook into WordPress's `send_headers` action instead:

```php
add_action( 'send_headers', function () {
    header( 'X-Content-Type-Options: nosniff' );
    header( 'X-Frame-Options: SAMEORIGIN' );
    header( 'Referrer-Policy: strict-origin-when-cross-origin' );
    header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(self)' );
} );
```

Put this in a small site-specific plugin rather than your theme's `functions.php` — a theme switch or update can wipe that file, and you'd lose every header without noticing. Also check whether a caching plugin or your host serves pages from a static cache generated *before* this code runs; a full-page HTML cache can skip `send_headers` entirely, in which case the header has to be set at the server or CDN layer instead.

## Handle HSTS and CSP carefully

Two of these headers can break your site if you get them wrong, so treat them differently from the rest:

- **HSTS** tells the browser to refuse plain HTTP for the entire `max-age` window — if your SSL certificate ever lapses, or you need to serve the site over HTTP temporarily, visitors who already received the header can't reach you until it expires. Confirm certificate auto-renewal is actually working before you add `includeSubDomains`, and don't add `preload` until you're sure every subdomain is HTTPS-only for good.
- **CSP** has to list every legitimate source your site loads from — Google Fonts, a CDN, your analytics snippet, an embedded YouTube video — or it blocks those along with anything malicious. Start in report-only mode so nothing actually breaks yet:

```apache
Header always set Content-Security-Policy-Report-Only "default-src 'self'; img-src 'self' data:; script-src 'self'"
```

Watch your browser's console for violation reports over a week or two, add the legitimate sources you find, and only switch `Content-Security-Policy-Report-Only` to `Content-Security-Policy` once nothing you actually use is still getting flagged.

![Diagram comparing a WordPress response with no security headers, left open to clickjacking and MIME-sniffing, against the same response with headers set, where the browser enforces HTTPS-only, same-origin framing and an allow-listed content policy](/img/blog/wordpress-security-headers.svg "WordPress response with no security headers versus with security headers enforced")

## Verify it worked

After making changes, re-run `curl -sI https://yoursite.com` or re-scan on securityheaders.com. Also load a few pages that use third-party embeds — a map, a payment form, a video — to make sure CSP didn't quietly break something that worked yesterday. A strict CSP is the one header on this list that's just as likely to break a feature as it is to stop an attack, so test before you consider the job done.
