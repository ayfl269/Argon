<?php

namespace ArgonModern;

/**
 * Walker for Author Links in Leftbar
 */
class AuthorLinksWalker extends \Walker_Nav_Menu {
	public function start_el( &$output, $object, $depth = 0, $args = [], $current_object_id = 0 ) {
		if ( $depth == 0 ) {
			$output .= "\n
			<div class='site-author-links-item'>
				<a href='" . $object->url . "' rel='noopener' target='_blank'>" . $object->title . "</a>";
		}
	}

	public function end_el( &$output, $object, $depth = 0, $args = [], $current_object_id = 0 ) {
		if ( $depth == 0 ) {
			$output .= "</div>";
		}
	}
}
