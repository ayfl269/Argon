<?php

namespace ArgonModern;

class Core {
	use Singleton;

	protected function setup() {
		$options = Options::instance();

		add_action( 'after_setup_theme', [ $this, 'init_update_checker' ] );
		add_action( 'after_setup_theme', [ $this, 'init_analytics' ] );
		add_action( 'widgets_init', [ $this, 'widgets_init' ] );
		add_action( 'wp_ajax_update_post_meta_ajax', [ $this, 'ajax_update_post_meta_ajax' ] );
		add_filter( 'excerpt_length', [ $this, 'excerpt_length' ], 999 );
		add_filter( 'excerpt_more', [ $this, 'excerpt_more' ] );

		// Timezone fix
		if ( $options->get( 'enable_timezone_fix' ) == 'true' ) {
			date_default_timezone_set( 'UTC' );
		}

		// Remove query strings from static resources
		add_filter( 'script_loader_src', [ $this, 'remove_query_strings' ], 15, 1 );
		add_filter( 'style_loader_src', [ $this, 'remove_query_strings' ], 15, 1 );

		// Register nav menus
		add_action( 'init', [ $this, 'register_menus' ] );

		// Gutenberg & TinyMCE
		add_action( 'init', [ $this, 'init_gutenberg_blocks' ] );
		add_action( 'init', [ $this, 'init_tinymce_buttons' ] );
		add_filter( 'admin_head', [ $this, 'admin_i18n_info' ] );

		// Admin interface beautification
		add_action( 'admin_init', [ $this, 'add_admin_color_scheme' ] );
		add_action( 'admin_head', [ $this, 'admin_theme_color_css' ] );

		// Enable link manager (Links menu in dashboard)
		add_filter( 'pre_option_link_manager_enabled', '__return_true' );

		// Search filters
		add_filter( 'pre_get_posts', [ $this, 'search_filter' ] );

		// Login page style
		if ( $options->get( 'enable_login_css' ) == 'true' ) {
			add_action( 'login_head', [ $this, 'login_page_style' ] );
		}

		// Performance: Cache headers
		add_action( 'send_headers', [ $this, 'add_cache_control_headers' ] );

		// Gravatar CDN & Text Gravatar
		if ( $options->get( 'gravatar_cdn', '' ) != '' ) {
			add_filter( 'get_avatar_url', [ $this, 'gravatar_cdn' ] );
		}
		if ( $options->get( 'text_gravatar', 'false' ) == 'true' && ! is_admin() ) {
			add_filter( 'get_avatar_url', [ $this, 'text_gravatar' ] );
		}

		// Permalinks: .html for pages
		if ( $options->get( 'page_add_html' ) == 'true' ) {
			add_action( 'init', [ $this, 'rewrite_page_html' ], -1 );
			add_filter( 'user_trailingslashit', [ $this, 'no_page_slash' ], 100, 2 );
			add_action( 'save_post', [ $this, 'flush_rewrite_rules' ], 20 );
		}

		// Meta Boxes
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_meta_data' ] );

		// Home filters
		if ( $options->get( "home_show_shuoshuo" ) == "true" ) {
			add_action( 'pre_get_posts', [ $this, 'home_add_shuoshuo' ] );
		}
		if ( $options->get( "hide_categories" ) != "" ) {
			add_action( 'pre_get_posts', [ $this, 'home_hide_categories' ] );
		}

		// Mail unsubscribe rewrite rule
		add_action( 'init', [ $this, 'add_mail_unsubscribe_rule' ] );
	}

	public function add_mail_unsubscribe_rule() {
		add_rewrite_rule( '^unsubscribe-comment-mailnotice/?(.*)$', 'wp-content/themes/argon-modern/unsubscribe-comment-mailnotice.php$1', 'top' );
	}

	public function add_meta_boxes() {
		add_meta_box( 'argon_meta_box_1', __( "文章设置", 'argon' ), [ $this, 'render_meta_box_1' ], [ 'post', 'page' ], 'side', 'low' );
	}

	public function render_meta_box_1() {
		wp_nonce_field( "argon_meta_box_nonce_action", "argon_meta_box_nonce" );
		global $post;
		$argon_meta_hide_readingtime      = get_post_meta( $post->ID, "argon_hide_readingtime", true );
		$argon_meta_simple                 = get_post_meta( $post->ID, "argon_meta_simple", true );
		$argon_first_image_as_thumbnail    = get_post_meta( $post->ID, "argon_first_image_as_thumbnail", true );
		$argon_show_post_outdated_info     = get_post_meta( $post->ID, "argon_show_post_outdated_info", true );
		$argon_after_post                  = get_post_meta( $post->ID, "argon_after_post", true );
		$argon_custom_css                  = get_post_meta( $post->ID, "argon_custom_css", true );
		?>
		<h4><?php _e( "显示字数和预计阅读时间", 'argon' ); ?></h4>
		<select name="argon_meta_hide_readingtime" id="argon_meta_hide_readingtime">
			<option value="false" <?php selected( $argon_meta_hide_readingtime, 'false' ); ?>><?php _e( "跟随全局设置", 'argon' ); ?></option>
			<option value="true" <?php selected( $argon_meta_hide_readingtime, 'true' ); ?>><?php _e( "不显示", 'argon' ); ?></option>
		</select>
		<p style="margin-top: 15px;"><?php _e( "是否显示字数和预计阅读时间 Meta 信息", 'argon' ); ?></p>
		<h4><?php _e( "Meta 中隐藏发布时间和分类", 'argon' ); ?></h4>
		<select name="argon_meta_simple" id="argon_meta_simple">
			<option value="false" <?php selected( $argon_meta_simple, 'false' ); ?>><?php _e( "不隐藏", 'argon' ); ?></option>
			<option value="true" <?php selected( $argon_meta_simple, 'true' ); ?>><?php _e( "隐藏", 'argon' ); ?></option>
		</select>
		<p style="margin-top: 15px;"><?php _e( "适合特定的页面，例如友链页面。开启后文章 Meta 的第一行只显示阅读数和评论数。", 'argon' ); ?></p>
		<h4><?php _e( "使用文章中第一张图作为头图", 'argon' ); ?></h4>
		<select name="argon_first_image_as_thumbnail" id="argon_first_image_as_thumbnail">
			<option value="default" <?php selected( $argon_first_image_as_thumbnail, 'default' ); ?>><?php _e( "跟随全局设置", 'argon' ); ?></option>
			<option value="true" <?php selected( $argon_first_image_as_thumbnail, 'true' ); ?>><?php _e( "使用", 'argon' ); ?></option>
			<option value="false" <?php selected( $argon_first_image_as_thumbnail, 'false' ); ?>><?php _e( "不使用", 'argon' ); ?></option>
		</select>
		<h4><?php _e( "显示文章过时信息", 'argon' ); ?></h4>
		<div style="display: flex;">
			<select name="argon_show_post_outdated_info" id="argon_show_post_outdated_info">
				<option value="default" <?php selected( $argon_show_post_outdated_info, 'default' ); ?>><?php _e( "跟随全局设置", 'argon' ); ?></option>
				<option value="always" <?php selected( $argon_show_post_outdated_info, 'always' ); ?>><?php _e( "一直显示", 'argon' ); ?></option>
				<option value="never" <?php selected( $argon_show_post_outdated_info, 'never' ); ?>><?php _e( "永不显示", 'argon' ); ?></option>
			</select>
			<button id="apply_show_post_outdated_info" type="button" class="components-button is-primary" style="height: 22px; display: none;"><?php _e( "应用", 'argon' ); ?></button>
		</div>
		<p style="margin-top: 15px;"><?php _e( "单独控制该文章的过时信息显示。", 'argon' ); ?></p>
		<h4><?php _e( "文末附加内容", 'argon' ); ?></h4>
		<textarea name="argon_after_post" id="argon_after_post" rows="3" cols="30" style="width:100%;"><?php echo esc_textarea( $argon_after_post ); ?></textarea>
		<p style="margin-top: 15px;"><?php _e( "给该文章设置单独的文末附加内容，留空则跟随全局，设为 <code>--none--</code> 则不显示。", 'argon' ); ?></p>
		<h4><?php _e( "自定义 CSS", 'argon' ); ?></h4>
		<textarea name="argon_custom_css" id="argon_custom_css" rows="5" cols="30" style="width:100%;"><?php echo esc_textarea( $argon_custom_css ); ?></textarea>
		<p style="margin-top: 15px;"><?php _e( "给该文章添加单独的 CSS", 'argon' ); ?></p>

		<script>
			window.jQuery(document).ready(function($){
				function showAlert(type, message){
					if (window.wp && wp.data){
						wp.data.dispatch('core/notices').createNotice(
							type,
							message,
							{ type: "snackbar", isDismissible: true, }
						);
					} else {
						alert(message);
					}
				}
				$("#argon_show_post_outdated_info").change(function(){
					$("#apply_show_post_outdated_info").css("display", "");
				});
				$("#apply_show_post_outdated_info").click(function(){
					var btn = $(this);
					btn.addClass("is-busy").attr("disabled", "disabled").css("opacity", "0.5");
					$("#argon_show_post_outdated_info").attr("disabled", "disabled");
					var data = {
						action: 'update_post_meta_ajax',
						argon_meta_box_nonce: $("#argon_meta_box_nonce").val(),
						post_id: <?php echo $post->ID; ?>,
						meta_key: 'argon_show_post_outdated_info',
						meta_value: $("#argon_show_post_outdated_info").val()
					};
					$.ajax({
						url: ajaxurl,
						type: 'post',
						data: data,
						success: function(response) {
							btn.removeClass("is-busy").removeAttr("disabled").css("opacity", "1");
							$("#argon_show_post_outdated_info").removeAttr("disabled");
							if (response.success){
								btn.css("display", "none");
								showAlert("success", "<?php _e( "应用成功", 'argon' ); ?>");
							} else {
								showAlert("error", "<?php _e( "应用失败", 'argon' ); ?>");
							}
						},
						error: function() {
							btn.removeClass("is-busy").removeAttr("disabled").css("opacity", "1");
							$("#argon_show_post_outdated_info").removeAttr("disabled");
							showAlert("error", "<?php _e( "应用失败", 'argon' ); ?>");
						}
					});
				});
			});
		</script>
		<?php
	}

	public function save_meta_data( $post_id ) {
		if ( ! isset( $_POST['argon_meta_box_nonce'] ) ) {
			return $post_id;
		}
		if ( ! wp_verify_nonce( $_POST['argon_meta_box_nonce'], 'argon_meta_box_nonce_action' ) ) {
			return $post_id;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		if ( isset( $_POST['post_type'] ) && $_POST['post_type'] == 'post' ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		} elseif ( isset( $_POST['post_type'] ) && $_POST['post_type'] == 'page' ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		}

		$keys = [
			'argon_hide_readingtime',
			'argon_meta_simple',
			'argon_first_image_as_thumbnail',
			'argon_show_post_outdated_info',
			'argon_after_post',
			'argon_custom_css'
		];

		foreach ( $keys as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, $_POST[ $key ] );
			}
		}
	}

	public function home_add_shuoshuo( $query ) {
		if ( is_home() && $query->is_main_query() ) {
			$query->set( 'post_type', [ 'post', 'shuoshuo' ] );
		}
		return $query;
	}

	public function home_hide_categories( $query ) {
		if ( is_home() && $query->is_main_query() ) {
			$excludeCategories = explode( ",", get_option( "argon_hide_categories" ) );
			$excludeCategories = array_map( function ( $cat ) {
				return - $cat;
			}, $excludeCategories );
			$query->set( 'category__not_in', $excludeCategories );
			$query->set( 'tag__not_in', $excludeCategories );
		}
		return $query;
	}

	public function register_menus() {
		register_nav_menus( [
			'toolbar_menu'         => __( '顶部导航', 'argon' ),
			'leftbar_menu'         => __( '左侧栏菜单', 'argon' ),
			'leftbar_author_links' => __( '左侧栏作者个人链接', 'argon' ),
			'leftbar_friend_links' => __( '左侧栏友情链接', 'argon' )
		] );
	}

	public function init_gutenberg_blocks() {
		// Ported from argon/functions.php:argon_init_gutenberg_blocks
		// Add block categories or register block types here if needed
	}

	public function init_tinymce_buttons() {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		if ( get_user_option( 'rich_editing' ) == 'true' ) {
			add_filter( 'mce_external_plugins', [ $this, 'add_tinymce_plugin' ] );
			add_filter( 'mce_buttons', [ $this, 'register_tinymce_buttons' ] );
			add_editor_style( ARGON_MODERN_URL . "/assets/tinymce_assets/tinymce_editor_codeblock.css" );
		}
	}

	public function register_tinymce_buttons( $buttons ) {
		array_push( $buttons, "|", "codeblock", "|", "label", "", "checkbox", "", "progressbar", "", "alert", "", "admonition", "", "collapse", "", "timeline", "", "github", "", "video", "", "hiddentext" );
		return $buttons;
	}

	public function add_tinymce_plugin( $plugins ) {
		$plugin_url              = ARGON_MODERN_URL . '/assets/tinymce_assets/tinymce_btns.js';
		$plugins['codeblock']   = $plugin_url;
		$plugins['label']       = $plugin_url;
		$plugins['checkbox']    = $plugin_url;
		$plugins['progressbar'] = $plugin_url;
		$plugins['alert']       = $plugin_url;
		$plugins['admonition']  = $plugin_url;
		$plugins['collapse']    = $plugin_url;
		$plugins['timeline']    = $plugin_url;
		$plugins['github']      = $plugin_url;
		$plugins['video']       = $plugin_url;
		$plugins['hiddentext']  = $plugin_url;
		return $plugins;
	}

	public function admin_i18n_info() {
		if ( function_exists( 'argon_get_locate' ) ) {
			echo "<script>var argon_language = '" . argon_get_locate() . "';</script>";
		}
	}

	public function add_admin_color_scheme() {
		wp_admin_css_color(
			'argon',
			'Argon',
			ARGON_MODERN_URL . "/admin.css",
			[ "#5e72e4", "#324cdc", "#e8ebfb" ],
			[ 'base' => '#525f7f', 'focus' => '#5e72e4', 'current' => '#fff' ]
		);
	}

	public function admin_theme_color_css() {
		// Only inject if current user's color scheme is 'argon'
		if ( get_user_option( 'admin_color' ) !== 'argon' ) {
			return;
		}

		$options    = Options::instance();
		$themecolor = $options->get( "theme_color", "#5e72e4" );
		$RGB        = Utils::hexstr2rgb( $themecolor );
		$HSL        = Utils::rgb2hsl( $RGB['R'], $RGB['G'], $RGB['B'] );
		echo "
			<style id='themecolor_css'>
				:root{
					--themecolor: {$themecolor} ;
					--themecolor-R: {$RGB['R']} ;
					--themecolor-G: {$RGB['G']} ;
					--themecolor-B: {$RGB['B']} ;
					--themecolor-H: {$HSL['H']} ;
					--themecolor-S: {$HSL['S']} ;
					--themecolor-L: {$HSL['L']} ;
				}
			</style>
		";
		if ( $options->get( "enable_immersion_color", "false" ) == "true" ) {
			echo "<script> document.documentElement.classList.add('immersion-color'); </script>";
		}
	}

	public function search_filter( $query ) {
		if ( ! $query->is_search || is_admin() ) {
			return $query;
		}
		if ( get_option( 'argon_enable_search_filters', 'true' ) == 'false' ) {
			return $query;
		}
		$query->set( 'post_type', $this->get_search_post_types() );
		return $query;
	}

	private function get_search_post_types() {
		$search_filters_type = get_option( "argon_search_filters_type", "*post,*page,shuoshuo" );
		$search_filters_type = explode( ',', $search_filters_type );
		if ( ! isset( $_GET['post_type'] ) ) {
			$default = array_filter( $search_filters_type, function ( $str ) {
				return $str[0] == '*';
			} );
			return array_map( function ( $str ) {
				return substr( $str, 1 );
			}, $default );
		}
		$search_filters_type = array_map( function ( $str ) {
			return $str[0] == '*' ? substr( $str, 1 ) : $str;
		}, $search_filters_type );
		$post_type           = explode( ',', $_GET['post_type'] );
		$arr                 = [];
		foreach ( $search_filters_type as $type ) {
			if ( in_array( $type, $post_type ) ) {
				$arr[] = $type;
			}
		}
		return empty( $arr ) ? [ 'none' ] : $arr;
	}

	public function login_page_style() {
		wp_enqueue_style( "argon_login_css", ARGON_MODERN_URL . "/login.css", null, ARGON_MODERN_VERSION );
	}

	public function add_cache_control_headers() {
		header( "Cache-Control: max-age=604800, must-revalidate" );
		header( "Pragma: cache" );
		header( "Expires: " . gmdate( "D, d M Y H:i:s", time() + 3600 ) . " GMT" );
	}

	public function rewrite_page_html() {
		global $wp_rewrite;
		if ( ! strpos( $wp_rewrite->get_page_permastruct(), '.html' ) ) {
			$wp_rewrite->page_structure = untrailingslashit( $wp_rewrite->page_structure ) . '.html';
		}
	}

	public function no_page_slash( $string, $type ) {
		global $wp_rewrite;
		if ( $wp_rewrite->using_permalinks() && $type == 'page' ) {
			return untrailingslashit( $string );
		}
		return $string;
	}

	public function flush_rewrite_rules() {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	public function remove_query_strings( $src ) {
		if ( ! is_admin() && ( strpos( $src, '?ver=' ) || strpos( $src, '&ver=' ) ) ) {
			$src = remove_query_arg( 'ver', $src );
		}
		return $src;
	}

	public function gravatar_cdn( $url ) {
		$cdn = get_option( 'argon_gravatar_cdn', 'gravatar.pho.ink/avatar/' );
		$cdn = str_replace( "http://", "", $cdn );
		$cdn = str_replace( "https://", "", $cdn );
		if ( substr( $cdn, -1 ) != '/' ) {
			$cdn .= "/";
		}
		$url = preg_replace( "/\/\/(.*?).gravatar.com\/avatar\//", "//" . $cdn, $url );
		return $url;
	}

	public function text_gravatar( $url ) {
		$url = preg_replace( "/[?&]d[^&]+/i", "", $url );
		$url .= '&d=404';
		return $url;
	}

	public function init_update_checker() {
		$update_checker_path = ARGON_MODERN_PATH . '/theme-update-checker/plugin-update-checker.php';
		if ( ! file_exists( $update_checker_path ) ) {
			return;
		}

		require_once( $update_checker_path );
		$update_source = get_option( 'argon_update_source' );

		switch ( $update_source ) {
			case "stop":
				break;
			case "fastgit":
				\Puc_v4_Factory::buildUpdateChecker(
					'https://api.solstice23.top/argon/info.json?source=fastgit',
					ARGON_MODERN_PATH . '/functions.php',
					'argon'
				);
				break;
			case "cfworker":
				\Puc_v4_Factory::buildUpdateChecker(
					'https://api.solstice23.top/argon/info.json?source=cfworker',
					ARGON_MODERN_PATH . '/functions.php',
					'argon'
				);
				break;
			case "solstice23top":
				\Puc_v4_Factory::buildUpdateChecker(
					'https://api.solstice23.top/argon/info.json?source=0',
					ARGON_MODERN_PATH . '/functions.php',
					'argon'
				);
				break;
			case "github":
			default:
				\Puc_v4_Factory::buildUpdateChecker(
					'https://raw.githubusercontent.com/solstice23/argon-theme/master/info.json',
					ARGON_MODERN_PATH . '/functions.php',
					'argon'
				);
		}
	}

	public function init_analytics() {
		if ( get_option( 'argon_has_inited' ) != 'true' ) {
			$this->post_analytics_info();
		}
	}

	private function post_analytics_info() {
		$theme_version = defined( 'ARGON_MODERN_VERSION' ) ? ARGON_MODERN_VERSION : '2.0.0';
		$url           = 'https://api.solstice23.top/argon_analytics/index.php?domain=' . urlencode( $_SERVER['HTTP_HOST'] ) . '&version=' . urlencode( $theme_version );

		wp_remote_get( $url, [
			'timeout' => 10,
			'headers' => [ 'User-Agent' => 'ArgonThemeModern' ],
		] );

		update_option( 'argon_has_inited', 'true' );
	}

	public function widgets_init() {
		register_sidebar( [
			'name'          => __( '左侧栏小工具', 'argon' ),
			'id'            => 'leftbar-tools',
			'description'   => __( '左侧栏小工具 (如果设置会在侧栏增加一个 Tab)', 'argon' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s card bg-white border-0">',
			'after_widget'  => '</div>',
			'before_title'  => '<h6 class="font-weight-bold text-black">',
			'after_title'   => '</h6>',
		] );
		register_sidebar( [
			'name'          => __( '右侧栏小工具', 'argon' ),
			'id'            => 'rightbar-tools',
			'description'   => __( '右侧栏小工具 (在 "Argon 主题选项" 中选择 "三栏布局" 才会显示)', 'argon' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s card shadow-sm bg-white border-0">',
			'after_widget'  => '</div>',
			'before_title'  => '<h6 class="font-weight-bold text-black">',
			'after_title'   => '</h6>',
		] );
		register_sidebar( [
			'name'          => __( '站点概览额外内容', 'argon' ),
			'id'            => 'leftbar-siteinfo-extra-tools',
			'description'   => __( '站点概览额外内容', 'argon' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s card bg-white border-0">',
			'after_widget'  => '</div>',
			'before_title'  => '<h6 class="font-weight-bold text-black">',
			'after_title'   => '</h6>',
		] );
	}

	public function ajax_update_post_meta_ajax() {
		$nonce = isset( $_POST['argon_meta_box_nonce'] ) ? $_POST['argon_meta_box_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'argon_meta_box_nonce_action' ) ) {
			wp_send_json_error( [ 'msg' => __( 'Nonce 验证失败', 'argon' ) ] );
		}
		$post_id    = intval( $_POST["post_id"] );
		$meta_key   = isset( $_POST["meta_key"] ) ? sanitize_key( wp_unslash( $_POST["meta_key"] ) ) : '';
		$meta_value = isset( $_POST["meta_value"] ) ? wp_unslash( $_POST["meta_value"] ) : '';

		$allowed_meta_keys = [
			'argon_hide_readingtime',
			'argon_meta_simple',
			'argon_first_image_as_thumbnail',
			'argon_show_post_outdated_info',
			'argon_after_post',
			'argon_custom_css',
		];

		if ( ! $post_id || ! in_array( $meta_key, $allowed_meta_keys, true ) ) {
			wp_send_json_error( [ 'msg' => __( '非法请求参数', 'argon' ) ] );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( [ 'msg' => __( '您没有权限进行此操作', 'argon' ) ] );
		}

		if ( get_post_meta( $post_id, $meta_key, true ) == $meta_value ) {
			wp_send_json_success();
		}

		$result = update_post_meta( $post_id, $meta_key, $meta_value );

		if ( $result ) {
			wp_send_json_success();
		} else {
			wp_send_json_error();
		}
	}

	public function excerpt_length( $length ) {
		return 100;
	}

	public function excerpt_more( $more ) {
		return '...';
	}
}
