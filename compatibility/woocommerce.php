<?php

function licensing_woo_create_activation( $order_id ){

	$espresso = new EspressoLicensing();
	
	$order = new WC_Order( $order_id );

	$api_keys = array();

 	foreach ( $order->get_items() as $item_id => $product_item ) {

        $product_id = $product_item->get_product_id();

        $product_key = get_post_meta( $product_id, 'espresso_product_key', true );

       	if( !empty( $product_key ) ){

       		$lifespan_days = get_post_meta( $product_id, 'espresso_licence_lifespan', true );

       		$site_limit = get_post_meta( $product_id, 'espresso_licence_limit', true );

	        $api_key = $espresso->process_purchase( $product_key, $lifespan_days, false, $site_limit, 'active' );

	        if( $api_key ){
				$api_keys[$product_id] = $api_key;
			}

		}

    }

	update_post_meta( $order_id, '_espresso_licensing_api_keys', $api_keys );

}
add_action( 'woocommerce_order_status_completed', 'licensing_woo_create_activation', 10, 1 );

// function licensing_woo_pending_activation( $order_id ){

// 	//Leaving this here but we don't need to do anything with this for now

// }
// add_action( 'woocommerce_order_status_pending', 'licensing_woo_pending_activation', 10, 1 );
// add_action( 'woocommerce_order_status_on-hold', 'licensing_woo_pending_activation', 10, 1 );
// add_action( 'woocommerce_order_status_processing', 'licensing_woo_pending_activation', 10, 1 );

function licensing_woo_cancel_activation( $order_id ){

	$api_keys = get_post_meta( $order_id, '_espresso_licensing_api_keys', true );

	$espresso = new EspressoLicensing();
	
	$order = new WC_Order( $order_id );

	$api_keys = array();

 	foreach ( $order->get_items() as $item_id => $product_item ) {

        $product_id = $product_item->get_product_id();

        $product_key = get_post_meta( $product_id, 'espresso_product_key', true );

       	if( !empty( $product_key ) ){

       		$license_key = isset( $api_keys[$product_id] ) ? $api_keys[$product_id] : '';

	        $api_key = $espresso->cancel_purchase( $product_key, $license_key );	     

		}

    }

}
add_action( 'woocommerce_order_status_failed', 'licensing_woo_cancel_activation', 10, 1 );
add_action( 'woocommerce_order_status_refunded', 'licensing_woo_cancel_activation', 10, 1 );
add_action( 'woocommerce_order_status_cancelled', 'licensing_woo_cancel_activation', 10, 1 );

function add_woocommerce_account_downloads_columns( $actions ) {

  	$actions['els_api_key'] = __('API Key', 'espresso-licensing');
  	$actions['els_version'] = __('Version', 'espresso-licensing');

    return $actions;
}
add_filter( 'woocommerce_account_downloads_columns', 'add_woocommerce_account_downloads_columns', 10, 2 );

function espresso_downloads_api_key( $download ){

	$product_id = $download['product_id'];
	
	$api_keys = get_post_meta( $download['order_id'], '_espresso_licensing_api_keys', true );

	if( isset( $api_keys[$download['product_id']] ) ){
		echo "<input type='text' readonly value='".$api_keys[$download['product_id']]."' />";
	}

}
add_action( 'woocommerce_account_downloads_column_els_api_key', 'espresso_downloads_api_key', 10, 1 );

function espresso_downloads_version( $download ){

	$product_id = $download['product_id'];
	
	$api_keys = get_post_meta( $download['order_id'], '_espresso_licensing_api_keys', true );

	if( isset( $api_keys[$download['product_id']] ) ){
		$espresso = new EspressoLicensing();

		$version = $espresso->get_latest_version( $api_keys[$download['product_id']] );

		echo $version;
	}

}
add_action( 'woocommerce_account_downloads_column_els_version', 'espresso_downloads_version', 10, 1 );

function misha_adv_product_options(){
 	
 	global $post;

	echo '<div class="options_group">';
 	
 	$valid_key = get_post_meta( $post->ID, 'espresso_product_key_valid', true );

 	if( $valid_key ){
 		$status = __('Valid', 'espresso-licensing');
 	} else {
		$status = __('Invalid', 'espresso-licensing');
 	}

	woocommerce_wp_text_input( array(
		'id'      => 'espresso_product_key',
		'value'   => get_post_meta( get_the_ID(), 'espresso_product_key', true ),
		'label'   => 'Product Key - '.$status,
		'desc_tip' => true,
		'description' => 'Login to your Espresso Licensing account and navigate to "Products" to obtain a product key.',
	) );

	woocommerce_wp_text_input( array(
		'id'      => 'espresso_licence_lifespan',
		'value'   => get_post_meta( get_the_ID(), 'espresso_licence_lifespan', true ),
		'label'   => 'License Lifespan',
		'desc_tip' => true,
		'description' => 'How long will a license key be valid for. Specify in days only.',
	) );

	woocommerce_wp_text_input( array(
		'id'      => 'espresso_licence_limit',
		'value'   => get_post_meta( get_the_ID(), 'espresso_licence_limit', true ),
		'label'   => 'Site Usage Limit',
		'desc_tip' => true,
		'description' => 'The maximum number of sites allowed to use a licence key. Leave empty or set to 0 for unlimited.',
	) );
 
	echo '</div>';
 
}
add_action( 'woocommerce_product_options_general_product_data', 'misha_adv_product_options');

function espresso_licensing_wp_save_fields( $id, $post ){

	if( !empty( $_POST['espresso_product_key'] ) ) {

		$product_key = sanitize_text_field( $_POST['espresso_product_key'] );

		update_post_meta( $id, 'espresso_product_key', $product_key );

		$espresso = new EspressoLicensing();

		$verify = $espresso->verify_product( $product_key );

		if( $verify ){
			update_post_meta( $id, 'espresso_product_key_valid', true );
		} else {
			update_post_meta( $id, 'espresso_product_key_valid', false );
		}

	} else {
		delete_post_meta( $id, 'espresso_product_key' );
	}
 
}
add_action( 'woocommerce_process_product_meta', 'espresso_licensing_wp_save_fields', 10, 2 );