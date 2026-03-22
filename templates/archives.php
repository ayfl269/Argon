<?php
/**
 * Template Name: 归档时间轴
 *
 * @package ArgonModern
 */

get_header(); ?>

<div class="page-information-card-container"></div>

<?php get_sidebar(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', 'timeline' );

			if ( \ArgonModern\Options::instance()->get( 'show_sharebtn' ) != 'false' ) :
				get_template_part( 'template-parts/share' );
			endif;

			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;
		endwhile;
		?>
<?php get_footer(); ?>
