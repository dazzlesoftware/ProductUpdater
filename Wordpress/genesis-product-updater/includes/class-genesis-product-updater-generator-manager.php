<?php
/**
 * @package   Genesis Product Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */
/**
 * Registry of all available platform generators.
 *
 * Platforms register themselves by hooking 'product_updater_register_platforms' and
 * adding an instantiated Product_Updater_Platform_Base subclass to the array keyed by
 * slug. This is the single extension point that makes "add a new platform"
 * a matter of adding one class + one line, never editing this file.
 */
class Product_Updater_Generator_Manager {

	private static $instance = null;

	/** @var Product_Updater_Platform_Base[] */
	private $platforms = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * @return Product_Updater_Platform_Base[] slug => generator instance
	 */
	public function get_platforms() {
		if ( null === $this->platforms ) {
			$this->platforms = apply_filters( 'product_updater_register_platforms', array() );
		}
		return $this->platforms;
	}

	public function get_platform( $slug ) {
		$platforms = $this->get_platforms();
		return isset( $platforms[ $slug ] ) ? $platforms[ $slug ] : null;
	}

	public function get_platform_choices() {
		$choices = array();
		foreach ( $this->get_platforms() as $slug => $generator ) {
			$choices[ $slug ] = $generator->get_label();
		}
		return $choices;
	}

	/**
	 * Per-platform data the product editor's JS uses to show/hide and label
	 * the Requires/Tested/Requires PHP fields on each version row.
	 */
	public function get_platform_meta() {
		$meta = array();
		foreach ( $this->get_platforms() as $slug => $generator ) {
			$meta[ $slug ] = array(
				'label'          => $generator->get_label(),
				'supportsCompat' => $generator->supports_compatibility_fields(),
				'supportsMobile' => $generator->supports_mobile_fields(),
			);
		}
		return $meta;
	}
}
