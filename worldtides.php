<?php

require plugin_dir_path(__FILE__).'includes/class-worldtides.php';

/**
 * @link              https://www.worldtides.info
 * @package           WorldTides
 * @wordpress-plugin
 * Plugin Name:       WorldTides Widget
 * Plugin URI:        https://wordpress.org/plugins/worldtides/
 * Description:       Customizable simple Tide Prediciton / Responsive web design
 * Version:           1.3.0
 * Author:            Brainware LLC
 * Author URI:        https://www.brainware.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       worldtides
 */

if (!defined('WPINC')) {
	die;
}

$plugin = new WorldTides();
register_activation_hook( __FILE__, ['WorldTides', 'activate']);
register_deactivation_hook( __FILE__, ['WorldTides', 'deactivate']);
$plugin->run();

?>