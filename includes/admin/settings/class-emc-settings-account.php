<?php
/**
 * EMC Account Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'emc_settings_account' ) ) :

/**
 * emc_settings_accounts
 */
class emc_settings_account extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'account';
		$this->label = __( 'My Account', 'envoimoinscher' );

		add_filter( 'emc_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		
		//Add this hook to display radio buttons inline
		add_action( 'woocommerce_admin_field_radio_inline', array( $this, 'output_radio_inline_field' ) );
		
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	}
	
	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
			
		$pick_dispo = array(
			'START' => array(
				'12:00' => '12:00', '12:15' => '12:15', '12:30' => '12:30', '12:45' => '12:45',
				'13:00' => '13:00', '13:15' => '13:15', '13:30' => '13:30', '13:45' => '13:45',
				'14:00' => '14:00', '14:15' => '14:15', '14:30' => '14:30', '14:45' => '14:45',
				'15:00' => '15:00', '15:15' => '15:15', '15:30' => '15:30', '15:45' => '15:45',
				'16:00' => '16:00', '16:15' => '16:15', '16:30' => '16:30', '16:45' => '16:45',
				'17:00' => '17:00'),
			'END' => array(
				'17:00' => '17:00', '17:15' => '17:15', '17:30' => '17:30', '17:45' => '17:45',
				'18:00' => '18:00', '18:15' => '18:15', '18:30' => '18:30', '18:45' => '18:45',
				'19:00' => '19:00', '19:15' => '19:15', '19:30' => '19:30', '19:45' => '19:45',
				'20:00' => '20:00', '20:15' => '20:15', '20:30' => '20:30', '20:45' => '20:45',
				'21:00' => '21:00')
		);
		
		$settings = array(
			
			array( 'title' => __( 'API account', 'envoimoinscher' ), 'type' => 'title', 'id' => 'api_account_options' ),

			array(
				'title'    => __( 'Login *', 'envoimoinscher' ),
				'desc'     => __( 'EnvoiMoinsCher.com login', 'envoimoinscher' ),
				'id'       => 'EMC_LOGIN',
				'type'     => 'text',
				'default'  => '',
				'placeholder' => __( 'login', 'envoimoinscher'),
				'desc_tip' => true,
				'required' => true,
			),
			
			array(
				'title'    => __( 'Password *', 'envoimoinscher' ),
				'desc'     => __( 'EnvoiMoinsCher.com password', 'envoimoinscher' ),
				'id'       => 'EMC_PASS',
				'type'     => 'password',
				'default'  => '',
				'placeholder' => __( 'password', 'envoimoinscher'),
				'desc_tip' => true,
				'required' => true,
			),
			
			array(
				'title'    => __( 'API key *', 'envoimoinscher' ),
				'desc'     => __( 'Key received by mail from EnvoiMoinsCher.com. Insert test key or production key according to your environment.', 'envoimoinscher' ),
				'id'       => 'EMC_KEY',
				'type'     => 'text',
				'default'  => '',
				'placeholder' => __( 'API key', 'envoimoinscher'),
				'desc_tip' => true,
				'required' => true,
			),
			
			array(
				'title'    => __( 'Environment *', 'envoimoinscher' ),
				'id'       => 'EMC_ENV',
				'default'  => 'test',
				'type'     => 'radio_inline',
				'options'  => array(
					'test'   => __( 'Test', 'envoimoinscher' ),
					'prod' 	 => __( 'Live', 'envoimoinscher' ),
				),
				'desc_tip' =>  false,
				'autoload' => true,
				'inline'   => true,
				'required' => true,
			),
			
			array( 'type' => 'sectionend', 'id' => 'api_account_options'),
			
			array( 'title' => __( 'Pickup address', 'envoimoinscher' ), 'type' => 'title', 'id' => 'pickup_address_options' ),
			
			array(
				'title'    => __( 'Shipper civility *', 'envoimoinscher' ),
				'desc'     => __( 'Shipper civility: choose Mr. or Mrs.', 'envoimoinscher' ),
				'id'       => 'EMC_CIV',
				'default'  => 'M',
				'type'     => 'radio_inline',
				'options'  => array(
					'M'       => __( 'Mr.', 'envoimoinscher' ),
					'Mme' => __( 'Mrs', 'envoimoinscher' ),
				),
				'desc_tip' =>  true,
				'autoload' => false,
				'required' => true,
			),
			
			array(
				'title'    => __( 'Shipper first name *', 'envoimoinscher' ),
				'desc'     => __( 'Shipper first name', 'envoimoinscher' ),
				'id'       => 'EMC_FNAME',
				'type'     => 'text',
				'default'  => '',
				'placeholder' => __( 'first name', 'envoimoinscher'),
				'desc_tip' => true,
				'required' => true,
			),
			
			array(
				'title'    => __( 'Shipper last name *', 'envoimoinscher' ),
				'desc'     => __( 'Shipper last name', 'envoimoinscher' ),
				'id'       => 'EMC_LNAME',
				'type'     => 'text',
				'default'  => '',
				'placeholder' => __( 'last name', 'envoimoinscher'),
				'desc_tip' => true,
				'required' => true,
			),
			
			array(
				'title'    => __( 'Company *', 'envoimoinscher' ),
				'desc'     => __( 'Parcel pickup company name', 'envoimoinscher' ),
				'id'       => 'EMC_COMPANY',
				'type'     => 'text',
				'default'  => '',
				'placeholder' => __( 'company', 'envoimoinscher'),
				'desc_tip' => true,
				'required' => true,
			),
			
			array(
				'title'    => __( 'Address *', 'envoimoinscher' ),
				'desc'     => __( 'Parcel pickup address', 'envoimoinscher' ),
				'id'       => 'EMC_ADDRESS',
				'type'     => 'textarea',
				'default'  => '',
				'placeholder' => __( 'address', 'envoimoinscher'),
				'desc_tip' => true,
				'required' => true,
			),
			
			array(
				'title'    => __( 'ZIP code *', 'envoimoinscher' ),
				'desc'     => __( 'Parcel pickup ZIP code', 'envoimoinscher' ),
				'id'       => 'EMC_POSTALCODE',
				'type'     => 'text',
				'default'  => '',
				'placeholder' => __( 'ZIP code', 'envoimoinscher'),
				'desc_tip' => true,
				'required' => true,
				'required' => true,
			),
			
			array(
				'title'    => __( 'City *', 'envoimoinscher' ),
				'desc'     => __( 'Parcel pickup city', 'envoimoinscher' ),
				'id'       => 'EMC_CITY',
				'type'     => 'text',
				'default'  => '',
				'placeholder' => __( 'city', 'envoimoinscher'),
				'desc_tip' => true,
				'required' => true,
			),
			
			array(
				'title'    => __( 'Phone number *', 'envoimoinscher' ),
				'desc'     => __( 'Shipper phone number (EMC or carriers may contact you concerning your shipment)', 'envoimoinscher' ),
				'id'       => 'EMC_TEL',
				'type'     => 'text',
				'default'  => '',
				'placeholder' => __( 'phone number', 'envoimoinscher'),
				'desc_tip' => true,
				'required' => true,
			),
			
			array(
				'title'    => __( 'Email *', 'envoimoinscher' ),
				'desc'     => __( 'Shipper email (EMC or carriers may contact you concerning your shipment)', 'envoimoinscher' ),
				'id'       => 'EMC_MAIL',
				'type'     => 'text',
				'default'  => '',
				'placeholder' => __( 'email', 'envoimoinscher'),
				'desc_tip' => true,
				'required' => true,
			),
			
			array(
				'title'    => __( 'Additional address information', 'envoimoinscher' ),
				'id'       => 'EMC_COMPL',
				'type'     => 'textarea',
				'default'  => '',
				'placeholder' => __( 'Floor, code, ...', 'envoimoinscher'),
				'desc_tip' => false,
			),
			
			array(
				'title' 		=> __( 'Pickup opening hour *', 'envoimoinscher' ),
				'desc'     => __( 'Time from which you are available for pickup (pickup usually starts from midday)', 'envoimoinscher' ),
				'id'       => 'EMC_DISPO_HDE',
				'type' 			=> 'select',
				'default' 		=> '12:00',
				'options' 		=> $pick_dispo['START'],
				'desc_tip' => true,
				'required' => true,
			),
			
			array(
				'title' 		=> __( 'Pickup closing hour *', 'envoimoinscher' ),
				'desc'     => __( 'Time until which you are available for pickup (pickup usually ends at 5pm)', 'envoimoinscher' ),
				'id'       => 'EMC_DISPO_HLE',
				'type' 			=> 'select',
				'default' 		=> '17:00',
				'options' 		=> $pick_dispo['END'],
				'desc_tip' => true,
				'required' => true,
			),
			
			array( 'type' => 'sectionend', 'id' => 'pickup_address_options'),
			
		);

		return $settings;
	}
	
	/**
	 * Output radio inline field.
	 *
	 * @param $field
	 */
	public function output_radio_inline_field ($field){
		
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
		
		if ( $description && in_array( $field['type'], array( 'textarea', 'radio' ) ) ) {
			$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
		} elseif ( $description && in_array( $field['type'], array( 'checkbox' ) ) ) {
			$description =  wp_kses_post( $description );
		} elseif ( $description ) {
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
		
		?><tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
				<?php echo $tip; ?>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $field['type'] ) ?>">
				<fieldset>
					<?php echo $description; ?>
					<ul style="margin-top:2px;">
					<?php
						foreach ( $field['options'] as $key => $val ) {
							?>
							<li style="float:left;padding-right:20px;">
								<label><input
									name="<?php echo esc_attr( $field['id'] ); ?>"
									value="<?php echo $key; ?>"
									type="radio"
									style="<?php echo esc_attr( $field['css'] ); ?>"
									class="<?php echo esc_attr( $field['class'] ); ?>"
									<?php echo implode( ' ', $custom_attributes ); ?>
									<?php checked( $key, $option_value ); ?>
									/> <?php echo $val ?></label>
							</li>
							<?php
						}
					?>
					</ul>
				</fieldset>
			</td>
		</tr><?php
	}
	
	/**
	 * Save options.
	 */
	public function save(){
		
		$settings = $this->get_settings();
		
		// Add error if required value is empty 
		foreach ( $settings as $value ) {
			if(isset($value['required']) && ($value['required'] == true) && (null == $_POST[$value['id']])) {
				emc_admin_settings::add_error( sprintf(__( '%s is a required field.', 'envoimoinscher' ), str_replace('*', '', $value['title']) ) );
			}
		}
		
		parent::save();
		
	}

}

endif;
