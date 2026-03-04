<?php

namespace ArgonModern;

trait Singleton {
	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->setup();
	}

	abstract protected function setup();
}
