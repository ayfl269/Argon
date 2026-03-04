<?php

namespace ArgonModern;

/**
 * Walker for Top Toolbar Menu
 */
class ToolbarMenuWalker extends \Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = [] ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent<div class=\"dropdown-menu\">\n";
	}
	public function end_lvl( &$output, $depth = 0, $args = [] ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent</div>\n";
	}
	public function start_el( &$output, $object, $depth = 0, $args = [], $current_object_id = 0 ) {
		if ( $depth == 0 ) {
			if ( isset( $args->walker->has_children ) && $args->walker->has_children == 1 ) {
				$output .= "\n
				<li class='nav-item dropdown'>
					<a href='" . $object->url . "' class='nav-link' data-toggle='dropdown' no-pjax onclick='return false;' title='" . $object->description . "'>
						<i class='ni ni-book-bookmark d-lg-none'></i>
						<span class='nav-link-inner--text'>" . $object->title . "</span>
				  </a>";
			} else {
				$output .= "\n
				<li class='nav-item'>
					<a href='" . $object->url . "' class='nav-link' target='" . $object->target . "' title='" . $object->description . "'>
						<i class='ni ni-book-bookmark d-lg-none'></i>
						<span class='nav-link-inner--text'>" . $object->title . "</span>
				  </a>";
			}
		} else if ( $depth == 1 ) {
			$output .= "<a href='" . $object->url . "' class='dropdown-item' target='" . $object->target . "' title='" . $object->description . "'>" . $object->title . "</a>";
		}
	}
	public function end_el( &$output, $object, $depth = 0, $args = [], $current_object_id = 0 ) {
		if ( $depth == 0 ) {
			$output .= "\n</li>";
		}
	}
}
