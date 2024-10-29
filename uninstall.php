<?php
/**
 * @author William Sergio Minossi
 * @copyright 2020
 */
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}
$antibots_option_name[] = 'antibots_is_active';
$antibots_option_name[] = '$antibots_enable_whitelist';
$antibots_option_name[] = '$antibots_my_radio_report_all_visits';
$antibots_option_name[] = 'antibots_keep_data';
$antibots_option_name[] = 'antibots_my_email_to';
$antibots_option_name[] = 'antibots_my_radio_report_all_visits';
$antibots_option_name[] = 'antibots_version';
$antibots_option_name[] = 'antibots_enable_whitelist';
$antibots_option_name[] = 'antibots_installed';
$antibots_option_name[] = 'antibots_was_activated';
for ($i = 0; $i < count($antibots_option_name); $i++)
{
 delete_option( $antibots_option_name[$i] );
 // For site options in Multisite
 delete_site_option( $antibots_option_name[$i] );    
}
// Drop a custom db table
global $wpdb;
$current_table = $wpdb->prefix . 'antibots_visitorslog';
$wpdb->query( "DROP TABLE IF EXISTS $current_table" );
$current_table = $wpdb->prefix . 'antibots_fingerprint';
$wpdb->query( "DROP TABLE IF EXISTS $current_table" );