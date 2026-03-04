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

<?php get_footer(); ?>
