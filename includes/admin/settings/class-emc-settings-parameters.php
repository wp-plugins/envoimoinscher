<?php
/**
 * EMC Additional Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'emc_settings_parameters' ) ) :

/**
 * emc_settings_parameters
 */
class emc_settings_parameters extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'parameters';
		$this->label = __( 'Settings', 'envoimoinscher' );
		
    $this->update_email_options();
		add_filter( 'emc_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
    add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	}
	
  /**
   * Update emails option.
   * Update options : EMC_mail_label, EMC_mail_notif, EMC_mail_bill.
   * 
   * @return Void
   */
  public function update_email_options() {
    require_once(WP_PLUGIN_DIR.'/envoimoinscher/env/WebService.php');
    require_once(WP_PLUGIN_DIR.'/envoimoinscher/env/User.php');
		$user_class = new EnvUser(array('user' => get_option('EMC_LOGIN'), 'pass' => get_option('EMC_PASS'), 'key' => get_option('EMC_KEY')));
    $user_class->setPlatformParams(EMC_PLATFORM, WC_VERSION, EMC_VERSION);
    $env = get_option('EMC_ENV');
    $user_class->setEnv($env);
		$upload_dir = wp_upload_dir();
    $user_class->setUploadDir($upload_dir['basedir']);
		$user_class->getEmailConfiguration();
    $emails_params = $user_class->user_configuration['emails'];

    if( !empty($emails_params) ){
			$emails_params['label'] == 'true' ? $label = 'yes' : $label = 'no';
			$emails_params['notification'] == 'true' ? $notification = 'yes' : $notification = 'no';
			$emails_params['bill'] == 'true' ? $bill = 'yes' : $bill = 'no';  

			update_option( 'EMC_mail_label', $label );
			update_option( 'EMC_mail_notif', $notification );
			update_option( 'EMC_mail_bill', $bill );
		}
  }
  
  /**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = array(
			
			array( 'title' => __( 'Mails to send / receive', 'envoimoinscher' ), 'type' => 'title', 'id' => 'emails_parameters' ),
        
			array(
				'title'    => __( 'Waybill mail', 'envoimoinscher' ),
				'desc'     => __( 'Sent to sender (you). This mail contain shipping instructions: waybill(s).', 'envoimoinscher' ),
				'id'       => 'EMC_mail_label',
				'type'     => 'checkbox',
				'default'  => 'yes',
				'desc_tip' => true,
			),
		
			array(
				'title'    => __( 'Recipient notification mail', 'envoimoinscher' ),
				'desc'     => __( 'Sent to recipient (your customer). He will be notified that a parcel is sent by EnvoiMoinsCher and not the carrier.', 'envoimoinscher' ),
				'id'       => 'EMC_mail_notif',
				'type'     => 'checkbox',
				'default'  => 'no',
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Billing mail', 'envoimoinscher' ),
				'desc'     => __( 'Sent to billing adress, as your EnvoiMoinsCher profile. Sends you your bill for sent orders.', 'envoimoinscher' ),
				'id'       => 'EMC_mail_bill',
				'type'     => 'checkbox',
				'default'  => 'yes',
				'desc_tip' => true,
			),
			
		array( 'type' => 'sectionend', 'id' => 'emails_parameters'),
			
		);

		return $settings;
	}
	
  /**
   * Posts new informations about e-mail configuration for logged user.
   * Accepted keys are : label, notification, bill. If you want to remove the e-mail sending
   * for one of these keys, you must put into it an empty string like "".
   * @access public
   * @param Array $params Params with new e-mail configuration
   * @return Void
   */
  public function save() {
    if( isset( $_POST ) && ! empty( $_POST ) ) {
      $new_emails_params = array(
        'label' => ! empty( $_POST['EMC_mail_label'] ) ? '1' : '',
        'notification' => ! empty( $_POST['EMC_mail_notif'] ) ? '1' : '',
        'bill' => ! empty( $_POST['EMC_mail_bill'] ) ? '1' : ''
      );
      
      require_once(WP_PLUGIN_DIR.'/envoimoinscher/env/WebService.php');
			require_once(WP_PLUGIN_DIR.'/envoimoinscher/env/User.php');
      $user_class = new EnvUser(array('user' => get_option('EMC_LOGIN'), 'pass' => get_option('EMC_PASS'), 'key' => get_option('EMC_KEY')));
      $user_class->setPlatformParams(EMC_PLATFORM, WC_VERSION, EMC_VERSION);
      $env = get_option('EMC_ENV');
      $user_class->setEnv($env);
			$upload_dir = wp_upload_dir();
			$user_class->setUploadDir($upload_dir['basedir']);
      $user_class->postEmailConfiguration($new_emails_params);

      $new_emails_params['label'] == '1' ? $label = 'yes' : $label = 'no';
      $new_emails_params['notification'] == '1' ? $notification = 'yes' : $notification = 'no';
      $new_emails_params['bill'] == '1' ? $bill = 'yes' : $bill = 'no';

      update_option( 'EMC_mail_label', $label );
      update_option( 'EMC_mail_notif', $notification );
      update_option( 'EMC_mail_bill', $bill );
    }
  }
}

endif;
