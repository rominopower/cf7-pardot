<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

/*
 * Deleted options when plugin uninstall.
 */
$uninstall = get_option( 'cf7_pardot_uninstall' );
if ( $uninstall ) {
    delete_option( 'cf7_pardot_user_key' );
    delete_option( 'cf7_pardot_email' );
    delete_option( 'cf7_pardot_password' );
}