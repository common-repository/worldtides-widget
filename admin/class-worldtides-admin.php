<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.worldtides.info
 * @package    WorldTides
 * @subpackage WorldTides/admin
 */

class WorldTides_Admin {
	private $plugin_name;
	private $version;
	public function __construct($plugin_name, $version) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}
	
	// Register the stylesheets for the admin area.
	public function enqueue_styles() {
		wp_enqueue_style('wp-color-picker');
	}
	
	// Register the JavaScript for the admin area.
	public function enqueue_scripts() {
		wp_enqueue_script('jquery-ui-autocomplete');
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script('wp-color-picker-alpha', plugin_dir_url(__FILE__).'js/wp-color-picker-alpha.min.js', array('wp-color-picker'), '2.1.2', FALSE);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__).'js/worldtides-admin.min.js', array('jquery'), $this->version, FALSE);
	}
	
	static function function_worldtides_plugin_action_links($links) {
		$links_prefix = array (
		);
		
		$links_suffix = array (
			"<a href='https://wordpress.org/support/plugin/worldtides' target='_blank'> Support</a>",
			"<a href='https://wordpress.org/support/plugin/worldtides/reviews/' target='_blank'> Reviews</a>"
		);
		
		return array_merge($links_prefix, $links, $links_suffix);
	}
}

if ((empty(get_option('worldtides_version'))) OR (version_compare(get_option('worldtides_version'), $this->version) == -1)) {
	update_option('worldtides_version', $this->version);
}

