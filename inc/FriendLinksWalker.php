<?php

namespace ArgonModern;

/**
 * Walker for Friend Links in Leftbar
 */
class FriendLinksWalker extends \Walker_Nav_Menu {
	public function start_el( &$output, $object, $depth = 0, $args = [], $current_object_id = 0 ) {
		if ( $depth == 0 ) {
			$output .= "\n
			<li class='site-friend-links-item'>
				<a href='" . $object->url . "' rel='noopener' target='_blank'>" . $object->title . "</a>";
		}
	}

	public function end_el( &$output, $object, $depth = 0, $args = [], $current_object_id = 0 ) {
		if ( $depth == 0 ) {
			$output .= "</li>";
		}
	}
}
