<?php
require __DIR__ . '/../includes/config.php';
require __DIR__ . '/../includes/functions.php';

$page_title       = 'Blog — ' . SITE_NAME;
$page_description = 'Guides and tips from the SimpleWPlugins team.';

require __DIR__ . '/../includes/header.php';

$posts = sw_get_posts();
?>
<section class="blog-list wrap">
    <h1>Blog</h1>

    <?php if (empty($posts)): ?>
        <p>No posts yet — check back soon.</p>
    <?php endif; ?>

    <?php foreach ($posts as $post): ?>
        <article class="post-preview">
            <div class="date"><?php echo esc(date('F j, Y', strtotime($post['date'] ?? 'now'))); ?></div>
            <h2><a href="/blog/<?php echo esc($post['slug']); ?>"><?php echo esc($post['title'] ?? ''); ?></a></h2>
            <p><?php echo esc($post['excerpt'] ?? ''); ?></p>
        </article>
    <?php endforeach; ?>
</section>
<?php
require __DIR__ . '/../includes/footer.php';
