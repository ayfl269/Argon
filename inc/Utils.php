<?php

namespace ArgonModern;

class Utils {
	public static function rgb2hsl( $R, $G, $B ) {
		$r       = $R / 255;
		$g       = $G / 255;
		$b       = $B / 255;
		$var_Min = min( $r, $g, $b );
		$var_Max = max( $r, $g, $b );
		$del_Max = $var_Max - $var_Min;
		$L       = ( $var_Max + $var_Min ) / 2;
		if ( $del_Max == 0 ) {
			$H = 0;
			$S = 0;
		} else {
			if ( $L < 0.5 ) {
				$S = $del_Max / ( $var_Max + $var_Min );
			} else {
				$S = $del_Max / ( 2 - $var_Max - $var_Min );
			}
			$del_R = ( ( ( $var_Max - $r ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
			$del_G = ( ( ( $var_Max - $g ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
			$del_B = ( ( ( $var_Max - $b ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
			if ( $r == $var_Max ) {
				$H = $del_B - $del_G;
			} else if ( $g == $var_Max ) {
				$H = ( 1 / 3 ) + $del_R - $del_B;
			} else if ( $b == $var_Max ) {
				$H = ( 2 / 3 ) + $del_G - $del_R;
			}
			if ( $H < 0 ) {
				$H += 1;
			}
			if ( $H > 1 ) {
				$H -= 1;
			}
		}
		return [
			'h' => $H, //0~1
			's' => $S,
			'l' => $L,
			'H' => round( $H * 360 ), //0~360
			'S' => round( $S * 100 ), //0~100
			'L' => round( $L * 100 ), //0~100
		];
	}

	public static function Hue_2_RGB( $v1, $v2, $vH ) {
		if ( $vH < 0 ) {
			$vH += 1;
		}
		if ( $vH > 1 ) {
			$vH -= 1;
		}
		if ( ( 6 * $vH ) < 1 ) {
			return ( $v1 + ( $v2 - $v1 ) * 6 * $vH );
		}
		if ( ( 2 * $vH ) < 1 ) {
			return $v2;
		}
		if ( ( 3 * $vH ) < 2 ) {
			return ( $v1 + ( $v2 - $v1 ) * ( ( 2 / 3 ) - $vH ) * 6 );
		}
		return $v1;
	}

	public static function hsl2rgb( $h, $s, $l ) {
		if ( $s == 0 ) {
			$r = $l;
			$g = $l;
			$b = $l;
		} else {
			if ( $l < 0.5 ) {
				$var_2 = $l * ( 1 + $s );
			} else {
				$var_2 = ( $l + $s ) - ( $s * $l );
			}
			$var_1 = 2 * $l - $var_2;
			$r     = self::Hue_2_RGB( $var_1, $var_2, $h + ( 1 / 3 ) );
			$g     = self::Hue_2_RGB( $var_1, $var_2, $h );
			$b     = self::Hue_2_RGB( $var_1, $var_2, $h - ( 1 / 3 ) );
		}
		return [
			'R' => round( $r * 255 ), //0~255
			'G' => round( $g * 255 ),
			'B' => round( $b * 255 ),
			'r' => $r, //0~1
			'g' => $g,
			'b' => $b
		];
	}

	public static function rgb2hex( $r, $g, $b ) {
		$hex = [ '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F' ];
		$rh  = "";
		$gh  = "";
		$bh  = "";
		while ( strlen( $rh ) < 2 ) {
			$rh = $hex[ $r % 16 ] . $rh;
			$r  = floor( $r / 16 );
		}
		while ( strlen( $gh ) < 2 ) {
			$gh = $hex[ $g % 16 ] . $gh;
			$g  = floor( $g / 16 );
		}
		while ( strlen( $bh ) < 2 ) {
			$bh = $hex[ $b % 16 ] . $bh;
			$b  = floor( $b / 16 );
		}
		return "#" . $rh . $gh . $bh;
	}

	public static function hexstr2rgb( $hex ) {
		return [
			'R' => hexdec( substr( $hex, 1, 2 ) ), //0~255
			'G' => hexdec( substr( $hex, 3, 2 ) ),
			'B' => hexdec( substr( $hex, 5, 2 ) ),
			'r' => hexdec( substr( $hex, 1, 2 ) ) / 255, //0~1
			'g' => hexdec( substr( $hex, 3, 2 ) ) / 255,
			'b' => hexdec( substr( $hex, 5, 2 ) ) / 255
		];
	}

	public static function rgb2str( $rgb ) {
		return $rgb['R'] . "," . $rgb['G'] . "," . $rgb['B'];
	}

	public static function hex2str( $hex ) {
		return self::rgb2str( self::hexstr2rgb( $hex ) );
	}

	public static function rgb2gray( $R, $G, $B ) {
		return round( $R * 0.299 + $G * 0.587 + $B * 0.114 );
	}

	public static function hex2gray( $hex ) {
		$rgb_array = self::hexstr2rgb( $hex );
		return self::rgb2gray( $rgb_array['R'], $rgb_array['G'], $rgb_array['B'] );
	}

	public static function checkHEX( $hex ) {
		if ( strlen( $hex ) != 7 ) {
			return false;
		}
		if ( substr( $hex, 0, 1 ) != "#" ) {
			return false;
		}
		return true;
	}

	public static function send_mail( $to, $subject, $content ) {
		wp_mail( $to, $subject, $content, [ 'Content-Type: text/html; charset=UTF-8' ] );
	}

	public static function check_email_address( $email ) {
		return (bool) preg_match( "/^\w+((-\w+)|(\.\w+))*@[A-Za-z0-9]+(([.\-])[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/", $email );
	}

	public static function format_number_in_kilos( $num ) {
		if ( $num >= 1000 ) {
			return round( $num / 1000, 1 ) . "k";
		}
		return $num;
	}
}
