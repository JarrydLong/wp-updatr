<?php

class EspressoLicensing{

	function __construct( $api_key = null ){

		$this->api_key = get_option( 'espresso_licensing_api_key');
		$this->api_url = 'https://app.espressolicensing.com/wp-json/espresso-licensing/v1/';
		$this->platform = get_option( 'espresso_licensing_integration' );

	}

	public function validate_client_api_key(){

		$site_url = get_site_url();

		$request = wp_remote_post( $this->api_url.'validate-client', array( 'body' => array(
			'api_key' => $this->api_key,
			'site_url' => $site_url	
		) ) );

		if( !is_wp_error( $request ) ){
			var_dump($request);
			$response = wp_remote_retrieve_body( $request );
			var_dump($response);
			$response = json_decode( $response );

			return $response;

		}

		return false;

	}

	public function get_product_keys(){

		$product_keys = array();

		switch( $this->platform ){
			case 'woocommerce':
				$product_keys = $this->get_products();
				break;
			case 'paid-memberships-pro':
				$product_keys = $this->get_levels();
				break;
			case 'easy-digital-downloads':
				$product_keys = $this->get_downloads();
				break;
		}

		return $product_keys;

	}

	function get_products(){

		$products = array();

		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
		);

		$the_query = new WP_Query( $args );

		if( $the_query->have_posts() ){
			while( $the_query->have_posts() ){
				$the_query->the_post();

				$title = get_the_title();

				$products[] = array(
					'id' => get_the_ID(),
					'name' => $title,
					'slug' => sanitize_title_with_dashes( $title )
				);
			}
		}

		return $products;

	}

	function get_levels(){

		global $wpdb;

		$levels = array();

		$sql = "SELECT * FROM $wpdb->pmpro_membership_levels";
		
		$results = $wpdb->get_results( $sql );
		
		if( !empty( $results ) ){

			foreach( $results as $result ){
				$levels[] = array(
					'id' => $result->id,
					'name' => $result->name,
					'slug' => sanitize_title_with_dashes( $result->name )
				);
			}

		}

		return $levels;

	}

	function get_downloads(){

		$downloads = array();

		$args = array(
			'post_type' => 'downloads',
			'posts_per_page' => -1,
		);

		$the_query = new WP_Query( $args );

		if( $the_query->have_posts() ){
			while( $the_query->have_posts() ){
				$the_query->the_post();

				$title = get_the_title();

				$downloads[] = array(
					'id' => get_the_ID(),
					'name' => $title,
					'slug' => sanitize_title_with_dashes( $title )
				);
			}
		}

		return $downloads;

	}

	public function sync_product_keys(){

		$products = $this->get_product_keys( $this->platform );

		$product_keys = get_option( '_el_'.$this->platform );

		if( !empty( $products ) ){

			foreach( $products as $product ){

				$the_product_key = '';

				$request = $this->request_product( $product );

				if( $request ){

					if( !empty( $request->product_key ) ){						
						$the_product_key = $request->product_key;
					} else {
						$product_key = $this->create_product( $product );
						$the_product_key = $product_key;
					}

				}

				if( empty( $product_keys ) || !$product_keys ){
					$product_keys = array( array( $product['id'] => $the_product_key ) );
				} else {
					$product_keys[] = array( $product['id'] => $the_product_key );
				}

			}

			update_option( '_el_'.$this->platform, $product_keys );

			return $product_keys;

		}
		
	}

	function request_product( $product ){

		$request = wp_remote_post( $this->api_url.'request-product/', array( 'body' => array(
			'api_key' => $this->api_key,			
			'product' => $product
		) ) );

		if( !is_wp_error( $request ) ){

			$response = wp_remote_retrieve_body( $request );

			$response = json_decode( $response );

			return $response;

		}

		return false;

	}

	public function verify_product( $product ){

		$request = wp_remote_post( $this->api_url.'verify-product-key', array( 'body' => array(
			'api_key' => $this->api_key,			
			'product_key' => $product
		) ) );

		if( !is_wp_error( $request ) ){

			$response = wp_remote_retrieve_body( $request );

			$response = json_decode( $response );

			return $response;

		}

		return false;

	}

	function create_product( $product ){

		$request = wp_remote_post( $this->api_url.'create-product/', array( 'body' => array(
			'api_key' => $this->api_key,
			'product' => $product
		) ) );

		if( !is_wp_error( $request ) ){

			$response = wp_remote_retrieve_body( $request );

			$response = json_decode( $response );

			return $response;

		}

		return false;

	}

	/**
	 * Statuses available: active | inactive
	 */
	public function process_purchase( $product_key, $days, $extend, $site_limit, $status = 'active' ){

		$request = wp_remote_post( $this->api_url.'process-purchase', array( 'body' => array(
			'api_key' => $this->api_key,
			'product_key' => $product_key,
			'status' => $status,
			'days' => $days,
			'extend' => $extend,
			'limit' => $site_limit //0 or empty is unlimited
		) ) );

		if( !is_wp_error( $request ) ){

			$response = wp_remote_retrieve_body( $request );

			$response = json_decode( $response );

			return $response;

		}

		return false;

	}

	public function cancel_purchase( $product_key, $license_key ){

		$request = wp_remote_post( $this->api_url.'cancel-purchase', array( 'body' => array(
			'api_key' => $this->api_key,
			'product_key' => $product_key,
			'license_key' => $license_key,
		) ) );

		if( !is_wp_error( $request ) ){

			$response = wp_remote_retrieve_body( $request );

			$response = json_decode( $response );

			return $response;

		}

		return false;

	}

	function get_latest_version( $product_key ){

		$request = wp_remote_post( $this->api_url.'get-version', array( 'body' => array(
			'api_key' => $this->api_key,
			'product_key' => $product_key
		) ) );

		if( !is_wp_error( $request ) ){

			$response = wp_remote_retrieve_body( $request );

			$response = json_decode( $response );

			if( $response ){
				return $response;
			} else {
				return __('Unavailable', 'espresso-licensing');
			}
			

		}

		return false;

	}

}