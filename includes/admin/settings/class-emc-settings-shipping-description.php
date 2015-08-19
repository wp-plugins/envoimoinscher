<?php
/**
 * EMC Shipping Description Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'emc_settings_shipping_description' ) ) :

/**
 * emc_settings_shipping_description
 */
class emc_settings_shipping_description extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'shipping-description';
		$this->label = __( 'Shipping description', 'envoimoinscher' );
		
		add_filter( 'emc_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		
		//Add this hook to display pickup types
		add_action( 'woocommerce_admin_field_pickup', array( $this, 'output_pickup_field' ) );
		add_action( 'woocommerce_update_option_pickup', array( $this, 'save_pickup_field' ) );

		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	}
	
	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		
		$categories = array();
		
		$category_list = envoimoinscher_model::get_categories();
		
		foreach( $category_list as $value ){
			$categories[$value->cat_id] = __( $value->cat_name, 'envoimoinscher' );
		}

		$settings = array(
			
			array( 'title' => __( 'Your shipments', 'envoimoinscher' ), 'type' => 'title', 'id' => 'shipment_types' ),

			array(
				'title' 	 => __( 'Shipment content *', 'envoimoinscher' ),
				'desc'     => __( 'You must specify the contents of your shipments. This information is transmitted to the carriers choose the most accurate among labels available language from the dropdown list. If you check the apply for the package description box, you use the language selected as the description of the contents of all your mail (this will be the information to be forwarded to (x) the carrier (s)). This data will be displayed on the shipping page on which you can trigger the order to send a parcel. It may be amended before validation of the page. Do not check the box if you want to resume direct name of products shipped (name that is saved in your product catalog)', 'envoimoinscher' ),
				'id'       => 'EMC_NATURE',
				'type' 		 => 'select',
				'default'  => '',
				'options'  => array( '' => __('-- Please choose one --', 'envoimoinscher') )+$categories,
				'desc_tip' => true,
				'required' => true,
			),
			
			array(
				'title'    => "",
				'desc'     => __( '<strong>Apply to parcel description.</strong><br/>Choose the most accurate label as this information will be given to carriers.<br/>If checked, this label will replace your product\'s name in all your shipments\' content description.', 'envoimoinscher' ),
				'id'       => 'EMC_CONTENT_AS_DESC',
				'type'     => 'checkbox',
				'default'  => 'no',
				'desc_tip' => true,
			),
			
			array(
				'title' 	 => __( 'Packaging *', 'envoimoinscher' ),
				'desc'     => __( 'Packaging must be specified for Colissimo offers. Additional charges may apply.', 'envoimoinscher' ),
				'id'       => 'EMC_WRAPPING',
				'type' 		 => 'select',
				'default'  => '',
				'options'  => array( '1-Boîte' => __( 'Box', 'envoimoinscher' ), '17-Tube' => __( 'Tube', 'envoimoinscher' )),
				'desc_tip' => true,
				'required' => true,
			),
			
			/*array(
				'title'    => __( 'Individual customer', 'envoimoinscher' ),
				'desc'     => __( 'Checking this option, all your customers will be considered as individual. It means they will all have access to dedicated relay points offers.', 'envoimoinscher' ),
				'id'       => 'EMC_INDI',
				'type'     => 'checkbox',
				'default'  => 'yes',
				'desc_tip' => true,
			),*/
			
			/*array(
				'title'    => __( 'Use multi-shipping', 'envoimoinscher' ),
				'desc'     => __( 'Send a command into several parcels instead of one.<br/>Careful! It may lead to different prices between your customer and real shipping price.<br/>All waybills will be charged.', 'envoimoinscher' ),
				'id'       => 'EMC_MULTIPARCEL',
				'type'     => 'checkbox',
				'default'  => 'no',
				'desc_tip' => true,
			),*/
			
			array( 'type' => 'sectionend', 'id' => 'shipment_types'),
			
			array( 'title' => __( 'Product weight setup', 'envoimoinscher' ), 'type' => 'title', 'id' => 'weight_setup' ),
			
			array(
				'title'    => __( 'Default weight (kg)', 'envoimoinscher' ),
				'desc'     => __( 'You can specify a default weight that will be applied on missing product weights (product weight is set in Products -> Edit product -> Shipping).', 'envoimoinscher' ),
				'id'       => 'EMC_AVERAGE_WEIGHT',
				'type'     => 'text',
				'default'  => '',
				'desc_tip' => false,
			),
			
			/*array(
				'title'    => __( 'Minimal weight', 'envoimoinscher' ),
				'desc'     => __( 'Apply 100g weight to under 100g products', 'envoimoinscher' ),
				'id'       => 'EMC_WEIGHTMIN',
				'type'     => 'checkbox',
				'default'  => 'no',
				'desc_tip' => true,
			),*/
			
			array( 'type' => 'sectionend', 'id' => 'weight_setup'),
			
			array( 'title' => __( 'Pick-ups', 'envoimoinscher' ), 'type' => 'title', 'id' => 'pickup_options' ),
			
			array(
				'title'    => __( 'Pick-up date D+ *', 'envoimoinscher' ),
				'id'       => 'EMC_PICKUP_J1',
				'type'     => 'pickup',
				'default'  => array ( 0 => "2", 1 => "0", 2 => "17" ),
				'required' => true,
			),
			
			array(
				'title'    => __( 'Pick-up date D+ *', 'envoimoinscher' ),
				'id'       => 'EMC_PICKUP_J2',
				'type'     => 'pickup',
				'default'  => array ( 0 => "3", 1 => "17", 2 => "24" ),
				'desc'     => __( 'Define how many days between order and pickup date.', 'envoimoinscher' ),
				'required' => true,
			),

			array(
				'title'    => __( 'Label for delivery date', 'envoimoinscher' ),
				'desc'     => __( 'You can customize delivery date front-office message. Example: "Delivery planned on: {DATE}". Leaving the field empty hides the delivery date.', 'envoimoinscher' ),
				'id'       => 'EMC_LABEL_DELIVERY_DATE',
				'type'     => 'text',
				'default'  => 'Livraison prévue le: {DATE}',
				'desc_tip' => false,
			),
			
			array( 'type' => 'sectionend', 'id' => 'pickup_options'),
			
			array( 'title' => __( 'Insurance', 'envoimoinscher' ), 'type' => 'title', 'id' => 'insurance_options' ),
			
			array(
				'title'    => __( 'Use AXA insurance', 'envoimoinscher' ),
				'desc'     => __( '<strong>Be careful that the cost of insurance is not automatically billed to your customers, and that if you choose this option, it will be automatically selected for all your orders.</strong><br/>By selecting declared value insurance, you declare to you have read <a href="http://www.boxtale.co.uk//faq/131-tout-savoir-sur-l-assurance-ad-valorem.html/notice/" target="_blank">AXA insurance declared value policy notice</a>. As packaging insufficiency and maladjustment are excluded risks of AXA warranty, you could benefit from some extra packaging.', 'envoimoinscher' ),
				'id'       => 'EMC_ASSU',
				'type'     => 'checkbox',
				'default'  => 'no',
				'desc_tip' => true,
			),
			
			array( 'type' => 'sectionend', 'id' => 'insurance_options'),
			
		);

		return $settings;
	}
	
	/**
	 * Output pickup type field.
	 *
	 * @param $field
	 */
	public function output_pickup_field ($field){
		
		if ( true === $field['desc_tip'] ) {
			$description = '';
			$tip = $field['desc'];
		} elseif ( ! empty( $field['desc_tip'] ) ) {
			$description = $field['desc'];
			$tip = $field['desc_tip'];
		} elseif ( ! empty( $field['desc'] ) ) {
			$description = $field['desc'];
			$tip = '';
		} else {
			$description = $tip = '';
		}
		
		if ( $description ) {
			$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
		}

		if ( $tip ) {
			$tip = '<img class="help_tip" data-tip="' . esc_attr( $tip ) . '" src="' . WC()->plugin_url() . '/assets/images/help.png" height="16" width="16" />';
		}
		
		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
			foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		$option_value = WC_Admin_Settings::get_option( $field['id'], $field['default'] );

		$hours = array( 0 => '0:00', 1 => '1:00', 2 => '2:00', 3 => '3:00', 4 => '4:00', 
							5 => '5:00', 6 => '6:00', 7 => '7:00', 8 => '8:00', 9 => '9:00', 
							10 => '10:00', 11 => '11:00', 12 => '12:00', 13 => '13:00',	14 => '14:00', 
							15 => '15:00', 16 => '16:00', 17 => '17:00', 18 => '18:00',	19 => '19:00', 
							20 => '20:00', 21 => '21:00', 22 => '22:00', 23 => '23:00', 24 => '24:00' 
						);

		?><tr valign="top" class="<?php echo sanitize_title( $field['type'] ) ?>">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field['id'][0] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
				<?php echo $tip; ?>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $field['type'] ) ?>">
				<input
					name="<?php echo esc_attr( $field['id'] . "_DAY" ); ?>"
					id="<?php echo esc_attr( $field['id'] . "_DAY" ); ?>"
					type="text"
					value="<?php echo esc_attr( $option_value[0] ); ?>"
					/>
				<span>
					<?php _e( 'for orders between', 'envoimoinscher' ); ?>
					<select 
						name="<?php echo esc_attr( $field['id'] . "_FROM" ); ?>"
						id="<?php echo esc_attr( $field['id'] . "_FROM" ); ?>"
						class="fromto"
					>
						<?php 
							if( $field['id'] == "EMC_PICKUP_J1" ) echo '<option value="0" selected="selected">0:00</option>';
							else{
								foreach($hours as $k => $v){
									echo '<option value='.$k.' class="fromto-'.$k.'"';
									if ( $k == $option_value[1] ) echo 'selected="selected"';
									echo ">$v</option>";
								}
							}
						?>
					</select>
					<?php _e( 'and', 'envoimoinscher' ); ?>
					<select 
						name="<?php echo esc_attr( $field['id'] . "_TO" ); ?>"
						id="<?php echo esc_attr( $field['id'] . "_TO" ); ?>"
						class="fromto"
					>
						<?php 
							if( $field['id'] == "EMC_PICKUP_J2" ) echo '<option value="24" selected="selected">24:00</option>';
							else{
								foreach($hours as $k => $v){
									echo '<option value='.$k.' class="fromto-'.$k.'"';
									if ( $k == $option_value[2] ) echo 'selected="selected"';
									echo ">$v</option>";
								}
							}
						?>
					</select>
				</span>
				<div><?php echo ( $description ) ? $description : ''; ?></div>
			</td>
		</tr><?php
	}
	
	/**
	 * Save options.
	 */
	public function save(){
		
		$settings = $this->get_settings();

		// Cancel save if required value is empty 
		foreach ( $settings as $value ) {
			if(isset($value['required']) && ($value['required'] == true)){
				if ('pickup' == $value['type']) {
					if( $_POST[ $value['id'] . "_DAY" ] == '' || $_POST[ $value['id'] . "_FROM" ] == '' || $_POST[ $value['id'] . "_TO" ] == '' ) {
						emc_admin_settings::add_error( __( 'All fields of pickup dates are required.', 'envoimoinscher' ) );
					}
				}
				elseif (null == $_POST[$value['id']]) {
					emc_admin_settings::add_error( sprintf(__( '%s is a required field.', 'envoimoinscher' ), str_replace('*', '', $value['title']) ) );
				}
			}
		}
		
		parent::save();
		
	}
	
	/**
	 * Save pickup type field.
	 *
	 * @param $field
	 */
	public function save_pickup_field ($field){
		update_option( $field['id'], array( $_POST[ $field['id'] . "_DAY" ], $_POST[ $field['id'] . "_FROM" ], $_POST[ $field['id'] . "_TO" ]) );
	}
}

endif;
