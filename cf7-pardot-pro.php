<?php
/*
Plugin Name: Contact Form 7 - Pardot Integration
Description: Contact Form 7 - Pardot Integration plugin allows you to connect WordPress Contact Form 7 and Pardot (Salesforce Pardot).
Version:     3.1.0
Author:      Obtain Code
Author URI:  https://obtaincode.net/
License:     GPL2
Text Domain: cf7_pardot
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit( 'restricted access' );
}

/*
 * This is a constant variable for plugin path.
 */
define( 'CF7_PARDOT_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/*
 * This is a file for includes core functionality.
 */
include_once CF7_PARDOT_PLUGIN_PATH . 'includes/includes.php';

/*
 * This is a function that run when plugin activation.
 */
if ( ! function_exists( 'cf7_pardot_register_activation_hook' ) ) {
    register_activation_hook( __FILE__, 'cf7_pardot_register_activation_hook' );
    function cf7_pardot_register_activation_hook() {
        
        $fields = get_option( 'cf7_pardot_module_fields' );
        if ( ! $fields ) {
            update_option( 'cf7_pardot_module_fields', 'a:28:{s:10:"salutation";a:3:{s:5:"label";s:10:"Salutation";s:4:"type";s:8:"Dropdown";s:8:"required";i:0;}s:10:"first_name";a:3:{s:5:"label";s:10:"First Name";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:9:"last_name";a:3:{s:5:"label";s:9:"Last Name";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:5:"email";a:3:{s:5:"label";s:5:"Email";s:4:"type";s:4:"Text";s:8:"required";i:1;}s:7:"company";a:3:{s:5:"label";s:7:"Company";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:19:"prospect_account_id";a:3:{s:5:"label";s:7:"Account";s:4:"type";s:20:"Integer - Account ID";s:8:"required";i:0;}s:7:"website";a:3:{s:5:"label";s:7:"Website";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:11:"campaign_id";a:3:{s:5:"label";s:8:"Campaign";s:4:"type";s:21:"Integer - Campaign ID";s:8:"required";i:0;}s:9:"job_title";a:3:{s:5:"label";s:9:"Job Title";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:10:"department";a:3:{s:5:"label";s:10:"Department";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:7:"country";a:3:{s:5:"label";s:7:"Country";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:11:"address_one";a:3:{s:5:"label";s:11:"Address One";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:11:"address_two";a:3:{s:5:"label";s:11:"Address Two";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:4:"city";a:3:{s:5:"label";s:4:"City";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:5:"state";a:3:{s:5:"label";s:5:"State";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:9:"territory";a:3:{s:5:"label";s:9:"Territory";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:3:"zip";a:3:{s:5:"label";s:3:"Zip";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:5:"phone";a:3:{s:5:"label";s:5:"Phone";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:3:"fax";a:3:{s:5:"label";s:3:"Fax";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:14:"annual_revenue";a:3:{s:5:"label";s:14:"Annual Revenue";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:9:"employees";a:3:{s:5:"label";s:9:"Employees";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:8:"industry";a:3:{s:5:"label";s:8:"Industry";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:17:"years_in_business";a:3:{s:5:"label";s:17:"Years In Business";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:8:"comments";a:3:{s:5:"label";s:8:"Comments";s:4:"type";s:8:"Textarea";s:8:"required";i:0;}s:5:"notes";a:3:{s:5:"label";s:5:"Notes";s:4:"type";s:8:"Textarea";s:8:"required";i:0;}s:5:"score";a:3:{s:5:"label";s:5:"Score";s:4:"type";s:4:"Text";s:8:"required";i:0;}s:15:"is_do_not_email";a:3:{s:5:"label";s:12:"Do Not Email";s:4:"type";s:7:"boolean";s:8:"required";i:0;}s:14:"is_do_not_call";a:3:{s:5:"label";s:11:"Do Not Call";s:4:"type";s:7:"boolean";s:8:"required";i:0;}}' );
        }
    }
}