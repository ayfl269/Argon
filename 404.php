<?php get_header(); ?>

<div class="page-information-card-container"></div>

<?php get_sidebar(); ?>

<div id="primary" class="content-area col-12">
	<main id="main" class="site-main text-center py-5">
		<h1 class="display-1 font-weight-bold text-primary">404</h1>
		<h2 class="h3 mb-4"><?php _e( 'Oops! That page can&rsquo;t be found.', 'argon-modern' ); ?></h2>
		<p class="mb-5"><?php _e( 'It looks like nothing was found at this location. Maybe try a search?', 'argon-modern' ); ?></p>
		<div class="row justify-content-center">
			<div class="col-lg-6">
				<?php get_search_form(); ?>
			</div>
		</div>
		<div class="mt-5">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary"><?php _e( 'Go Home', 'argon-modern' ); ?></a>
		</div>
<?php get_footer(); ?>
