# Argon 主题模板开发文档

## 目录

1. [模板基础结构](#模板基础结构)
2. [标准模板示例](#标准模板示例)
3. [关键函数说明](#关键函数说明)
4. [自定义模板开发](#自定义模板开发)
5. [常见问题](#常见问题)

---

## 模板基础结构

### 基本组成

一个标准的 Argon 主题模板文件由以下部分组成：

```php
<?php get_header(); ?>      // 加载页面头部

<?php get_sidebar(); ?>     // 加载侧边栏（可选）

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <!-- 主要内容区域 -->
    </main>
</div>

<?php get_footer(); ?>      // 加载页面底部
```

### 文件位置

- 模板文件位于：`template-parts/` 目录
- 页面模板可以放在主题根目录或 `template-parts/` 目录

---

## 标准模板示例

### 1. 标准页面模板 (page.php)

```php
<?php get_header(); ?>

<?php get_sidebar(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php
        while ( have_posts() ) :
            the_post();
            get_template_part( 'template-parts/content', 'page' );

            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
        endwhile;
        ?>
    </main>
</div>

<?php get_footer(); ?>
```

### 2. 文章列表模板 (index.php)

```php
<?php get_header(); ?>

<div class="page-information-card-container"></div>

<?php get_sidebar(); ?>

<?php
    $waterflow_type = \ArgonModern\Options::instance()->get('article_list_waterflow', '1');
    $main_class = "site-main article-list article-list-home";
    if ($waterflow_type != '1'){
        $main_class .= " waterflow";
    }
?>

<div id="primary" class="content-area">
    <main id="main" class="<?php echo $main_class; ?>">
        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <?php if (get_post_type() == 'shuoshuo'){
                    get_template_part( 'template-parts/content-shuoshuo-preview' );
                }else{
                    get_template_part( 'template-parts/content-preview', \ArgonModern\Options::instance()->get('article_list_layout', '1') );
                } ?>
            <?php endwhile; ?>
            
            <?php echo \ArgonModern\Template::get_formatted_paginate_links_for_all_platforms(); ?>
        <?php endif; ?>
    </main>
</div>

<?php get_footer(); ?>
```

### 3. 单篇文章模板 (single.php)

```php
<?php get_header(); ?>

<?php get_sidebar(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php
        while ( have_posts() ) :
            the_post();
            get_template_part( 'template-parts/content', get_post_type() );

            if ( \ArgonModern\Options::instance()->get( 'show_sharebtn' ) != 'false' ) :
                get_template_part( 'template-parts/share' );
            endif;

            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;

            \ArgonModern\Template::render_post_navigation();
            echo \ArgonModern\Template::get_related_posts();
        endwhile;
        ?>
    </main>
</div>

<?php get_footer(); ?>
```

### 4. 自定义页面模板 (page-steam-showcase.php)

```php
<?php
/*
Template Name: 个人展示页面
Template Post Type: page
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<?php get_sidebar(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="steam-showcase-container">
            <!-- 头部信息 -->
            <div class="showcase-header">
                <div class="profile-section">
                    <div class="profile-avatar">
                        <img src="<?php echo get_avatar_url(get_current_user_id()); ?>" alt="Profile Avatar">
                    </div>
                    <div class="profile-info">
                        <h1 class="profile-name"><?php echo get_the_author_meta('display_name', get_current_user_id()); ?></h1>
                        <p class="profile-bio"><?php echo get_the_author_meta('description', get_current_user_id()); ?></p>
                        <div class="profile-stats">
                            <div class="stat-item">
                                <span class="stat-value"><?php echo wp_count_posts('post')->publish; ?></span>
                                <span class="stat-label">文章</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 主要内容 -->
            <div class="showcase-content">
                <div class="showcase-main">
                    <?php 
                    // WordPress 循环
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

                    <!-- 最近活动 -->
                    <div class="showcase-section card bg-white shadow-sm border-0">
                        <h2 class="section-title">最近活动</h2>
                        <div class="activity-timeline">
                            <?php
                            $recent_args = array(
                                'post_type' => 'post',
                                'posts_per_page' => 5,
                                'orderby' => 'date',
                                'order' => 'DESC'
                            );
                            $recent_query = new WP_Query($recent_args);
                            
                            if ($recent_query->have_posts()) :
                                while ($recent_query->have_posts()) : $recent_query->the_post();
                            ?>
                            <div class="activity-item">
                                <div class="activity-content">
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
                    <!-- 侧边栏内容 -->
                </div>
            </div>

            <!-- 评论区 -->
            <?php
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
            ?>
        </div>
    </main>
</div>

<?php get_footer(); ?>

<script>
// 仅修复懒加载图片不显示的问题
document.addEventListener('DOMContentLoaded', function() {
    function fixLazyImages() {
        var lazyImages = document.querySelectorAll('img[data-original]');
        lazyImages.forEach(function(img) {
            if (img.dataset.original && img.src.startsWith('data:image/svg+xml')) {
                img.src = img.dataset.original;
                img.removeAttribute('data-original');
                img.classList.remove('lazyload');
            }
        });
    }
    
    fixLazyImages();
    
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                fixLazyImages();
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});
</script>

<style>
/* 自定义样式 */
#main {
    padding: 0 !important;
}

.steam-showcase-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.showcase-header {
    background: var(--color-foreground);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 10px;
    color: var(--color-text-deeper);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

/* 更多样式... */
</style>
```

---

## 关键函数说明

### WordPress 核心函数

| 函数 | 说明 | 示例 |
|------|------|------|
| `get_header()` | 加载头部模板 | `<?php get_header(); ?>` |
| `get_sidebar()` | 加载侧边栏模板 | `<?php get_sidebar(); ?>` |
| `get_footer()` | 加载底部模板 | `<?php get_footer(); ?>` |
| `the_post()` | 设置文章数据 | `the_post();` |
| `the_content()` | 输出文章内容 | `<?php the_content(); ?>` |
| `the_title()` | 输出文章标题 | `<?php the_title(); ?>` |
| `have_posts()` | 检查是否有文章 | `if ( have_posts() )` |
| `comments_template()` | 加载评论区 | `comments_template();` |

### 自定义查询

```php
// 查询最新文章
$recent_args = array(
    'post_type' => 'post',
    'posts_per_page' => 5,
    'orderby' => 'date',
    'order' => 'DESC'
);
$recent_query = new WP_Query($recent_args);

if ($recent_query->have_posts()) :
    while ($recent_query->have_posts()) : $recent_query->the_post();
    // 输出内容
    endwhile;
    wp_reset_postdata(); // 重要：重置文章数据
endif;
```

### 主题选项

```php
// 获取主题选项
\ArgonModern\Options::instance()->get('option_name', 'default_value');

// 示例
$waterflow_type = \ArgonModern\Options::instance()->get('article_list_waterflow', '1');
```

### 用户信息

```php
// 获取当前用户 ID
get_current_user_id();

// 获取用户头像
get_avatar_url(get_current_user_id());

// 获取用户显示名称
get_the_author_meta('display_name', get_current_user_id());

// 获取用户简介
get_the_author_meta('description', get_current_user_id());
```

### 统计信息

```php
// 统计文章数量
wp_count_posts('post')->publish;

// 统计评论数量
$current_user = wp_get_current_user();
$comment_count = get_comments(array(
    'author_email' => $current_user->user_email,
    'count' => true
));

// 统计说说数量
wp_count_posts('shuoshuo')->publish;
```

---

## 自定义模板开发

### 1. 创建页面模板

在模板文件开头添加模板名称注释：

```php
<?php
/*
Template Name: 我的自定义页面
Template Post Type: page
*/
```

### 2. 使用主题样式类

Argon 主题提供了一些常用的样式类：

```html
<!-- 卡片样式 -->
<div class="card bg-white shadow-sm border-0">
    <!-- 内容 -->
</div>

<!-- 徽章样式 -->
<span class="badge badge-secondary">标签</span>

<!-- 按钮样式 -->
<button class="btn btn-primary">按钮</button>
```

### 3. CSS 变量

主题提供了 CSS 变量用于样式定制：

```css
/* 颜色变量 */
var(--color-foreground)      /* 前景色（卡片背景） */
var(--color-background)      /* 背景色 */
var(--color-text-deeper)     /* 深色文字 */
var(--themecolor)            /* 主题色 */
var(--themecolor-gradient)   /* 主题色渐变 */

/* 暗色模式适配 */
html.darkmode .your-class {
    /* 暗色模式样式 */
}
```

### 4. JavaScript 初始化

**重要**：不要手动调用 `classInit()` 或 `waterflowInit()`，主题会自动处理。

只需在需要时添加自定义脚本：

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // 你的自定义代码
});

// Pjax 支持
document.addEventListener('pjax:complete', function() {
    // Pjax 加载后的代码
});
```

---

## 常见问题

### Q1: 侧边栏不显示

**原因**：缺少 `get_sidebar()` 调用

**解决**：
```php
<?php get_header(); ?>
<?php get_sidebar(); ?>  // 确保添加这行
```

### Q2: 页面内容为空

**原因**：WordPress 循环使用错误

**解决**：
```php
// 错误写法
the_post();
the_content();

// 正确写法
if ( have_posts() ) : 
    while ( have_posts() ) : 
        the_post();
        the_content();
    endwhile;
endif;
```

### Q3: 自定义查询后内容混乱

**原因**：没有重置文章数据

**解决**：
```php
$custom_query = new WP_Query($args);
if ($custom_query->have_posts()) :
    while ($custom_query->have_posts()) : $custom_query->the_post();
    // 内容
    endwhile;
    wp_reset_postdata(); // 重要！
endif;
```

### Q4: 样式不生效

**原因**：CSS 选择器优先级不够

**解决**：
```css
/* 使用 !important 提高优先级 */
#main {
    padding: 0 !important;
}

/* 或使用更具体的选择器 */
.steam-showcase-container .showcase-header {
    /* 样式 */
}
```

### Q5: 懒加载图片不显示

**解决**：添加图片修复脚本
```javascript
document.addEventListener('DOMContentLoaded', function() {
    function fixLazyImages() {
        var lazyImages = document.querySelectorAll('img[data-original]');
        lazyImages.forEach(function(img) {
            if (img.dataset.original && img.src.startsWith('data:image/svg+xml')) {
                img.src = img.dataset.original;
                img.removeAttribute('data-original');
                img.classList.remove('lazyload');
            }
        });
    }
    fixLazyImages();
});
```

---

## 最佳实践

1. **遵循标准模板结构** - 使用 `get_header()`、`get_sidebar()`、`get_footer()`
2. **正确使用 WordPress 循环** - 使用 `have_posts()`、`the_post()`、`the_content()`
3. **重置查询数据** - 自定义查询后调用 `wp_reset_postdata()`
4. **不要手动初始化主题** - 让主题自动处理 `classInit()` 等函数
5. **使用主题 CSS 变量** - 确保亮色/暗色模式自动适配
6. **添加 Pjax 支持** - 使用 `pjax:complete` 事件处理动态加载
7. **保持代码简洁** - 只添加必要的自定义代码

---

## 参考文件

- `page.php` - 标准页面模板
- `index.php` - 文章列表模板
- `single.php` - 单篇文章模板
- `sidebar.php` - 侧边栏模板
- `header.php` - 头部模板
- `footer.php` - 底部模板
- `template-parts/page-steam-showcase.php` - 自定义展示页面示例

---

**最后更新**: 2026-03-20
**主题版本**: Argon Modern 2.0.0
