<?php
/**
 * EMC Offers simulator
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

if ( ! class_exists( 'emc_settings_simulator' ) ) :

/**
 * emc_settings_simulator
 */
class emc_settings_simulator extends WC_Settings_Page {

  /**
   * Constructor.
   */
  public function __construct() {

    $this->id    = 'simulator';
    $this->label = __( 'Offers simulator', 'envoimoinscher' );
    
    add_filter( 'emc_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
    add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
  }
	
	/**
	* Get fields array. We're not calling it get_settings to prevent create_options from creating empty options and not using default value.
	*
	* @return array
	*/
	public function get_fields() {
		$products = array();
		$args     = array( 'post_type' => 'product' );
		foreach(get_posts( $args ) as $value) {
			$product  = wc_get_product( $value->ID );
			if( $product->product_type == 'simple' ) {
				$products[$value->ID] = $value->post_title;
			} elseif ( $product->product_type == 'variable' ) {
				foreach($product->get_available_variations() as $variation) {
					$title = $value->post_title;
					foreach($variation['attributes'] as $attributes){
						$title .= ' ' . $attributes;
					}
					$products[$variation['variation_id']] = $title;
				}
			}
		}

		if( empty($products) ) {
			$products[''] = __( 'No product available in your online shop', 'envoimoinscher' );
		}

		$display_countries =  WC()->countries->get_countries();

		$fields = array(

				array( 'title' => __( 'EnvoiMoinsCher: shipping cost estimation', 'envoimoinscher' ), 'type' => 'title', 'desc' => __( 'The simulation page allows you to make a quotation according to the characteristics of your parcel.<br />Prices and offers are displayed as seen by the client.', 'envoimoinscher' ), 'id' => 'api_simulator_options' ),

				array(
					'title'    => __( 'Choose a product *', 'envoimoinscher' ),
					'id'       => 'product_simulator',
					'type'     => 'select',
					'default'  => isset($_POST['product_simulator']) ? $_POST['product_simulator'] : '',
					'options'  => $products,
					'required' => true,
					'css'      => 'min-width:350px;',
				),

				array(
					'title'    => __( 'Sender address', 'envoimoinscher' ),
					'id'       => 'sender_address',
					'type'     => 'text',
					'default'  => isset($_POST['sender_address']) ? $_POST['sender_address'] : get_option('EMC_ADDRESS', ''),
					'required' => false,
					'css'      => 'min-width:350px;',
				),
				
				array(
					'title'    => __( 'Sender ZIP code *', 'envoimoinscher' ),
					'id'       => 'sender_postcode',
					'type'     => 'text',
					'default'  => isset($_POST['sender_postcode']) ? $_POST['sender_postcode'] : get_option('EMC_POSTALCODE', ''),
					'required' => true,
				),        

				array(
					'title'    => __( 'Sender city *', 'envoimoinscher' ),
					'id'       => 'sender_city',
					'type'     => 'text',
					'default'  => isset($_POST['sender_city']) ? $_POST['sender_city'] : get_option('EMC_CITY', ''),
					'required' => true,
				),

				array(
					'title'    => __( 'Recipient address', 'envoimoinscher' ),
					'id'       => 'recipient_address',
					'type'     => 'text',
					'default'  => isset($_POST['recipient_address']) ? $_POST['recipient_address'] : '',
					'required' => false,
					'css'      => 'min-width:350px;',
				),

				array(
					'title'    => __( 'Recipient ZIP code *', 'envoimoinscher' ),
					'id'       => 'recipient_postcode',
					'type'     => 'text',
					'default'  => isset($_POST['recipient_postcode']) ? $_POST['recipient_postcode'] : '',
					'required' => true,
				),
				
				array(
					'title'    => __( 'Recipient city *', 'envoimoinscher' ),
					'id'       => 'recipient_city',
					'type'     => 'text',
					'default'  => isset($_POST['recipient_city']) ? $_POST['recipient_city'] : '',
					'required' => true,
				),

				array(
					'title'    => __( 'Recipient country *', 'envoimoinscher' ),
					'id'       => 'recipient_country',
					'css'      => 'min-width:350px;',
					'type'     => 'select',
					'default'  => isset($_POST['recipient_country']) ? $_POST['recipient_country'] : 'FR',
					'required' => true,
					'options'  => $display_countries,
				),

				array( 'type' => 'sectionend', 'id' => 'api_simulator_options'),

			);

			return $fields;       
	}
	
	/**
	 * Output the settings
	 */
	public function output() {
		$fields = $this->get_fields();

		WC_Admin_Settings::output_fields( $fields );
	}

	/**
	* Output query results
	*/
	public function additionalOutput() {
		if( isset( $_POST ) && !empty( $_POST ) ){
			if( $this->check_fields() ){       
				$offers = $this->get_simulator_offers();
				if( is_array( $offers ) ){
					return $offers;
				} else {
					// if no offers or curl error or request error
					emc_admin_settings::add_error( sprintf(__( 'EnvoiMoinsCher API error returned: %s', 'envoimoinscher' ), $offers ) );
					emc_admin_settings::show_messages();
					return  false;
				}
			}
		}
	}

	/**
	* Check empty values from fields
	*/
	public function check_fields() { 
		
		$settings = $this->get_settings();
		
		foreach ( $settings as $value ) {
			if( isset( $value['required'] ) && ( $value['required'] == true ) && ( null == $_POST[$value['id']] ) ) {
				emc_admin_settings::add_error( __( 'Please fill in all the required * fields.', 'envoimoinscher' ) );
				emc_admin_settings::show_messages();
				return false;
			}
		}
		return  true;
	}
	
	/**
	* Calculate Shipping
	*/
	public function get_simulator_offers() {
		if ( isset( $_POST['product_simulator'] ) )  {
			
			$product_id   = $_POST['product_simulator'];

			$from = array(
				'pays'      	=> 'FR',
				'code_postal' => $_POST['sender_postcode'],
				'ville'       => $_POST['sender_city'],
				'type'				=> 'entreprise',
				'societe'     => 'Société expéditrice',
				'adresse'     => $_POST['sender_address'],
				'civilite'    => 'M',
				'prenom'    	=> 'Henri',
				'nom'     		=> 'Dubois',
				'email'       => 'henri@dubois.com',
				'tel'        	=> '0102030405',
				'infos'       => 'vide',
			);
			
			$to = array(
				'pays'  			=> $_POST['recipient_country'],
				'code_postal' => $_POST['recipient_postcode'],
				'ville'      	=> $_POST['recipient_city'],
				'type'				=> 'particulier',
				'adresse'   	=> $_POST['recipient_address'],
				'prenom' 			=> 'Hervé',
				'nom'  				=> 'Dupont',
				'email'     	=> 'herve@dupont.com',
				'tel'					=> '0504030201',
				'infos'      	=> 'vide',
			);
			
			$weight = envoimoinscher_model::get_product_weight($product_id);
			if ( !empty($weight) ) {
				$dims = envoimoinscher_model::get_dim_from_weight($weight);
			
				$parcels = array(
					1 => array(
						'poids' 		=> $weight,
						'longueur' 	=> $dims->length, 
						'largeur' 	=> $dims->width, 
						'hauteur' 	=> $dims->height,
					)
				);
			}
			else{
				$parcels = array(
					1 => array(
						'poids' 		=> '',
						'longueur' 	=> 18, 
						'largeur' 	=> 18, 
						'hauteur' 	=> 18,
					)
				);
			}
			
			$params = array(
				'collecte' 			=> date('Y-m-d'),
				'delai' 				=> 'aucun',
				'code_contenu' 	=> get_option('EMC_NATURE'),
				'colis.valeur' 	=> get_post_meta( $product_id, '_price', true),
				'module'        => EMC_PLATFORM,
        'version'       => WC_VERSION,
			);
			
			return envoimoinscher_model::get_quotation($from, $to, $parcels, $params, true ,false);
		}
	}  
}

endif;
