<?php
	/**
	 * The public-facing functionality of the plugin.
	 *
	 * @link       https://www.worldtides.info
	 * @package    WorldTides
	 * @subpackage WorldTides/public
	 */
	
	class WorldTides_Public {
		private $plugin_name;
		private $version;
		
		public function __construct($plugin_name, $version ) {
			$this->plugin_name = $plugin_name;
			$this->version     = $version;
		}
		
		public function enqueue_styles() {
			wp_enqueue_style('wpb-google-fonts', '//fonts.googleapis.com/css?family=Open+Sans', FALSE);
			wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/worldtides-public.css', array (), $this->version, 'all');
			wp_enqueue_style('worldtides-icons', plugin_dir_url(__FILE__) . 'font/worldtides-icons/worldtides-icons.css', array (), $this->version, 'all');
			
		}
		
		public function enqueue_scripts() {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/worldtides-public.min.js', array ( 'jquery' ), $this->version, FALSE );			
		}
	}
	
