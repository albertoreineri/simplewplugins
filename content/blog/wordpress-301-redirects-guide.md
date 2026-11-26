---
title: How to Set Up Redirects in WordPress the Right Way
date: 2026-11-26
description: WordPress only redirects old URLs automatically in one narrow case. Here's what it actually covers, and the real tools to handle everything else without losing your SEO.
---

You renamed a page, merged two blog posts into one, or moved your site to a new domain, and now some of your old URLs return a 404. Maybe Google Search Console is flagging them, maybe you found out because someone clicked a link from an old email and landed on an error page. Either way, the fix is a redirect — but WordPress's actual behavior here is narrower than most people assume, and getting it wrong quietly costs you the search ranking and backlinks pointing at the old URL.

## What WordPress actually redirects on its own

WordPress core does have one built-in redirect, and it's easy to overestimate what it covers. When you change the slug of an already-published post, WordPress saves the old slug in a `_wp_old_slug` meta field and, the next time someone requests that old URL, a function called `wp_old_slug_redirect()` catches the resulting 404, finds the post by its old slug, and sends a 301 to the current URL instead.

That's genuinely useful, but it comes with real limits:

- It only fires for a request that would otherwise be a 404 — it doesn't touch URLs that still resolve to something.
- It skips hierarchical post types, which means ordinary **Pages** aren't covered, only non-hierarchical types like standard posts and most custom post types.
- It stops working the moment another post takes over that same old slug, since the lookup can then no longer tell which post should get the redirect.

What it does *not* do: redirect anything when you change your permalink structure in **Settings > Permalinks**, when you delete a page outright, when you move the whole site to a new domain, or when you restructure categories. In every one of those cases, the old URLs just start 404ing until you add a redirect yourself.

## When you need to add one manually

- Migrating to a new domain or moving from HTTP to a different URL structure
- Deleting or merging pages/posts that had backlinks or search traffic
- Renaming a Page (not covered by the core behavior above)
- Restructuring category or tag URLs
- Fixing specific 404s you found in Google Search Console's "Pages" report under "Not found (404)"

Here's where each kind of fix actually lives — server-level rules fire before WordPress even loads, while the core behavior and redirect plugins only kick in once WordPress is already handling the request:

![Diagram comparing two places a WordPress redirect can be handled: server-level rewrite rules in .htaccess or nginx config that run before WordPress loads, versus WordPress-level handling through the core old-slug redirect or a redirect plugin, both ending in a 301 response to the visitor](/img/blog/wordpress-301-redirects-guide.svg "Where a WordPress redirect gets handled: server config vs. inside WordPress")

## The real tools to set it up

**Redirection** (free) is the most widely used dedicated plugin for this. After installing and activating it, go to **Tools > Redirection**, click **Add New**, and fill in the source URL (the old path, e.g. `/old-page/`) and the target URL (the new one). It supports regex matching for bulk patterns, and its **404s** tab logs every URL that's actually hitting a dead end on your site — which is the fastest way to find redirects you're missing instead of guessing. It also pairs with the same author's **Search Regex** plugin for bulk find-and-replace across content.

```bash
wp plugin install redirection --activate
```

**Rank Math** (free) has a basic redirect manager built in if you're already using it for SEO. It's off by default — turn it on under **Rank Math > General Settings > Advanced** by enabling the **Redirections** module, then manage redirects from **Rank Math > Redirections**. The free tier covers manual 301/302/307 redirects and can auto-create a redirect when you change a post's slug; wildcard and bulk-import redirects are a paid-tier feature.

**Yoast SEO Premium** (paid) includes its own redirect manager under **Yoast SEO > Redirects**, and it's convenient if you're already paying for Yoast — but note the free version of Yoast doesn't include this feature at all, so don't go looking for it there.

**Server-level rules**, if you're comfortable editing config files directly, run before WordPress even loads, which makes them the fastest option and the only one that still works if WordPress itself is down. On Apache, add your rule to `.htaccess` *above* the `# BEGIN WordPress` block, since anything below it gets regenerated when WordPress flushes rewrite rules:

```apache
RewriteEngine On
RewriteRule ^old-page/?$ /new-page/ [R=301,L]
# BEGIN WordPress
...
```

On nginx, add the rule inside your site's `server` block and reload the config afterward:

```nginx
rewrite ^/old-page/?$ /new-page/ permanent;
```

```bash
sudo nginx -t && sudo systemctl reload nginx
```

## Avoid building a redirect chain

A chain happens when an old URL redirects to a second URL that itself redirects to a third. Each hop adds latency, and if a chain gets long enough, crawlers and some browsers will stop following it rather than keep chasing it. If you're restructuring URLs more than once, periodically check your redirects for ones that point to another redirect instead of the final destination, and update them to point straight there — Redirection's log makes this easy to spot, since repeated requests through an old entry show up before they reach the page you actually want visitors to land on.

## Quick checklist

- Don't rely on core's old-slug redirect for Pages or for anything beyond a single slug rename
- Add a real redirect for every URL you delete, merge, or move that had traffic or backlinks
- Check Search Console's 404 report periodically, not just right after a migration
- Point redirects straight at the final URL — never at another redirect
- Prefer server-level rules for high-traffic, permanent moves; use a plugin for anything you'll need to edit often
