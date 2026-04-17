<?php

namespace ArgonModern;

class Assets {
	use Singleton;

	protected function setup() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
	}

	public function enqueue_scripts() {
		$options = Options::instance();
		$version = ARGON_MODERN_VERSION;

		// Styles
		wp_enqueue_style( 'argon-original-css', self::get_asset_uri( '/assets/css/argon.min.css' ), [], $version );
		wp_enqueue_style( 'argon-modern-style', get_stylesheet_uri(), [ 'argon-original-css' ], $version );

		// Vendor Styles Registration
		wp_register_style( 'font-awesome', self::get_asset_uri( '/assets/vendor/font-awesome/css/font-awesome.min.css' ), [], '4.7.0' );
		wp_register_style( 'izitoast', self::get_asset_uri( '/assets/vendor/izitoast/css/iziToast.css' ), [], $version );
		wp_register_style( 'fancybox', self::get_asset_uri( '/assets/vendor/fancybox/jquery.fancybox.min.css' ), [], $version );
		wp_register_style( 'nprogress', self::get_asset_uri( '/assets/vendor/nprogress/nprogress.css' ), [], $version );
		wp_register_style( 'headindex', self::get_asset_uri( '/assets/vendor/headindex/headindex.css' ), [], $version );
		wp_register_style( 'argon-nouislider', self::get_asset_uri( '/assets/vendor/nouislider/css/nouislider.min.css' ), [], $version );
		wp_register_style( 'argon-pickr-monolith', self::get_asset_uri( '/assets/vendor/pickr/themes/monolith.min.css' ), [], $version );

		// Core Styles Enqueue
		wp_enqueue_style( 'font-awesome' );
		wp_enqueue_style( 'izitoast' );
		wp_enqueue_style( 'nprogress' );

		if ( $options->get( 'disable_googlefont' ) != 'true' ) {
			wp_enqueue_style( 'googlefont', '//fonts.googleapis.com/css?family=Open+Sans:400,600,700|Noto+Serif+SC:600&display=swap', [], null );
		}

		// Conditional Styles
		wp_enqueue_style( 'headindex' );

		if ( $options->get( 'enable_fancybox' ) != 'false' ) {
			wp_enqueue_style( 'fancybox' );
		}

		// Pickr and NoUiSlider are used in FAB settings popup
		if ( $options->get( 'fab_show_settings_button' ) != 'false' ) {
			wp_enqueue_style( 'argon-nouislider' );
			wp_enqueue_style( 'argon-pickr-monolith' );
		}

		// Code Highlight Theme
		if ( $options->get( 'argon_enable_code_highlight' ) == 'true' ) {
			$code_theme = $options->get( 'argon_code_highlight_theme', 'vs2015' );
			wp_enqueue_style( 'argon-code-highlight', self::get_asset_uri( '/assets/vendor/highlight/styles/' . $code_theme . '.css' ), [], $version );
		}

		// JS Vendor Registration
		wp_register_script( 'popper', self::get_asset_uri( '/assets/vendor/popper/popper.min.js' ), [], $version, true );
		wp_register_script( 'bootstrap', self::get_asset_uri( '/assets/vendor/bootstrap/bootstrap.min.js' ), [ 'jquery', 'popper' ], $version, true );
		wp_register_script( 'headroom', self::get_asset_uri( '/assets/vendor/headroom/headroom.min.js' ), [], $version, true );
		wp_register_script( 'nprogress', self::get_asset_uri( '/assets/vendor/nprogress/nprogress.js' ), [], $version, true );
		wp_register_script( 'clipboard', self::get_asset_uri( '/assets/vendor/clipboard/clipboard.min.js' ), [], $version, true );
		wp_register_script( 'izitoast', self::get_asset_uri( '/assets/vendor/izitoast/js/iziToast.min.js' ), [], $version, true );
		wp_register_script( 'pangu', self::get_asset_uri( '/assets/vendor/pangu/pangu.min.js' ), [], $version, true );
		wp_register_script( 'clamp', self::get_asset_uri( '/assets/vendor/clamp/clamp.min.js' ), [], $version, true );
		wp_register_script( 'tippy', self::get_asset_uri( '/assets/vendor/tippy.js/dist/tippy.umd.min.js' ), [ 'popper' ], $version, true );

		// Conditional Vendors
		if ( $options->get( 'enable_pjax' ) != 'false' ) {
			wp_register_script( 'pjax', self::get_asset_uri( '/assets/vendor/jquery-pjax-plus/jquery.pjax.plus.js' ), [ 'jquery' ], $version, true );
			wp_enqueue_script( 'pjax' );
		}

		wp_register_script( 'headindex', self::get_asset_uri( '/assets/vendor/headindex/headindex.js' ), [ 'jquery' ], $version, true );
		wp_enqueue_script( 'headindex' );

		if ( $options->get( 'enable_fancybox' ) != 'false' ) {
			wp_register_script( 'fancybox', self::get_asset_uri( '/assets/vendor/fancybox/jquery.fancybox.min.js' ), [ 'jquery' ], $version, true );
			wp_enqueue_script( 'fancybox' );
		}

		if ( $options->get( 'enable_zoomify' ) != 'false' ) {
			wp_register_script( 'zoomify', self::get_asset_uri( '/assets/vendor/zoomify/zoomify.js' ), [ 'jquery' ], $version, true );
			wp_enqueue_script( 'zoomify' );
		}

/*
		if ( $options->get( 'show_sharebtn' ) != 'false' ) {
			wp_enqueue_script( 'sharejs' );
		}
*/

		// Enqueue Core Vendors
		wp_enqueue_script( 'jquery' );
		wp_add_inline_script( 'jquery', 'window.$ = jQuery;', 'after' );
		wp_enqueue_script( 'popper' );
		wp_enqueue_script( 'bootstrap' );
		wp_enqueue_script( 'headroom' );
		wp_enqueue_script( 'nprogress' );
		wp_enqueue_script( 'izitoast' );
		wp_enqueue_script( 'clipboard' );
		wp_enqueue_script( 'pangu' );
		wp_enqueue_script( 'clamp' );
		wp_enqueue_script( 'tippy' );

		// Optional UI Vendors (Pickr/NoUiSlider)
		wp_enqueue_script( 'argon-nouislider', self::get_asset_uri( '/assets/vendor/nouislider/js/nouislider.min.js' ), [ 'jquery' ], $version, true );
		wp_enqueue_script( 'argon-pickr', self::get_asset_uri( '/assets/vendor/pickr/pickr.min.js' ), [ 'jquery' ], $version, true );



		// Code Highlight
		if ( $options->get( 'argon_enable_code_highlight' ) == 'true' ) {
			wp_enqueue_script( 'argon-highlight', self::get_asset_uri( '/assets/vendor/highlight/highlight.pack.js' ), [ 'jquery' ], $version, true );
			wp_enqueue_script( 'argon-highlight-ln', self::get_asset_uri( '/assets/vendor/highlight/highlightjs-line-numbers.min.js' ), [ 'argon-highlight' ], $version, true );
		}

		// Main Theme Scripts
		wp_enqueue_script( 'argon-original-js', self::get_asset_uri( '/assets/js/argon.min.js' ), [ 'jquery', 'bootstrap' ], $version, true );
		wp_add_inline_script( 'argon-original-js', 'window.$ = jQuery;', 'before' );
		
		$theme_deps = [ 'argon-original-js', 'izitoast', 'clipboard' ];
		if ( $options->get( 'enable_pjax' ) != 'false' ) {
			$theme_deps[] = 'pjax';
		}
		$theme_deps[] = 'headindex';
		if ( $options->get( 'argon_enable_code_highlight' ) == 'true' ) {
			$theme_deps[] = 'argon-highlight';
			$theme_deps[] = 'argon-highlight-ln';
		}
		
		wp_enqueue_script( 'argon-theme-js', ARGON_MODERN_URL . '/argontheme.js', $theme_deps, $version, true );
		wp_add_inline_script( 'argon-theme-js', 'window.$ = jQuery;', 'before' );
	}

	public function admin_enqueue_scripts() {
		// wp_enqueue_style( 'argon-modern-admin', ARGON_MODERN_URL . '/admin.css', [], ARGON_MODERN_VERSION );
	}

	/**
	 * Get URI for an asset
	 *
	 * @param string $path
	 * @return string
	 */
	public static function get_asset_uri( $path = '' ) {
		$options = Options::instance();
		$assets_source = $options->get( "argon_assets_path" );
		$version = ARGON_MODERN_VERSION;
		$base_url = ARGON_MODERN_URL;

		switch ( $assets_source ) {
			case "jsdelivr":
				$base_url = "https://cdn.jsdelivr.net/gh/solstice23/argon-theme@" . $version;
				break;
			case "fastgit":
				$base_url = "https://raw.fastgit.org/solstice23/argon-theme/v" . $version;
				break;
			case "sourcegcdn":
				$base_url = "https://gh.sourcegcdn.com/solstice23/argon-theme/v" . $version;
				break;
			case "jsdelivr_gcore":
				$base_url = "https://gcore.jsdelivr.net/gh/solstice23/argon-theme@" . $version;
				break;
			case "jsdelivr_fastly":
				$base_url = "https://fastly.jsdelivr.net/gh/solstice23/argon-theme@" . $version;
				break;
			case "jsdelivr_cf":
				$base_url = "https://testingcf.jsdelivr.net/gh/solstice23/argon-theme@" . $version;
				break;
			case "custom":
				$base_url = preg_replace( '/\/$/', '', $options->get( "argon_custom_assets_path" ) );
				$base_url = str_replace( '%theme_version%', $version, $base_url );
				break;
		}

		return $base_url . ( $path ? '/' . ltrim( $path, '/' ) : '' );
	}
}

// Optimization: Defer non-essential scripts to eliminate render-blocking
add_filter('script_loader_tag', function($tag, $handle) {
	// Don't defer jQuery or core scripts that might be needed immediately
	$skip_defer = ['jquery', 'jquery-core', 'jquery-migrate'];
	if (in_array($handle, $skip_defer)) {
		return $tag;
	}
	// Add defer attribute to all other scripts
	if (strpos($tag, 'defer') === false) {
		return str_replace(' src', ' defer src', $tag);
	}
	return $tag;
}, 10, 2);
