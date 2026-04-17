<?php

namespace ArgonModern;

class Options {
	use Singleton;

	private $settings_key = 'argon_modern_settings';
	private $defaults = [];

	protected function setup() {
		$this->defaults = [
			'theme_color' => '#5e72e4',
			'enable_immersion_color' => 'false',
			'darkmode_autoswitch' => 'false',
			'enable_amoled_dark' => 'false',
			'card_radius' => '4',
			'card_shadow' => 'default',
			'banner_title' => get_bloginfo( 'name' ),
			'banner_subtitle' => get_bloginfo( 'description' ),
			'banner_background_url' => '',
			'banner_background_hide_shapes' => 'false',
			'banner_background_color_type' => 'shape-primary',
			'enable_dark_mode' => true,
			'show_author' => true,
			'page_add_html' => 'false',
			'seo_description' => '',
			'seo_keywords' => '',
			'show_customize_theme_color_picker' => 'true',
			'show_admin_bar' => 'true',
			'enable_mobile_scale' => 'false',
			'page_background_url' => '',
			'page_background_dark_url' => '',
			'page_background_opacity' => '1',
			'page_background_banner_style' => 'false',
			'argon_enable_comment_upvote' => 'false',
			'argon_enable_comment_pinning' => 'false',
			'argon_comment_ua' => 'browser',
			'argon_comment_allow_mailnotice' => 'false',
			'argon_enable_code_highlight' => 'false',
			'argon_code_highlight_theme' => 'argon-monokai',
			'argon_code_highlight_hide_linenumber' => 'false',
			'argon_code_highlight_transparent_linenumber' => 'false',
			'argon_code_highlight_break_line' => 'false',
			'argon_math_render' => 'none',
			'argon_mathjax_cdn_url' => '//cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml-full.js',
			'argon_mathjax_v2_cdn_url' => '//cdn.jsdelivr.net/npm/mathjax@2.7.5/MathJax.js?config=TeX-AMS_HTML',
			'argon_katex_cdn_url' => '//cdn.jsdelivr.net/npm/katex@0.11.1/dist/',
			'argon_disable_big_image_threshold' => 'false',
			'argon_disable_intermediate_image_sizes' => 'false',
			'argon_disable_image_srcset' => 'false',
			'argon_disable_image_scaling' => 'false',
			'argon_disable_image_editor_attributes' => 'false',
		];
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		// add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
	}

	public function register_settings() {
		register_setting( 'argon_modern_group', $this->settings_key, [
			'sanitize_callback' => [ $this, 'sanitize_settings' ],
			'default'           => $this->defaults,
		] );
	}

	public function add_admin_menu() {
		// Use legacy themeoptions_page from settings.php
		add_menu_page(
			__( 'Argon Theme Settings', 'argon-modern' ),
			__( 'Argon Theme Options', 'argon-modern' ),
			'edit_theme_options',
			'argon-theme-options',
			'themeoptions_page'
		);
	}

	public function sanitize_settings( $input ) {
		$sanitized = [];
		foreach ( $this->defaults as $key => $default ) {
			if ( isset( $input[ $key ] ) ) {
				$sanitized[ $key ] = sanitize_text_field( $input[ $key ] );
			} else {
				$sanitized[ $key ] = $default;
			}
		}
		return $sanitized;
	}

	public function get( $key, $default = null ) {
		// 1. Try with 'argon_' prefix (legacy)
		$legacy_key = ( strpos( $key, 'argon_' ) === 0 ) ? $key : 'argon_' . $key;
		$val = get_option( $legacy_key );
		if ( $val !== false && $val !== '' ) {
			return $val;
		}

		// 2. Try the exact key (modern or global)
		$val = get_option( $key );
		if ( $val !== false && $val !== '' ) {
			return $val;
		}

		// 3. Fallback to serialized modern settings
		$options = get_option( $this->settings_key );
		if ( is_array( $options ) && isset( $options[ $key ] ) ) {
			return $options[ $key ];
		}

		return $default ?: ( isset( $this->defaults[ $key ] ) ? $this->defaults[ $key ] : null );
	}

	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Argon Modern Theme Settings', 'argon-modern' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'argon_modern_group' );
				do_settings_sections( 'argon-modern-settings' );
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'Theme Color', 'argon-modern' ); ?></th>
						<td>
							<input type="color" name="<?php echo $this->settings_key; ?>[theme_color]" value="<?php echo esc_attr( $this->get( 'theme_color' ) ); ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Banner Title', 'argon-modern' ); ?></th>
						<td>
							<input type="text" name="<?php echo $this->settings_key; ?>[banner_title]" value="<?php echo esc_attr( $this->get( 'banner_title' ) ); ?>" class="regular-text" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Banner Subtitle', 'argon-modern' ); ?></th>
						<td>
							<textarea name="<?php echo $this->settings_key; ?>[banner_subtitle]" class="regular-text"><?php echo esc_textarea( $this->get( 'banner_subtitle' ) ); ?></textarea>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
