<div class='wrap'>
	<h3><?php _e('WP Updatr Site Settings', 'wp-updatr'); ?></h3>
	<form method='POST'>
		<table class='form-table striped'>
			<tr>
				<th><?php _e('API Key', 'wp-updatr'); ?></th>
				<td>
					<input type='text' name='wpur_api_key' value='<?php echo get_option( 'wp_updatr_api_key' ); ?>' style='width: 50%;' /> 
					<p class='description'><?php echo wp_updatr_validate_api_key() ? __('Your API Key is Valid', 'wp-updatr') : __('Your API Key is Invalid', 'wp-updatr'); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php _e('Select An Integration', 'wp-updatr'); ?></th>
				<?php $integration = get_option( 'wp_updatr_integration' ); ?>
				<td>
					<p><input type='radio' name='wpur_integration' value='woocommerce' id='wpur_woocommerce' <?php checked( 'woocommerce', $integration ); ?> /> <label for='wpur_woocommerce'><?php _e( 'WooCommerce', 'wp-updatr' ); ?></label></p>
					<p><input type='radio' name='wpur_integration' value='paid-memberships-pro' id='wpur_pmpro' <?php checked( 'paid-memberships-pro', $integration ); ?> /> <label for='wpur_pmpro'><?php _e( 'Paid Memberships Pro', 'wp-updatr' ); ?></label></p>
					<p><input type='radio' name='wpur_integration' value='easy-digital-downloads' id='wpur_pmpro' <?php checked( 'easy-digital-downloads', $integration ); ?> /> <label for='wpur_pmpro'><?php _e( 'Easy Digital Downloads', 'wp-updatr' ); ?></label></p>
				</td>
			</tr>
			<tr>
				<th></th>
				<td>
					<input type='submit' class='button button-primary' name='wpur_save_settings' value='<?php _e('Save Settings', 'wp-updatr'); ?>' />
				</td>
			</tr>
		</table>
	</form>
	<hr/>
	<p class='description'>
		WP Updatr | 
		<a href='' target='_BLANK'><?php _e('Documentation', 'wp-updatr'); ?></a> | 
		<a href='' target='_BLANK'><?php _e('Sign Up For Our Unlimited Plan', 'wp-updatr'); ?></a>
	</p>
</div>
