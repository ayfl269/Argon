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

<?php get_footer(); ?>
