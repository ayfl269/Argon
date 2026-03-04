<article id="post-<?php the_ID(); ?>" <?php post_class( 'post card bg-white shadow-sm border-0 overflow-hidden' ); ?>>
	<header class="post-header text-center <?php echo has_post_thumbnail() ? 'post-header-with-thumbnail' : ''; ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="post-thumbnail-container">
				<?php the_post_thumbnail( 'large', [ 'class' => 'post-thumbnail' ] ); ?>
			</div>
			<div class="post-header-text-container">
		<?php endif; ?>

		<a class="post-title h3 font-weight-bold mb-3 d-block text-dark" href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>

		<div class="post-meta text-muted small mb-3">
			<?php \ArgonModern\Template::render_article_meta(); ?>
		</div>

		<?php if ( has_post_thumbnail() ) : ?>
			</div><!-- .post-header-text-container -->
		<?php endif; ?>
	</header>

	<div class="post-content" id="post_content">
		<?php
		if ( is_singular() ) :
			echo \ArgonModern\Template::get_post_outdated_info();
			the_content();
		else :
			the_excerpt();
		endif;
		?>
	</div>

	<?php if ( is_singular() ) : ?>
		<?php
		$reference_list = \ArgonModern\Template::get_reference_list();
		if ( ! empty( $reference_list ) ) {
			echo '<div class="post-references-container mt-4">' . $reference_list . '</div>';
		}
		?>

		<?php
		$donate_qrcode_url = \ArgonModern\Options::instance()->get( 'argon_donate_qrcode_url' );
		if ( ! empty( $donate_qrcode_url ) ) :
		?>
			<div class="post-donate mt-5 text-center">
				<button class="btn btn-danger donate-btn"><?php _e( '赞赏', 'argon' ); ?></button>
				<div class="donate-qrcode card shadow-sm bg-white mt-3" style="display: none; width: 200px; margin: 0 auto;">
					<div class="card-body p-2">
						<img src="<?php echo esc_url( $donate_qrcode_url ); ?>" class="img-fluid" alt="Donate">
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	
	<?php if ( has_tag() ) : ?>
		<div class="post-tags mt-3 small">
			<i class="fa fa-tags mr-1"></i>
			<?php
			$tags = get_the_tags();
			foreach ( $tags as $tag ) {
				echo '<a href="' . get_tag_link( $tag->term_id ) . '" class="badge badge-secondary tag post-meta-detail-tag">' . $tag->name . '</a> ';
			}
			?>
		</div>
	<?php endif; ?>
</article>
