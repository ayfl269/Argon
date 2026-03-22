<?php
/**
 * The template for displaying all single shuoshuo
 *
 * @package ArgonModern
 */

get_header(); ?>

<div class="page-information-card-container">
	<div class="page-information-card card bg-gradient-secondary shadow-lg border-0">
		<div class="card-body">
			<h3 class="text-black"><?php _e( '说说', 'argon' ); ?></h3>
			<p class="text-black mt-3 mb-0 opacity-8">
				<i class="fa fa-quote-left mr-1"></i>
				<?php echo wp_count_posts( 'shuoshuo' )->publish; ?> <?php _e( '条说说', 'argon' ); ?>
			</p>
		</div>
	</div>
</div>

<?php get_sidebar(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', 'shuoshuo-details' );

			if ( \ArgonModern\Options::instance()->get( 'show_sharebtn' ) != 'false' ) :
				get_template_part( 'template-parts/share' );
			endif;

			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;
		endwhile;
		?>
<?php get_footer(); ?>
