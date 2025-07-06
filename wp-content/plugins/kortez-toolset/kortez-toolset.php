<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
/*
Plugin Name: Kortez Toolset
Plugin URI:  
Description: A easy plugin to import dummy data for themes by Kortez Themes.
Version:     1.1.2
Author:      Kortez Themes
Author URI:  https://kortezthemes.com/
License:     GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Domain Path: /languages
Text Domain: kortez-toolset
*/

define( 'KORTEZ_TEMPLATE_URL', plugin_dir_url( __FILE__ ) );
define( 'KORTEZ_TOOLSET_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Returns the currently active theme's name.
 *
 * @since    1.0.0
 */
function kortez_toolset_get_theme_slug(){
    $demo_theme = wp_get_theme();
   	return $demo_theme->get( 'TextDomain' );
}

/**
 * Returns the currently active theme's screenshot.
 *
 * @since    1.0.0
 */
function kortez_toolset_get_theme_screenshot(){
	$demo_theme = wp_get_theme();
    return $demo_theme->get_screenshot();
}
/**
 * The core plugin class that is used to define internationalization,admin-specific hooks, 
 * and public-facing site hooks..
 *
 * @since    1.0.0
 */   
require KORTEZ_TOOLSET_PATH . 'demo/functions.php';

/**
 * Register all of the hooks related to the admin area functionality
 * of the plugin.
 *
 * @since    1.0.0
 */
$plugin_admin = kortez_toolset_hooks();
add_filter( 'advanced_import_demo_lists', array( $plugin_admin,'kortez_toolset_demo_import_lists'), 10, 1 );
add_filter( 'admin_menu', array( $plugin_admin, 'import_menu' ), 10, 1 );
add_filter( 'wp_ajax_kortez_toolset_getting_started', array( $plugin_admin, 'install_advanced_import' ), 10, 1 );
add_filter( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles' ), 10, 1 );
add_filter( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ), 10, 1 );
add_action( 'advanced_import_replace_term_ids', array( $plugin_admin, 'replace_term_ids' ), 20 );
add_action( 'advanced_import_replace_post_ids', array( $plugin_admin, 'replace_attachment_ids' ), 30 );
