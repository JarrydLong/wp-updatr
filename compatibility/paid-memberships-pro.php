<?php
function espresso_licensing_pmpro_level_settings() {

	$level_id = 0;

	if( !empty( $_REQUEST['edit'] ) ){
		if( $_REQUEST['edit'] < 0 ){
			//New level
		} else {
			$level_options = get_option( 'espresso_licensing_levels_'.intval( $_REQUEST['edit'] ) );
		}
	}

	$verify_text = '';

	if( isset( $level_options['key'] ) ){

		$product_key = $level_options['key'];

		$espresso = new EspressoLicensing();

		if( $espresso->verify_product( $product_key ) ){
			$verify_text = __('Valid', 'espresso-licensing');
		} else {
			$verify_text = __('Invalid', 'espresso-licensing');
		}

	}

	?>
	<hr />
	<h3><?php esc_html_e( 'Espresso Licensing Settings', 'espresso-licensing' ); ?></h3>
	<p class="description">
		<?php
			$espress_allowed_link = array(
				'a' => array (
					'href' => array(),
					'target' => array(),
					'title' => array(),
				),
			);
			echo sprintf( wp_kses( __( 'Link your Paid Memberships Pro levels to your Espresso Licensing products. Alternatively, <a href="%s" title="Register Now" target="_blank">Register for a Free Trial today</a>.', 'espresso-licensing' ), $espress_allowed_link ), 'https://espressolicensing.com/?utm_source=plugin&utm_medium=pmpro-membershiplevels&utm_campaign=add-ons&utm_content=pmpro-roles' );
		?>
	</p>
	<table class="form-table">
		<tbody>
			<tr>
				<th><?php echo __('Product Key', 'espresso-licensing') .' - '.$verify_text; ?></th>
				<td>
					<input type='text' name='espresso_product_key' value='<?php if( isset( $level_options['key'] ) ){ echo $level_options['key']; } ?>' class='regular_text'/>
					<p class="description"><?php _e('Login to your Espresso Licensing account and navigate to "Products" to obtain a product key.', 'espresso-licensing'); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php _e('Site Limit', 'espresso-licensing'); ?></th>
				<td>
					<input type='text' name='espresso_licence_limit' value='<?php if( isset( $level_options['limit'] ) ){ echo $level_options['limit']; } ?>' class='regular_text'/>
					<p class="description"><?php _e('The maximum number of sites allowed to use a licence key. Leave empty or set to 0 for unlimited.', 'espresso-licnesing'); ?></p>
			</td>
			</tr>
		</tbody>
	</table>
	<?php
}
add_action( 'pmpro_membership_level_after_other_settings', 'espresso_licensing_pmpro_level_settings', 10 );

function espresso_licensing_pmpro_edit_level( $saveid ){

	if( isset( $_REQUEST['espresso_product_key'] ) ){

		$license_key = sanitize_text_field( $_REQUEST['espresso_product_key'] );
		$site_limit = intval( $_REQUEST['espresso_licence_limit'] );

		update_option( 'espresso_licensing_levels_'.$saveid, array( 'key' => $license_key, 'limit' => $site_limit ) );
	}
}
add_action( 'pmpro_save_membership_level', 'espresso_licensing_pmpro_edit_level', 10, 1 );

function espresso_licensing_pmpro_delete_level( $delete_id ){

	delete_option( 'espresso_licensing_levels_'.$saveid );

}
add_action( 'pmpro_delete_membership_level', 'espresso_licensing_pmpro_delete_level', 10, 1 );

function espresso_licensing_pmpro_after_checkout( $user_id, $morder ){

	espresso_licensing_pmpro_setup_api_keys( $morder );

}
add_action( 'pmpro_after_checkout', 'espresso_licensing_pmpro_after_checkout', 10, 2 );

function espresso_licensing_pmpro_renewals( $morder ){

	espresso_licensing_pmpro_setup_api_keys( $morder, true );

}
add_action( 'pmpro_subscription_payment_completed', 'espresso_licensing_pmpro_renewals', 10, 1 );

function espresso_licensing_pmpro_fails( $morder ){

	global $wpdb;

	$sql = "SELECT * FROM $wpdb->pmpro_membership_orders WHERE membership_id = '".$morder->membership_id."' AND user_id = '".$morder->user_id."' ORDER BY id DESC";

	$recent_transactions = $wpdb->get_results( $sql );

	$membership_id = $morder->membership_id;

	$level_options = get_option( 'espresso_licensing_levels_'.$morder->membership_id );

	$espresso = new EspressoLicensing();

	if( !empty( $level_options ) ){

		foreach( $recent_transactions as $recent ){

			$license_key = espresso_licensing_pmpro_get_license_key( $recent->notes );

			if( $license_key ){

				$api_key = $espresso->cancel_purchase( $level_options['key'], $license_key );

			}
		}

	}

}
add_action( 'pmpro_subscription_payment_failed', 'espresso_licensing_pmpro_fails', 10, 1 );
add_action( 'pmpro_stripe_subscription_deleted', 'espresso_licensing_pmpro_fails', 10, 1 );

function espresso_licensing_pmpro_setup_api_keys( $morder, $renewals = false ){

	global $wpdb;

	$api_keys = array();	

	$level_id = $morder->membership_id;

	$level_options = get_option( 'espresso_licensing_levels_'.$level_id );

    $product_key = isset( $level_options['key'] ) ? $level_options['key'] : '';

    $frequency = $morder->BillingFrequency;
    $period = $morder->BillingPeriod;

    $multiplier = 1;

    switch( $period ){
    	case 'Day':
    		$multiplier = 1;
    		break;
		case 'Week': 
			$multiplier = 7;
			break;
		case 'Month': 
			$multiplier = 30;
			break;
		case 'Year': 
			$multiplier = 365;
			break;
    }
    
    $lifespan_days = $multiplier * $frequency;

    $site_limit = isset( $level_options['limit'] ) ? intval( $level_options['limit'] ) : 0;

   	if( !empty( $product_key ) ){

   		$espresso = new EspressoLicensing();

        $api_key = $espresso->process_purchase( $product_key, $lifespan_days, $renewals, $site_limit, 'active' );

        $notes = "";
		$notes .= "\n---\n";
		$notes .= "{LICENSE_KEY:" . $api_key . "}\n";
		$notes .= "---\n";

		$morder->notes .= $notes;

		$sqlQuery = "UPDATE $wpdb->pmpro_membership_orders SET notes = '" . esc_sql( $morder->notes ) . "' WHERE id = '" . intval( $morder->id ) . "' LIMIT 1";
		
		$wpdb->query($sqlQuery);

	}	

}

function espresso_licensing_pmpro_get_license_key( $order_notes ){

	$value = pmpro_getMatches( "/{LICENSE_KEY:([^}]*)}/", $order_notes, true );
	
	return $value;

}

function espresso_licensing_pmpro_display_confirmation( $morder ){
	?>
	<li><strong><?php _e('API Key', 'paid-memberships-pro' );?>:</strong> <?php echo espresso_licensing_pmpro_get_license_key( $morder->notes ); ?></li>
	<?php
}
add_action( 'pmpro_invoice_bullets_bottom', 'espresso_licensing_pmpro_display_confirmation', 10, 1 );