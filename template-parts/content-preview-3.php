<?php
/**
 * Template part for displaying posts in preview style 3
 *
 * @package ArgonModern
 */

$options = \ArgonModern\Options::instance();
$template = \ArgonModern\Template::instance();
?>
<article class="post card bg-white shadow-sm border-0 <?php if ($options->get('enable_into_article_animation', 'true') == 'true'){echo 'post-preview';} ?> post-preview-layout-3" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php
			if ($template::has_post_thumbnail()){
				echo "<header class='post-header post-header-with-thumbnail'>";
				$thumbnail_id = get_post_thumbnail_id(get_the_ID());
				$thumbnail_data = wp_get_attachment_image_src($thumbnail_id, "full");
				$thumbnail_url = $thumbnail_data ? $thumbnail_data[0] : $template::get_post_thumbnail();
				$width = $thumbnail_data ? $thumbnail_data[1] : '';
				$height = $thumbnail_data ? $thumbnail_data[2] : '';
				$attr = ($width && $height) ? " width='{$width}' height='{$height}' style='aspect-ratio: {$width} / {$height}; width: 100%; height: auto;'" : "";

				if ($options->get('enable_lazyload') != 'false'){
					echo "<img class='post-thumbnail' src='" . $thumbnail_url . "' loading='lazy' alt='thumbnail'{$attr}></img>";
				}else{
					echo "<img class='post-thumbnail' src='" . $thumbnail_url . "' alt='thumbnail'{$attr}></img>";
				}
				echo "</header>";
			}
		?>
		<header class="post-header">
			<a class="post-title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			<div class="post-meta">
				<?php \ArgonModern\Template::render_article_meta(); ?>
			</div>
		</header>

	<?php
		$trim_words_count = $options->get('trim_words_count', 175);
	?>
	<?php if ($trim_words_count > 0){ ?>
		<div class="post-content">
			<?php
				if ($options->get("hide_shortcode_in_preview") == 'true'){
					$preview = wp_trim_words(do_shortcode(get_the_content('...')), $trim_words_count);
				}else{
					$preview = wp_trim_words(get_the_content('...'), $trim_words_count);
				}
				if (post_password_required()){
					$preview = __("这篇文章受密码保护，输入密码才能阅读", 'argon');
				}
				if ($preview == ""){
					$preview = __("这篇文章没有摘要", 'argon');
				}
				if ($post -> post_excerpt){
					$preview = $post -> post_excerpt;
				}
				echo $preview;
			?>
		</div>
	<?php
		}
	?>

	<?php if (has_tag()) { ?>
		<div class="post-tags">
			<i class="fa fa-tags" aria-hidden="true"></i>
			<?php
				$tags = get_the_tags();
				foreach ($tags as $tag) {
					echo "<a href='" . get_category_link($tag -> term_id) . "' target='_blank' class='tag badge badge-secondary post-meta-detail-tag'>" . $tag -> name . "</a>";
				}
			?>
		</div>
	<?php } ?>
</article>
