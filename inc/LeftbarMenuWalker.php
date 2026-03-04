<?php

namespace ArgonModern;

/**
 * Walker for Leftbar Menu
 */
class LeftbarMenuWalker extends \Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = [] ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent<ul class=\"leftbar-menu-item leftbar-menu-subitem shadow-sm\">\n";
	}

	public function end_lvl( &$output, $depth = 0, $args = [] ) {
		$indent = str_repeat( "\t", $depth );
		$output .= "\n$indent</ul>\n";
	}

	public function start_el( &$output, $object, $depth = 0, $args = [], $current_object_id = 0 ) {
		$output .= "\n
		<li class='leftbar-menu-item" . ( $args->walker->has_children == 1 ? " leftbar-menu-item-haschildren" : "" ) . ( $object->current == 1 ? " current" : "" ) . "'>
			<a href='" . $object->url . "'" . ( $args->walker->has_children == 1 ? " no-pjax onclick='return false;'" : "" ) . " target='" . $object->target . "'>" . $object->title . "</a>";
	}

	public function end_el( &$output, $object, $depth = 0, $args = [], $current_object_id = 0 ) {
		$output .= "</li>";
	}
}
