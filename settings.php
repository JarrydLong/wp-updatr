<div class='wrap'>
	<h3><?php _e('Espresso Licencing Settings', 'espresso-licensing'); ?></h3>
	<form method='POST'>
		<table class='form-table striped'>
			<tr>
				<th><?php _e('My API Key', 'espresso-licensing'); ?></th>
				<td>
					<input type='text' name='el_api_key' value='<?php echo get_option( 'espresso_licensing_api_key' ); ?>' style='width: 50%;' /> 
					<p class='description'><?php echo espresso_licensing_validate_api_key() ? __('Your API Key is Valid', 'espresso-licensing') : __('Your API Key is Invalid', 'espresso-licensing'); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php _e('Select An Integration', 'espresso-licensing'); ?></th>
				<?php $integration = get_option( 'espresso_licensing_integration' ); ?>
				<td>
					<p><input type='radio' name='el_integration' value='woocommerce' id='el_woocommerce' <?php checked( 'woocommerce', $integration ); ?> /> <label for='el_woocommerce'><?php _e( 'WooCommerce', 'espresso-licensing' ); ?></label></p>
					<p><input type='radio' name='el_integration' value='paid-memberships-pro' id='el_pmpro' <?php checked( 'paid-memberships-pro', $integration ); ?> /> <label for='el_pmpro'><?php _e( 'Paid Memberships Pro', 'espresso-licensing' ); ?></label></p>
				</td>
			</tr>
			<tr>
				<th></th>
				<td>
					<input type='submit' class='button button-primary' name='el_save_settings' value='<?php _e('Save Settings', 'espresso-licensing'); ?>' />
				</td>
			</tr>
		</table>
	</form>
</div>