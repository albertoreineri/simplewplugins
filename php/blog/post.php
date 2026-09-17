<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$post = sw_get_post($slug);

if (!$post) {
    http_response_code(404);
    $page_title = 'Post not found — ' . SITE_NAME;
    require __DIR__ . '/../includes/header.php';
    echo '<section class="post-single wrap"><h1>Post not found</h1><p><a href="/blog/">Back to the blog</a></p></section>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$related_plugin = !empty($post['plugin']) ? sw_find_plugin($post['plugin']) : null;

$page_title       = $post['title'] . ' — ' . SITE_NAME;
$page_description = $post['excerpt'] ?? SITE_TAGLINE;

require __DIR__ . '/../includes/header.php';
?>
<article class="post-single wrap">
    <a class="back-link" href="/blog/">← Back to the blog</a>
    <div class="date"><?php echo esc(date('F j, Y', strtotime($post['date'] ?? 'now'))); ?></div>
    <h1><?php echo esc($post['title']); ?></h1>

    <div class="post-body">
        <?php echo $post['html']; // Rendered from our own trusted markdown files, not user input. ?>
    </div>

    <?php if ($related_plugin): ?>
        <div class="plugin-callout">
            <p style="margin:0 0 6px;color:#6b7280;font-size:13px;text-transform:uppercase;letter-spacing:.5px;">Mentioned in this post</p>
            <a href="<?php echo $related_plugin['wporg'] ? esc('https://wordpress.org/plugins/' . $related_plugin['wporg'] . '/') : esc('/#' . $related_plugin['slug']); ?>">
                <?php echo esc($related_plugin['name']); ?> →
            </a>
        </div>
    <?php endif; ?>
</article>
<?php
require __DIR__ . '/../includes/footer.php';
