---
title: Why WordPress Isn't Sending Email (And How to Actually Fix It)
date: 2026-10-01
description: WordPress emails silently failing or landing in spam isn't random — it's how wp_mail() is built. Here's why, and the real fix.
plugin: simple-smtp-lite
---

Password resets that never arrive. Contact form submissions nobody sees. WooCommerce order confirmations missing from the customer's inbox. If you run a WordPress site, you've probably hit at least one of these, and the frustrating part is that WordPress usually doesn't tell you anything went wrong — the form just says "message sent," and that's it.

## wp_mail() was never built to be reliable

Every email WordPress sends — core, plugins, themes — goes through one function: `wp_mail()`. Under the hood, by default, that function hands the message off to PHP's built-in `mail()` function, which in turn passes it to whatever local mail agent your server has (Sendmail or Postfix, usually). That handoff has no authentication attached to it at all: no proof to the receiving mail server that the message actually came from your domain.

That's the core problem. Mailbox providers like Gmail and Outlook use authentication records (SPF, DKIM, DMARC) to decide whether a message is trustworthy. An email sent via plain PHP `mail()` typically has none of that, so it either gets dropped silently, bounced, or dumped straight into spam. And because `wp_mail()` returns `true` as soon as PHP accepts the message for local delivery, WordPress has no idea anything failed downstream — "sent" just means "handed off," not "delivered."

## The usual culprits

A few specific situations make this worse:

- **PHP's `mail()` is disabled outright.** Many hosts turn it off by default because it's a common spam vector — if that's the case, `wp_mail()` fails immediately with nothing to show for it.
- **You're on localhost or a dev environment.** There's no mail server to hand the message to at all, so nothing will ever send until you add SMTP.
- **The "From" address isn't authorized for your domain.** A lot of setups default to something like `wordpress@yourdomain.com` that was never configured to send mail — receiving servers reject it outright.
- **No SPF/DKIM records for your sending domain.** Even when the message gets out, without these DNS records it has no way to prove authenticity, so it's an easy spam-filter target.

Here's the difference those two paths make:

![Diagram comparing WordPress wp_mail() sent through unauthenticated PHP mail(), which lands in spam or gets dropped, versus sent through an authenticated SMTP or email API plugin, which reaches the inbox](/img/blog/wordpress-not-sending-email.svg "wp_mail() delivery: PHP mail() versus authenticated SMTP")

## Check whether you actually have a problem

Before changing anything, confirm it. If you have shell access, the fastest test is WP-CLI:

```bash
wp eval 'var_dump( wp_mail( "you@example.com", "WordPress test", "If this arrives, wp_mail works." ) );'
```

If that returns `true` but the email never shows up (check spam too), you've confirmed the handoff to PHP is succeeding but delivery is failing — exactly the authentication problem above. If it returns `false` or throws a warning, PHP mail is likely disabled on your server entirely.

No shell access? Install any of the plugins below — all of them include a one-click "send test email" tool in their settings once you've connected a provider, which is a cleaner way to confirm things are working end to end.

## The fix: stop using PHP mail, route through real SMTP or an email API

The actual fix isn't a WordPress setting — there isn't one. It's rerouting `wp_mail()` away from PHP's `mail()` and through an authenticated SMTP connection or a transactional email API instead. A few real plugins that do this well:

- **[WP Mail SMTP](https://wordpress.org/plugins/wp-mail-smtp/)** (free, paid Pro tier available) — the most widely used option, with native integrations for Gmail/Google Workspace, Brevo, SendGrid, Mailgun, SMTP.com, Postmark, SparkPost, and plain SMTP. Install it, go to **WP Mail SMTP > Settings > General**, pick a mailer, fill in that provider's credentials, then use the **Email Test** tab to send a real test message before trusting it in production.
- **[FluentSMTP](https://wordpress.org/plugins/fluent-smtp/)** — also free, with no paid tier at all, and support for Amazon SES, Gmail, SendGrid, Mailgun, Postmark, Brevo and others, plus multi-connection routing (different providers for different types of mail) and a built-in sending log.
- **[Post SMTP](https://wordpress.org/plugins/post-smtp/)** — free, with a particular focus on deliverability logging, and support for Gmail, Office 365/Outlook, SendGrid, Mailgun, and Mailtrap for testing.

If you'd rather install one from the command line instead of the plugin search screen:

```bash
wp plugin install wp-mail-smtp --activate
```

Any of these turn `wp_mail()` from "hope it arrives" into an authenticated send through a provider that actually tracks delivery — most of them also show you a log of every email WordPress has tried to send, with delivery status, which is something core WordPress never gives you at all.

## Picking a provider

For most small-to-medium sites, a free tier from a transactional email service is enough: Brevo gives 300 emails/day free, SendGrid offers a free tier as well, and both plug directly into the plugins above with just an API key — no SMTP ports to fight with on restrictive hosts. If your site sends in volume (WooCommerce stores with a lot of daily orders, membership sites), a paid plan on one of those services, or Amazon SES, is worth it specifically for the deliverability monitoring and higher sending limits.

## Don't skip SPF and DKIM

Switching to SMTP/API delivery fixes authentication for that specific provider, but it's still worth adding SPF and DKIM DNS records for your domain if your host or email provider's setup guide asks for them — most of the plugins above show you exactly what records to add during setup. These records tell receiving mail servers "this provider is allowed to send mail on behalf of my domain," which is the single biggest factor in whether your messages land in the inbox instead of spam.

## What we're building

Running an SMTP plugin fixes the problem, but most of them ask you to pick through a dozen provider integrations and settings screens to get there. We're working on **Simple SMTP Lite**, aimed at the common case: one screen, your SMTP credentials (or a provider's API key), encrypted at rest, no bloat. It's not available on WordPress.org yet — you can see where it fits among our other plugins on the [homepage](/#simple-smtp-lite) — but if your immediate problem is emails not arriving, any of the three plugins above will fix it today.

## A simple checklist

- Confirm the problem first — test with WP-CLI's `wp eval` or a plugin's built-in test-email tool.
- Never rely on PHP's default `mail()` for anything that matters (password resets, order confirmations, form notifications).
- Connect a real SMTP or API provider through WP Mail SMTP, FluentSMTP, or Post SMTP.
- Add the SPF/DKIM records your provider asks for — authentication is what actually keeps you out of spam.
- Check your plugin's email log occasionally, not just when someone complains an email never arrived.
