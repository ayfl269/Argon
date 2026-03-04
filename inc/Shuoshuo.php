<?php

namespace ArgonModern;

class Shuoshuo {
	use Singleton;

	protected function setup() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'wp_ajax_upvote_shuoshuo', [ $this, 'ajax_upvote_shuoshuo' ] );
		add_action( 'wp_ajax_nopriv_upvote_shuoshuo', [ $this, 'ajax_upvote_shuoshuo' ] );

		// Admin column customization
		add_filter( 'manage_shuoshuo_posts_columns', [ $this, 'admin_columns' ] );
		add_action( 'manage_shuoshuo_posts_custom_column', [ $this, 'admin_column_content' ], 10, 2 );
		add_action( 'admin_head', [ $this, 'admin_css' ] );
	}

	public function admin_columns( $columns ) {
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			if ( $key == 'title' ) {
				$new_columns['shuoshuo_content'] = __( '内容', 'argon' );
				continue;
			}
			$new_columns[ $key ] = $value;
		}
		$new_columns['shuoshuo_upvotes'] = __( '赞', 'argon' );
		return $new_columns;
	}

	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'shuoshuo_content':
				$post = get_post( $post_id );
				echo '<a class="row-title" href="' . get_edit_post_link( $post_id ) . '">' . wp_trim_words( strip_tags( $post->post_content ), 50 ) . '</a>';
				break;
			case 'shuoshuo_upvotes':
				echo self::get_upvotes( $post_id );
				break;
		}
	}

	public function admin_css() {
		$screen = get_current_screen();
		if ( $screen && $screen->post_type == 'shuoshuo' && $screen->base == 'edit' ) {
			echo '<style>
				.column-shuoshuo_content { width: 60%; }
				.column-shuoshuo_upvotes { width: 80px; }
				.column-author, .column-comments, .column-date { width: 10%; }
			</style>';
		}
	}

	public function ajax_upvote_shuoshuo() {
		$id = isset( $_POST['shuoshuo_id'] ) ? intval( $_POST['shuoshuo_id'] ) : ( isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0 );
		if ( ! $id ) {
			wp_send_json_error( [ 'msg' => __( 'ID 错误', 'argon' ) ] );
		}

		$upvoted_list = isset( $_COOKIE['argon_shuoshuo_upvoted'] ) ? $_COOKIE['argon_shuoshuo_upvoted'] : '';
		if ( in_array( $id, explode( ',', $upvoted_list ) ) ) {
			wp_send_json_error( [ 'msg' => __( '该说说已被赞过', 'argon' ), 'total_upvote' => self::get_upvotes( $id ) ] );
		}

		self::set_upvotes( $id );
		setcookie( 'argon_shuoshuo_upvoted', $upvoted_list . $id . ',', time() + 31536000, '/' );

		wp_send_json_success( [
			'id'           => $id,
			'msg'          => __( '点赞成功', 'argon' ),
			'total_upvote' => self::get_upvotes( $id ),
		] );
	}

	public function register_post_type() {
		register_post_type( 'shuoshuo', [
			'labels'              => [
				'name'               => __( '说说', 'argon' ),
				'singular_name'      => __( '说说', 'argon' ),
				'add_new'            => __( '发表说说', 'argon' ),
				'add_new_item'       => __( '发表说说', 'argon' ),
				'edit_item'          => __( '编辑说说', 'argon' ),
				'new_item'           => __( '新说说', 'argon' ),
				'view_item'          => __( '查看说说', 'argon' ),
				'search_items'       => __( '搜索说说', 'argon' ),
				'not_found'          => __( '暂无说说', 'argon' ),
				'not_found_in_trash' => __( '没有已遗弃的说说', 'argon' ),
				'menu_name'          => __( '说说', 'argon' )
			],
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'exclude_from_search' => true,
			'query_var'           => true,
			'rewrite'             => [
				'slug'       => 'shuoshuo',
				'with_front' => false
			],
			'capability_type'     => 'post',
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_icon'           => 'dashicons-format-quote',
			'supports'            => [ 'editor', 'author', 'title', 'custom-fields', 'comments' ]
		] );
	}

	public static function get_upvotes( $post_id ) {
		$count_key = 'upvotes';
		$count = get_post_meta( $post_id, $count_key, true );
		if ( $count == '' ) {
			delete_post_meta( $post_id, $count_key );
			add_post_meta( $post_id, $count_key, '0' );
			$count = '0';
		}
		return number_format_i18n( (int) $count );
	}

	public static function set_upvotes( $post_id ) {
		if ( get_post_type( $post_id ) != 'shuoshuo' ) {
			return;
		}
		$count_key = 'upvotes';
		$count = (int) get_post_meta( $post_id, $count_key, true );
		if ( $count == 0 && get_post_meta( $post_id, $count_key, true ) == '' ) {
			update_post_meta( $post_id, $count_key, 1 );
		} else {
			update_post_meta( $post_id, $count_key, $count + 1 );
		}
	}
}
