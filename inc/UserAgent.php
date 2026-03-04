<?php

namespace ArgonModern;

class UserAgent {
	use Singleton;

	private $ua_icons = [];

	protected function setup() {
		$this->init_icons();
	}

	/**
	 * Parses a user agent string into its important parts
	 *
	 * @param string|null $u_agent User agent string to parse or null. Uses $_SERVER['HTTP_USER_AGENT'] on NULL
	 * @return array an array with platform, browser and version keys
	 */
	public static function parse( $u_agent = null ) {
		require_once __DIR__ . '/useragent-parser.php';
		return \argon_parse_user_agent( $u_agent );
	}

	private function init_icons() {
		require_once __DIR__ . '/useragent-parser.php';
		
		if ( isset( $GLOBALS['UA_ICON'] ) ) {
			$this->ua_icons = $GLOBALS['UA_ICON'];
		}
	}

	public function get_icon( $name ) {
		if ( isset( $this->ua_icons[ $name ] ) ) {
			return $this->ua_icons[ $name ];
		}
		
		return $this->ua_icons['Unknown'];
	}

	/**
	 * Renders User Agent info with icons
	 *
	 * @param string $userAgent
	 * @return string
	 */
	public static function render_ua_info( $userAgent ) {
		$options = Options::instance();
		$ua_config = $options->get( 'argon_comment_ua' );
		
		if ( empty( $ua_config ) || $ua_config === 'hidden' ) {
			return '';
		}

		$show_platform = strpos( $ua_config, 'platform' ) !== false;
		$show_browser  = strpos( $ua_config, 'browser' ) !== false;
		$show_version  = strpos( $ua_config, 'version' ) !== false;

		$parsed = self::parse( $userAgent );
		$instance = self::instance();
		
		$out = "<div class='comment-useragent'>";
		
		if ( $show_platform ) {
			$platform_icon = $instance->get_icon( $parsed['platform'] );
			$out .= $platform_icon . " " . $parsed['platform'];
		}
		
		if ( $show_browser ) {
			if ( $show_platform ) {
				$out .= ' ';
			}
			$browser_icon = $instance->get_icon( $parsed['browser'] );
			$out .= $browser_icon . " " . $parsed['browser'];
			
			if ( $show_version && ! empty( $parsed['version'] ) ) {
				$out .= ' ' . $parsed['version'];
			}
		}
		
		$out .= "</div>";
		
		return apply_filters( 'argon_comment_ua_icon', $out );
	}
}
