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

			<?php
			while ( have_posts() ) :
				the_post();
				if (get_post_type() == 'shuoshuo'){
					get_template_part( 'template-parts/content-shuoshuo-preview' );
				}else{
					get_template_part( 'template-parts/content-preview', \ArgonModern\Options::instance()->get('article_list_layout', '1') );
				}
			endwhile;
			?>

		<?php
			echo \ArgonModern\Template::get_formatted_paginate_links_for_all_platforms();
		?>

	<?php endif; ?>

<?php get_footer(); ?>
