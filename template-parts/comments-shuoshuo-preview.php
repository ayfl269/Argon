<?php
/**
 * Template part for displaying shuoshuo comments preview
 *
 * @package ArgonModern
 */

if ( post_password_required() ) {
	return;
}
?>

<?php if ( have_comments() ) : ?>
	<div class="shuoshuo-comments">
		<ol class="comment-list">
			<?php
			wp_list_comments( [
				'type'     => 'comment',
				'callback' => 'argon_comment_shuoshuo_preview_format'
			] );
			?>
		</ol>
	</div>
<?php endif; ?>
