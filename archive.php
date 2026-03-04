<?php get_header(); ?>

<div class="page-information-card-container">
	<div class="page-information-card card bg-gradient-secondary shadow-lg border-0">
		<div class="card-body">
			<h3 class="text-black"><?php the_archive_title(); ?></h3>
			<?php if ( get_the_archive_description() != '' ) { ?>
				<p class="text-black mt-3">
					<?php the_archive_description(); ?>
				</p>
			<?php } ?>
			<p class="text-black mt-3 mb-0 opacity-8">
				<i class="fa fa-file-o mr-1"></i>
				<?php global $wp_query; echo $wp_query->found_posts; ?> <?php _e('篇文章', 'argon');?>
			</p>
		</div>
	</div>
</div>

<?php get_sidebar(); ?>

<?php
	$waterflow_type = \ArgonModern\Options::instance()->get('article_list_waterflow', '1');
	$main_class = "site-main article-list article-list-archive";
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

	<?php else : ?>

		<?php get_template_part( 'template-parts/content', 'none-tag' ); ?>

	<?php endif; ?>

<?php get_footer(); ?>
