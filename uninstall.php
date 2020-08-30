<?php

/*
Uninstall plugin
*/

// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

uninstall_wont_gyrix_data();
function uninstall_wont_gyrix_data()
{
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'wont_gyrix_note_template';" );
}

wont_gyrix_remove_script();
function wont_gyrix_remove_script()
{
	wp_dequeue_script('wont_templatejs');
	wp_deregister_style('wont_templatecss');
}
