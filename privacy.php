<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$page_title       = 'Privacy & Cookie Policy — ' . SITE_NAME;
$page_description = 'How SimpleWPlugins handles personal data and cookies.';

require __DIR__ . '/includes/header.php';
?>
<article class="post-single wrap">
    <h1>Privacy &amp; Cookie Policy</h1>

    <div class="post-body">
        <p><em>Last updated: <?php echo date('F j, Y'); ?></em></p>

        <h2>Data controller</h2>
        <p>The data controller for this website is Alberto Reineri, based in Italy. For any privacy-related question or to exercise your rights, contact <a href="mailto:<?php echo esc(SITE_EMAIL); ?>"><?php echo esc(SITE_EMAIL); ?></a>.</p>

        <h2>What data we process</h2>
        <p>This site is intentionally minimal. We don't run any contact form, account system, or database of visitor data. The only two ways your data reaches us are:</p>
        <ul>
            <li><strong>Email you send us.</strong> The <a href="/contact">Contact</a> page is a plain <code>mailto:</code> link — writing to us puts whatever you include (your email address, name if you sign it, message content) directly into our inbox. We use it only to reply to you, and keep it only as long as needed to handle the conversation.</li>
            <li><strong>Analytics cookies, only if you consent.</strong> See below.</li>
        </ul>

        <h2>Google Analytics</h2>
        <p>We use Google Analytics 4 (GA4) to understand, in aggregate, how visitors use this site — which pages get read, roughly how much traffic we get, and from where. GA4 can set cookies (such as <code>_ga</code> and <code>_ga_*</code>) and send data, including your IP address, to Google servers, including outside the European Economic Area.</p>
        <p><strong>This only happens if you click "Accept" on the cookie banner.</strong> Until you do, Analytics stays fully switched off — no cookies are set and no data is sent to Google. You can withdraw your consent at any time by clearing this site's data in your browser (which resets the banner) or by using your browser's tracking-protection settings.</p>
        <p>The legal basis for this processing is your consent (art. 6(1)(a) GDPR). Google acts as our data processor for this service; you can read Google's own privacy practices at <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer">policies.google.com/privacy</a>.</p>

        <h2>Cookies used</h2>
        <table style="width:100%;border-collapse:collapse;margin:16px 0;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid var(--border);">
                    <th style="padding:8px 0;">Cookie</th>
                    <th style="padding:8px 0;">Purpose</th>
                    <th style="padding:8px 0;">Duration</th>
                    <th style="padding:8px 0;">Set when</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:8px 0;"><code>_ga</code></td>
                    <td style="padding:8px 0;">Distinguishes unique visitors (Google Analytics)</td>
                    <td style="padding:8px 0;">13 months</td>
                    <td style="padding:8px 0;">Only after you accept</td>
                </tr>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:8px 0;"><code>_ga_*</code></td>
                    <td style="padding:8px 0;">Persists session state (Google Analytics)</td>
                    <td style="padding:8px 0;">13 months</td>
                    <td style="padding:8px 0;">Only after you accept</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;">Local storage: <code>sw_consent</code></td>
                    <td style="padding:8px 0;">Remembers your cookie choice so we don't ask again</td>
                    <td style="padding:8px 0;">Until you clear browser data</td>
                    <td style="padding:8px 0;">As soon as you accept or reject</td>
                </tr>
            </tbody>
        </table>

        <h2>Your rights</h2>
        <p>Under the GDPR, you have the right to access, correct, delete, or export your personal data, to object to or restrict its processing, and to withdraw consent at any time without affecting the lawfulness of processing before withdrawal. To exercise any of these rights, email <a href="mailto:<?php echo esc(SITE_EMAIL); ?>"><?php echo esc(SITE_EMAIL); ?></a>. You also have the right to lodge a complaint with your national data protection authority — in Italy, the <a href="https://www.garanteprivacy.it/" target="_blank" rel="noopener noreferrer">Garante per la protezione dei dati personali</a>.</p>

        <h2>Changes to this policy</h2>
        <p>If this policy changes, we'll update the date at the top of this page.</p>
    </div>
</article>
<?php
require __DIR__ . '/includes/footer.php';
