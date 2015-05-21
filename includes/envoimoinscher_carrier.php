<?php

if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}

/**
 * Standard EnvoiMoinsCher carrier class.
 */

class envoimoinscher_carrier extends WC_Shipping_Method {
	
	/** @var string envoimoinscher operator code */	
	public $ope_code;
	
	/** @var string envoimoinscher service code */
	public $srv_code;
	
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function  __construct() {				
		
		$this->id = self::getCalledClass();
		
		$service = envoimoinscher_model::get_service_by_carrier_code($this->id);
		$this->ope_code = $service->ope_code;
		$this->srv_code = $service->srv_code;
		
		$this->title = $service->ope_name. ' (' . $service->srv_name_bo . ')';
		$this->method_title = $service->ope_name. ' (' . $service->srv_name_bo . ')';
	
		$this->init();
	}

	/**
	 * init function.
	 *
	 * @access public
	 * @return void
	 */
	public function init() {

		// Load the settings API
		$this->init_settings(); // This is part of the settings API. Loads settings you previously init.
		$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
		// Add rate calculation
		add_filter('woocommerce_calculated_shipping',array(&$this, 'calculate_shipping'));
		
		// Process settings
		add_action('woocommerce_update_options_shipping_'.$this->id, array(&$this, 'process_admin_options'));
	}
	/**
	 * update carrier options (scale options)
	 * @access public
	 * @return void
	 * 
	 */
	
	public function process_admin_options(){
		parent::process_admin_options();
		if( isset($_POST['rate_from']) ){
			$params = array();
			$params['shipping_method']	= 	$_POST['rate_carrier'];
			$params['type']							= 	$_POST['rate_type'];
			$params['rate_from'] 				= 	$_POST['rate_from'];
			$params['rate_to'] 					= 	$_POST['rate_to'];
			$params['rate_price'] 			= 	$_POST['rate_price'];
			envoimoinscher_model::save_scale_data($params);
		}
	}
	
	/**
	 * init_form_fields function.
	 *
	 * @access public
	 * @return void
	 */
	public function init_form_fields() {

		$service = envoimoinscher_model::get_service_by_carrier_code($this->id);
		$carrier_settings = get_option('woocommerce_'.$this->id.'_settings');
		
		if( $service->srv_dropoff_point == 1 ) {
			$default_PP = '';
			$type_PP = 'text';
		}
		else{
			$default_PP = 'POST';
			$type_PP = 'hidden';
		}
		
		if( get_option('EMC_POSTALCODE') && get_option('EMC_CITY') ) {
			$PP_link = '//www.envoimoinscher.com/choix-relais.html?cp='.get_option('EMC_POSTALCODE').'&ville='.get_option('EMC_CITY').'&country=FR&srv='.$service->srv_code.'&ope='.$service->ope_code;
		}
		else{
			$PP_link = '//www.envoimoinscher.com/choix-relais.html?srv='.$service->srv_code.'&ope='.$service->ope_code;
		}
		
		$default_pricing = isset($carrier_settings['pricing']) ? $carrier_settings['pricing'] : 1;
		
		$this->form_fields = array(
			'enabled' => array(
				'title' => __( 'Enable/Disable', 'envoimoinscher' ),
				'type' => 'checkbox',
				'default' => 'yes',
			),
			'srv_name' => array(
				'title' => __( 'Label', 'envoimoinscher' ),
				'type' => 'text',
				'description' => __( 'This controls the label which the user sees during checkout.', 'envoimoinscher' ),
				'default' => $service->srv_name,
			),
			'srv_description' => array(
				'title' => __( 'Description', 'envoimoinscher' ),
				'type' => 'text',
				'description' => __( 'This controls the description which the user sees during checkout.', 'envoimoinscher' ),
				'default' => $service->srv_description,
			),
			'pricing' => array(
				'title' => __( 'Prices', 'envoimoinscher' ),
				'type' => 'radio',
				'default' => $default_pricing,
				'options'  => array( 1 => __( 'Real price', 'envoimoinscher' ), 2 => __( 'Rate', 'envoimoinscher' )),
				'css' => '-webkit-margin-before:0.2em;',
				'class' => 'pricing'
			),
			'rate_type' => array(
				'description' => __( 'Define the type of rate to make your scales : weight for total cart weight and price for total cart price', 'envoimoinscher' ),
				'type' => 'radio',
				'default' => 'weight',
				'options'  => array( 'weight' => __( 'Weight', 'envoimoinscher' ), 'price' => __( 'Price', 'envoimoinscher' )),
				'css' => '-webkit-margin-before:0.2em;',
				'class' => 'rate-type'
			),
			'default_dropoff_point' => array(
				'title' => __( 'Dropoff relay point', 'envoimoinscher' ),
				'description' => '<a href="'.$PP_link.'" target="_blank">'.__( 'Get code', 'envoimoinscher' ).'</a>',
				'type' => $type_PP,
				'default' => $default_PP,
			),
			'carrier_tracking_url' => array(
				'title' => __( 'Carrier tracking url', 'envoimoinscher' ),
				'description' => __( 'Use this field to add a carrier tracking URL to your customer order view page. If you use @ it will be replaced by the carrier tracking reference to get a direct URL to your order tracking. Leave blank if you don\'t want to display a tracking link.', 'envoimoinscher' ),
				'type' => 'text',
				'default' => '',
			),
		);

	}
	
	/*
	 * Fix for PHP 5.2
	 */
	public static function getCalledClass(){
		
		if ( function_exists('get_called_class') ) {
			return get_called_class();
		}
		else {
			$arr = array(); 
			$arrTraces = debug_backtrace();
			foreach ($arrTraces as $arrTrace){
				 if(!array_key_exists("class", $arrTrace)) continue;
				 if(count($arr)==0) $arr[] = $arrTrace['class'];
				 else if(get_parent_class($arrTrace['class'])==end($arr)) $arr[] = $arrTrace['class'];
			}
			return end($arr);
		}
	}
	
	/**
	 * admin_options function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_options() {
		global $woocommerce;
		?>
		<h3><?php echo $this->method_title; ?></h3>
		<table class='form-table'>
			 <?php $this->generate_settings_html(); ?>
		</table>
		<?php
		include_once ('admin/views/html-admin-rate-table.php');
	}

	/**
	 * Generate Radio Input HTML.
	 *
	 * @param mixed $key
	 * @param mixed $data
	 * @since 1.0.0
	 * @return string
	 */
	public function generate_radio_html( $key, $data ) {

		$field    = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'radio',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array()
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<ul class="radio <?php echo esc_attr( $data['class'] ); ?>"  id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?>>
						<?php foreach ( (array) $data['options'] as $option_key => $option_value ) : ?>
							<li><label><input name="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( $option_key ); ?>" type="radio" <?php checked( $option_key, esc_attr( $this->get_option( $key ) ) ); ?>><?php echo esc_attr( $option_value ); ?></label></li>
						<?php endforeach; ?>
					</ul>
					<?php echo $this->get_description_html( $data ); ?>
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}
	
	
	/**
	 * Generate Hidden Input HTML.
	 *
	 * @param mixed $key
	 * @param mixed $data
	 * @since 1.0.0
	 * @return string
	 */
	public function generate_hidden_html( $key, $data ) {

		$field    = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'hidden',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array()
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc" style="padding:0" >
				<label for="<?php echo esc_attr( $field ); ?>"></label>
			</th>
			<td class="forminp" style="padding:0" >
				<fieldset>
					<legend class="screen-reader-text"><span></span></legend>
					<input class="<?php echo esc_attr( $data['class'] ); ?>" type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" value="<?php echo esc_attr( $this->get_option( $key ) ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?> />
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	
	/**
	 * calculate_shipping function.
	 *
	 * @access public
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {
		// get carrier settings
		$carrier_settings = get_option('woocommerce_'.$this->id.'_settings');
		$ope_srv = explode('_',$this->id);
		$operator = envoimoinscher_model::get_operator_by_code($ope_srv[0]);
		
		// sender/recipient information
		$from = envoimoinscher_model::get_sender_address();

		// get package address
		$to = array(
			'pays'				=> isset($package['destination']['country'])    ?$package['destination']['country']			:'',
			'code_postal' => isset($package['destination']['postcode'])		?$package['destination']['postcode']		:'',
			'ville'				=> isset($package['destination']['city'])				?$package['destination']['city']				:'',
			'adresse'			=>(isset($package['destination']['address'])    ?$package['destination']['address']			:'').
											(isset($package['destination']['address_2'])	?$package['destination']['address_2']		:''),
			'type'				=> 'particulier'
		);
		
		// exit calculation if country or postal code are not set
		if( $to['pays'] == '' || $to['code_postal'] == '' ){
			return;
		}

		// cart informations
		$weight = envoimoinscher_model::get_cart_weight();
		$dims = envoimoinscher_model::get_dim_from_weight($weight);
		$parcels = array(
			1 => array(
			'poids' => $weight, 
			'longueur' => $dims->length, 
			'largeur' => $dims->width, 
			'hauteur' => $dims->height
			)
		);
		
		// additional parameters
		$pickup_J1 = get_option( 'EMC_PICKUP_J1' );
		$pickup_J2 = get_option( 'EMC_PICKUP_J2' );
		
		$params = array(
			'collecte' => date( 'Y-m-d', envoimoinscher_model::setCollectDate(
				array(
					array(
						'j'		=> $pickup_J1[0],
						'from' => $pickup_J1[1],
						'to'	 => $pickup_J1[2]
					),
					array(
						'j'		=> $pickup_J2[0],
						'from' => $pickup_J2[1],
						'to'	 => $pickup_J2[2]
					)
				)
			) ),
			'delai' => 'aucun',
			'code_contenu' => get_option('EMC_NATURE'),
			'colis.valeur' => $package['contents_cost']
		);
		$params['operator'] = $this->ope_code;
		$params['service'] 	= $this->srv_code;
		
		$offers = envoimoinscher_model::get_quotation($from, $to, $parcels, $params, false);		
		if ( !isset( $offers[$this->id] ) ) return;
		
		//compute shipping rates with scale or not
		$scale_cost = null;
		if($carrier_settings['pricing'] == "2"){
			$scales = envoimoinscher_model::get_scale_data($this->id, $carrier_settings['rate_type']); 
			// scale_reference : total cart weight / total cart price
			$scale_reference = ($carrier_settings['rate_type'] == 'weight') ? $weight : $package['contents_cost'];
			if( !empty($scales) ){
				foreach ($scales as $scale){
					if( ($scale_reference >= $scale['rate_from']) && ($scale_reference < $scale['rate_to'])){
						$scale_cost = $scale['rate_price'];
					}
				}
			}			
		}

		// store delivery date for display in FO
		if( isset($offers[$this->id]['delivery']['date']) ) {
			update_option( '_delivery_date_' . $this->id , $offers[$this->id]['delivery']['date'] );
		}

		// set carrier shipping cost
		$rate = array(
			'id'    			 => $this->id,
			'label'  			 => isset($carrier_settings['srv_name']) ? $carrier_settings['srv_name'] : $this->id .' ('.$operator[0]->ope_name.')',
			'cost'    		 => ($scale_cost != null) ? $scale_cost : $offers[$this->id]['price']['tax-exclusive'],
		);

		$this->add_rate($rate);
	}

}/* end of class */