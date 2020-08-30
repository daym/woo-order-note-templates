<?php
/*
* Plugin Name: Woo Order Note Templates
* Plugin URI: https://wordpress.org/plugins/woo-order-note-templates
* Description: This plugin provides functionality to create woocommerce order notes templates and use these templates to add notes (customer note or private note). The notes can be added customer orders on the main shop order page of woocommerce. 
* Version: 1.3.4
* Author: Priyanka Bhave - Gyrix TechnoLabs
* Author URI: http://gyrix.co/
* Requires at least: 4.1
* Tested up to: 4.9.8
*/

if (!defined('ABSPATH'))

{
    exit; // Exit if accessed directly
}

if(!defined('WONT_GYRIXTEMPLATEPATH'))
{
	define('WONT_GYRIXTEMPLATEPATH', plugin_dir_path(__FILE__));
	define('WONT_GYRIXTEMPLATEURL', plugin_dir_url(__FILE__));
}
include_once(WONT_GYRIXTEMPLATEPATH.'/includes/order-note-manager.php');
function wont_gyrix_order_note_template_run() 
{	
	register_activation_hook( __FILE__, array('wont_gyrix_order_note_manager' , 'wont_gyrix_register_cpt' ) );
	wont_gyrix_order_note_manager::wont_gyrix_register_taxonomy();
	wont_gyrix_order_note_manager::wont_gyrix_register_cpt();
	wont_gyrix_order_note_manager::wont_get_instance();
}

add_action( 'init', 'wont_gyrix_order_note_template_run' );

// to de-activate plugin
function wont_gyrix_plugin_deactivation() 
{
    flush_rewrite_rules(); 
}
register_deactivation_hook( __FILE__, 'wont_gyrix_plugin_deactivation' );

