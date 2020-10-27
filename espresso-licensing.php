<?php
/**
 * Plugin Name: Espresso Licensing
 * Description: Making it easy to launch and support paid WordPress products by integrating with WooCommerce & Paid Memberships Pro to generate API Keys/Product Licenses for your customers.
 * Author: Espress Media Group
 * Author URI: https://espressolicensing.com/
 * Version: 1.0.0
 */

require_once plugin_dir_path( __FILE__ ).'class.espresso-license.php';
require_once plugin_dir_path( __FILE__ ).'compatibility/woocommerce.php';
require_once plugin_dir_path( __FILE__ ).'compatibility/paid-memberships-pro.php';

function espresso_licensing_admin_menu(){

	add_menu_page( __( 'Espresso Licensing', 'licencing' ), __( 'Espresso Licensing', 'licencing' ), 'manage_options', 'espresso-licensing', 'espresso_licensing_menu_content' );

}
add_action( 'admin_menu', 'espresso_licensing_admin_menu' );

function espresso_licensing_menu_content(){

	require_once plugin_dir_path( __FILE__ ).'settings.php';

}

function espresso_licensing_validate_api_key(){

	$espresso = new EspressoLicensing();

	$valid = $espresso->validate_client_api_key();

	return $valid;

}

function espresso_licensing_save_settings(){

	if( isset( $_POST['el_save_settings'] ) ){

		$api_key = isset( $_POST['el_api_key'] ) ? $_POST['el_api_key'] : '';
		update_option( 'espresso_licensing_api_key', $api_key );

		$integration = isset( $_POST['el_integration'] ) ? $_POST['el_integration'] : '';
		update_option( 'espresso_licensing_integration', $integration );

	}

}
add_action( 'admin_init', 'espresso_licensing_save_settings' );