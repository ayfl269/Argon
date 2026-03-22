<?php
/**
 * Template Name: 个人展示页面
 *
 * @package ArgonModern
 */

if (!defined('ABSPATH')) {
    exit;
}

// 获取展示的用户 ID
$showcase_user_id = get_current_user_id();
$current_page_id = get_queried_object_id();
$current_page = get_post($current_page_id);
$showcase_description = '';
if ($current_page) {
    // 匹配 [argon_showcase_user]
    if (preg_match('/\[argon_showcase_user\s+([^\]]+)\]/i', $current_page->post_content, $matches)) {
        $attr_str = $matches[1];
        if (preg_match('/id=["\']?(\d+)["\']?/i', $attr_str, $id_match)) {
            $showcase_user_id = intval($id_match[1]);
        } elseif (preg_match('/(?:user|username)=["\']?([^"\']+)["\']?/i', $attr_str, $user_match)) {
            $user = get_user_by('login', $user_match[1]);
            if (!$user) {
                $user = get_user_by('slug', $user_match[1]);
            }
            if ($user) {
                $showcase_user_id = $user->ID;
            }
        }
    }
    // 匹配 [argon_showcase_description]...[/argon_showcase_description]
    if (preg_match('/\[argon_showcase_description\](.*?)\[\/argon_showcase_description\]/is', $current_page->post_content, $desc_matches)) {
        $showcase_description = trim($desc_matches[1]);
    }
}

// 确保用户存在，如果不存在则回退到当前登录用户或页面作者
$showcase_user = get_userdata($showcase_user_id);
if (!$showcase_user) {
    $showcase_user_id = get_current_user_id() ?: ($current_page ? $current_page->post_author : 0);
    $showcase_user = get_userdata($showcase_user_id);
}

get_header();
?>

<?php get_sidebar(); ?>

<div id="primary" class="content-area">
<main id="main" class="site-main">
<div class="steam-showcase-container">
    <div class="showcase-header">
        <div class="profile-section">
            <div class="profile-avatar">
                <img src="<?php echo get_avatar_url($showcase_user_id); ?>" alt="Avatar">
            </div>
            <div class="profile-info">
                <div class="profile-header-top">
                    <h1 class="profile-name"><?php echo get_the_author_meta('display_name', $showcase_user_id); ?></h1>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-value"><?php echo count_user_posts($showcase_user_id, 'post'); ?></span>
                            <span class="stat-label">文章</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php 
                                $comment_count = get_comments(array(
                                    'user_id' => $showcase_user_id,
                                    'count' => true
                                ));
                                echo $comment_count;
                            ?></span>
                            <span class="stat-label">评论</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?php echo count_user_posts($showcase_user_id, 'shuoshuo'); ?></span>
                            <span class="stat-label">说说</span>
                        </div>
                    </div>
                </div>
                <p class="profile-bio"><?php echo get_the_author_meta('description', $showcase_user_id); ?></p>
                <?php if ($showcase_description) : ?>
                <div class="profile-extra-description">
                    <?php echo apply_filters('the_content', $showcase_description); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="showcase-content">
        <div class="showcase-main">
            <?php 
            // 开始 WordPress 循环
            if ( have_posts() ) : 
                while ( have_posts() ) : 
                    the_post();
            ?>
            <div class="showcase-section card bg-white shadow-sm border-0">
                <div class="showcase-media-content">
                    <?php the_content(); ?>
                </div>
            </div>
            <?php 
                endwhile;
            endif;
            ?>

            <div class="showcase-section card bg-white shadow-sm border-0">
                <h2 class="section-title">
                    最近活动
                </h2>
                <div class="activity-timeline">
                    <?php
                    $recent_args = array(
                        'post_type' => 'post',
                        'author' => $showcase_user_id,
                        'posts_per_page' => 5,
                        'orderby' => 'date',
                        'order' => 'DESC'
                    );
                    $recent_query = new WP_Query($recent_args);
                    
                    if ($recent_query->have_posts()) :
                        while ($recent_query->have_posts()) : $recent_query->the_post();
                    ?>
                    <div class="activity-item">
                        <div class="activity-marker"></div>
                        <div class="activity-content">
                            <div class="activity-header">
                                <span class="activity-type">发布了文章</span>
                                <span class="activity-time"><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . '前'; ?></span>
                            </div>
                            <h4 class="activity-title"><?php the_title(); ?></h4>
                            <p class="activity-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>
                        </div>
                    </div>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </div>
            </div>
        </div>

        <div class="showcase-sidebar">
        </div>
    </div><!-- .showcase-content -->
</div><!-- .steam-showcase-container -->

    <?php
    if ( comments_open() || get_comments_number() ) :
        comments_template();
    endif;
    ?>

<script>
(function() {
    function fixLazyImages() {
        var lazyImages = document.querySelectorAll('img[data-original]');
        lazyImages.forEach(function(img) {
            if (img.dataset.original && (img.src.startsWith('data:image/svg+xml') || !img.src || img.src === window.location.href)) {
                img.src = img.dataset.original;
                img.removeAttribute('data-original');
                img.classList.remove('lazyload');
            }
        });
    }

    function init() {
        fixLazyImages();
        
        // 监听 DOM 变化，处理动态加载的图片
        if (!window.steamShowcaseObserver) {
            window.steamShowcaseObserver = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        fixLazyImages();
                    }
                });
            });
            window.steamShowcaseObserver.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }

    // 立即执行
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // 适配 PJAX
    $(document).off('pjax:complete.steamshowcase').on('pjax:complete.steamshowcase', function() {
        fixLazyImages();
    });
})();
</script>

<style>
#main {
    padding: 0 !important;
}

.steam-showcase-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* 评论区与最近活动的间隔优化 */
.steam-showcase-container > .showcase-section:last-child {
    margin-bottom: 30px;
}

#comments {
    margin-top: 30px;
    margin-bottom: 20px;
}

.showcase-header {
    background: var(--color-foreground);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 10px;
    color: var(--color-text-deeper);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

html.darkmode .showcase-header {
    background: var(--color-foreground);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.profile-section {
    display: flex;
    align-items: center;
    gap: 30px;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid rgba(255, 255, 255, 0.3);
    overflow: hidden;
    flex-shrink: 0;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-info {
    flex: 1;
}

.profile-name {
    font-size: 1.8rem;
    margin: 0;
    font-weight: 350;
}

.profile-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    width: 100%;
}

.profile-stats {
    display: flex;
    gap: 20px;
}

.stat-item {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 1.2rem;
    font-weight: 700;
}

.stat-label {
    font-size: 0.8rem;
    opacity: 0.8;
}

.profile-bio {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0 0 10px 0;
}

.profile-extra-description {
    margin-top: 15px;
    font-size: 1rem;
    opacity: 0.95;
    line-height: 1.6;
}

.profile-extra-description p {
    margin-bottom: 8px;
}

.profile-extra-description p:last-child {
    margin-bottom: 0;
}

.showcase-content {
    display: block;
    margin-top: 30px;
}

.showcase-main {
    width: 100%;
}

.showcase-sidebar {
    display: none;
}

.showcase-section {
    border-radius: 12px;
    padding: 20px;
}

html.darkmode .showcase-section {
    border-radius: 12px;
}

.showcase-main > .showcase-section:first-child {
    padding: 0;
    overflow: hidden;
}

.section-title {
    font-size: 1.8rem;
    margin: 0 0 25px 0;
    color: var(--color-text, #333);
    display: flex;
    align-items: center;
    gap: 10px;
}

html.darkmode .section-title {
    color: var(--color-text, #e2e8f0);
}

.showcase-media-content {
    min-height: 200px;
    padding: 20px;
    overflow: hidden;
}

.showcase-main > .showcase-section:first-child .showcase-media-content {
    padding: 0;
}

.showcase-main > .showcase-section:not(:last-child) {
    margin-bottom: 30px;
}

.showcase-media-content .gallery,
.showcase-media-content .wp-block-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.showcase-media-content img {
    width: 100% !important;
    height: auto !important;
    display: block !important;
    border-radius: 8px;
    object-fit: cover;
}

.showcase-main > .showcase-section:first-child .showcase-media-content img {
    border-radius: 0;
}

.showcase-media-content p, 
.showcase-media-content figure,
.showcase-media-content .aligncenter,
.showcase-media-content .alignnone,
.showcase-media-content .alignleft,
.showcase-media-content .alignright {
    margin: 0;
    width: 100%;
    max-width: 100%;
}

.activity-timeline {
    position: relative;
    padding-left: 30px;
}

.activity-timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
}

.activity-item {
    position: relative;
    margin-bottom: 25px;
    padding-bottom: 25px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

html.darkmode .activity-item {
    border-bottom-color: rgba(255, 255, 255, 0.1);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-marker {
    position: absolute;
    left: -24px;
    top: 0;
    width: 12px;
    height: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

.activity-content {
    background: var(--color-background, #f5f5f5);
    padding: 20px;
    border-radius: 8px;
}

html.darkmode .activity-content {
    background: var(--color-background, #2d3748);
}

.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.activity-type {
    font-size: 0.85rem;
    color: var(--themecolor, #5e72e4);
    font-weight: 600;
}

.activity-time {
    font-size: 0.85rem;
    color: var(--color-text-muted, #666);
}

html.darkmode .activity-time {
    color: var(--color-text-muted, #a0aec0);
}

.activity-title {
    margin: 0 0 10px 0;
    font-size: 1.1rem;
    color: var(--color-text, #333);
}

html.darkmode .activity-title {
    color: var(--color-text, #e2e8f0);
}

.activity-excerpt {
    margin: 0;
    font-size: 0.9rem;
    color: var(--color-text-muted, #666);
    line-height: 1.6;
}

html.darkmode .activity-excerpt {
    color: var(--color-text-muted, #a0aec0);
}

@media (max-width: 768px) {
    .profile-section {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-header-top {
        flex-direction: column;
        gap: 10px;
        align-items: center;
    }
    
    .profile-stats {
        justify-content: center;
    }
    
    .showcase-content {
        flex-direction: column;
    }
    
    .showcase-sidebar {
        width: 100%;
    }
}
</style>

<?php get_footer(); ?>
