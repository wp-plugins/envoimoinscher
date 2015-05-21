<?php
/**
 * EMC Shipping Data
 *
 * Functions for displaying the EMC shipping data meta box.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * EMC_Meta_Box_Order_Shipping Class
 */
class EMC_Meta_Box_Order_Shipping {
	
	/**
	 * Actual order
	 *
	 * @var mixed
	 */
	protected static $order = false;
	
	/**
	 * Actual order's items
	 *
	 * @var mixed
	 */
	protected static $items = false;
	
	/**
	 * Actual emc offer for this order
	 *
	 * @var mixed
	 */
	protected static $emc_offer = false;
	
	/**
	 * Actual emc carrier for this order
	 *
	 * @var mixed
	 */
	protected static $emc_carrier = false;
	
	/**
	 * Pickup point field
	 *
	 * @var array
	 */
	protected static $parcel_pickup_point = array();
	
	/**
	 * Pickup point field
	 *
	 * @var array
	 */
	protected static $parcel_dropoff_point = array();
	
	/**
	 * Insurance field
	 *
	 * @var array
	 */
	protected static $insurance = array();
	
	/**
	 * Pickup date field
	 *
	 * @var array
	 */
	protected static $pickup_date = array();
	
	/**
	 * Dimension fields
	 *
	 * @var array
	 */
	protected static $dimensions = array();
	
	/**
	 * Content description fields
	 *
	 * @var array
	 */
	protected static $content_description = array();
  
  /**
	 * Content description fields
	 *
	 * @var array
	 */
	protected static $shipping_international = array();
	
	/**
	 * Init shipping info fields we display + save
	 */
	public static function init_shipping_info() {
		
		self::$dimensions = array(
			'_dims_weight' => array(
				'label' => __( 'Total shipping weight:', 'envoimoinscher' ),
				'unit'  => 'kg',
				'show'  => true,
			),
			'_dims_width' => array(
				'label' => __( 'Total shipping width:', 'envoimoinscher' ),
				'unit'  => 'cm',
				'show'  => true,
			),
			'_dims_length' => array(
				'label' => __( 'Total shipping length:', 'envoimoinscher' ),
				'unit'  => 'cm',
				'show'  => true,
			),
			'_dims_height' => array(
				'label' => __( 'Total shipping height:', 'envoimoinscher' ),
				'unit'  => 'cm',
				'show'  => true,
			),
		);
		
		self::$content_description = array(
			'_desc_content' => array(
				'label' => __( 'Content description:', 'envoimoinscher' ),
				'show'  => true,
			),
			'_desc_value' => array(
				'label' => __( 'Declared content value:', 'envoimoinscher' ),
				'show'  => true,
			),
		);
		
		self::$dimensions = array(
			'_dims_weight' => array(
				'label' => __( 'Total shipping weight:', 'envoimoinscher' ),
				'label_simple'  => __( 'weight', 'envoimoinscher' ),
				'unit'  => 'kg',
				'show'  => true,
			),
			'_dims_width' => array(
				'label' => __( 'Total shipping width:', 'envoimoinscher' ),
				'label_simple'  => __( 'width', 'envoimoinscher' ),
				'unit'  => 'cm',
				'show'  => true,
			),
			'_dims_length' => array(
				'label' => __( 'Total shipping length:', 'envoimoinscher' ),
				'label_simple'  => __( 'length', 'envoimoinscher' ),
				'unit'  => 'cm',
				'show'  => true,
			),
			'_dims_height' => array(
				'label' => __( 'Total shipping height:', 'envoimoinscher' ),
				'label_simple'  => __( 'height', 'envoimoinscher' ),
				'unit'  => 'cm',
				'show'  => true,
			),
		);
		
		self::$parcel_pickup_point = array(
			'_pickup_point' => array(
					'label' => __( 'Customer pickup point:', 'envoimoinscher' ),
					'show'  => true,
			),
		);
		
		self::$parcel_dropoff_point = array(
			'_dropoff_point' => array(
				'label' => __( 'Dropoff point:', 'envoimoinscher' ),
				'show'  => true,
			),
		);
		
		self::$insurance = array(
			'_insurance' => array(
				'label' => __( 'Insure this shipment with AXA', 'envoimoinscher' ),
				'show'  => true,
			),
		);
		
		self::$pickup_date = array(
			'_pickup_date' => array(
				'label' => __( 'Departure date:', 'envoimoinscher' ),
				'show'  => true,
			),
		);
    
    $international_filter = array();
    $i = 1; // item type count
		
		$international_filter['proforma_reason'] = array(
      'label'    => __( 'Reason for your shipment *', 'envoimoinscher' ),
      'id'       => 'proforma_reason',
      'name'       => 'proforma_reason',
      'type'     => 'select',
      'options'  => array(
        'sale'      => __( 'Sale', 'envoimoinscher' ),
        'repair'   	=> __( 'Repair', 'envoimoinscher' ),
        'return'    => __( 'Return', 'envoimoinscher' ),
        'present'   => __( 'Present, gift', 'envoimoinscher' ),
        'sample'   	=> __( 'Sample, model', 'envoimoinscher' ),
        'personal' 	=> __( 'Personal use', 'envoimoinscher' ),
        'company'   => __( 'Inter-company documents', 'envoimoinscher' ),
        'other'   	=> __( 'Other', 'envoimoinscher' )
      ),
      'show'  => true,
      'required' => true
    );
		
    foreach ( self::$items as $item_id => $item ) {
			$international_filter['proforma_'.$i.'_post_title_en'] = array(
				'label' => __( 'Describe your goods (English):', 'envoimoinscher' ),
				'show'  => true,
			);
        
      $international_filter['proforma_'.$i.'_post_title_fr'] = array(
				'label' => __( 'Describe your goods (language of the country of origin):', 'envoimoinscher' ),
				'show'  => true,
			);
        
      $international_filter['proforma_'.$i.'_qty'] = array(
				'label' => __( 'Quantity:', 'envoimoinscher' ),
        'show'  => true,
      );
        
      $international_filter['proforma_'.$i.'_price'] = array(
				'label' => __( 'Unit price:', 'envoimoinscher' ),
				'unit'  => '€',
				'show'  => true,
			);
        
      $international_filter['proforma_'.$i.'_origin'] = array(
				'label' => __( 'Country:', 'envoimoinscher' ),
				'show'  => true,
			);

			$international_filter['proforma_'.$i.'_weight'] = array(
				'label' => __( 'Unit weight:', 'envoimoinscher' ),
				'unit'  => 'kg',
				'show'  => true,
			);
			
			$i++;
    }
    
    self::$shipping_international = $international_filter;
	}
	
	private static function output_carrier_selection($rate) {
		?>
			<div class="rates">
				<?php if (self::$emc_offer){ ?>
				<div class="type"><?php _e( 'Your current rate is:', 'envoimoinscher' ); ?> <span id="display_rate"><?php echo wc_price( $rate, array('ex_tax_label'=>true) ); ?></span></div>
				<?php }else{ ?>
				<div class="type red"><?php _e( 'The carrier selection is no longer valid for this order, please select another and click on "Update rate"', 'envoimoinscher' ); ?> </div>
				<?php } ?>
				<span id="current_rate"><?php echo $rate; ?></span>
			</div>
			<div>
					<button class="button-primary"><?php _e( 'Update rate', 'envoimoinscher' ); ?></button>
			</div>
		<?php
	}
	
	private static function ouput_no_emc_carrier_selected() {
		?>
			<style type="text/css">
				#post-body-content, #titlediv, #major-publishing-actions, #minor-publishing-actions, #visibility, #submitdiv { display:none }
			</style>
			<div class="panel-wrap woocommerce">
				<div id="order_shipping" class="panel">
					<h4><?php _e( 'You must first select an EnvoiMoinsCher carrier in order to send this order with us.', 'envoimoinscher' ); ?></h4>
				</div>
			</div>
		<?php
	}

	private static function output_proforma( $order, $offers ) {
		$proforma_mandatory_fields = array();

		// Get the selected service
		if ( get_post_meta( $order->id, '_emc_carrier', true ) != '' ){
			$carrier_code = get_post_meta( $order->id, '_emc_carrier', true );
		}
		else{
			foreach($order->get_shipping_methods() as $value){
				$carrier_code = $value['method_id'];
			}
		}

    if( is_array( $offers ) ) {
      if( isset( $offers[ $carrier_code ] ) ){
        $emc_offer = $offers[ $carrier_code ];
        if( isset( $emc_offer[ 'mandatory' ] ) ){
          foreach( $emc_offer[ 'mandatory' ] as $option => $value ) {
            if ( strstr( $option,'proforma.' ) && !isset( $proforma_mandatory_fields[ $option ] ) ) {
              $proforma_mandatory_fields[ $option ] = $value;
            }
          }
        }
      }
    }
		
		// No mandatory here, so no need to display it
		if (count($proforma_mandatory_fields) == 0) {
			return;
		}
		
		?>
		<div id="international_shipping" class="order_shipping_column_container">
			<h3><?php _e( 'International Shipping', 'envoimoinscher' ); ?></h3>
			<?php
      
      $champs = self::$shipping_international;

      // Select shipping reason
      $select_reason = '<p class="select_reason"><label>' . $champs[ 'proforma_reason' ][ 'label' ] . '</label><select name="' . $champs[ 'proforma_reason' ][ 'name' ] . '" id="' . $champs[ 'proforma_reason' ][ 'id' ] . '">';
			
			foreach ( $champs[ 'proforma_reason' ][ 'options' ] as $option => $value ) {
				if( $option == get_post_meta ( $order->id, '_proforma_reason', true ) ) {
					$select_reason .= '<option value="' . $option . '" selected>' . $value . '</option>';
				} else {
					$select_reason .= '<option value="' . $option . '">' . $value . '</option>';
				}
			}
      
      $select_reason .= '</select></p>';
      echo $select_reason;
      
			// item count
			$i = 1;
			foreach ( self::$items as $item_id => $item ) {
				$_product  = $order->get_product_from_item( $item );
				$item_meta = $order->get_item_meta( $item_id );
			
				?>
				<div class="item">
					<!-- display thumbnail -->
					<div class="img">
					<?php if ( $_product ) : ?>
						<?php echo $_product->get_image( 'shop_thumbnail', array( 'title' => '' ) ); ?>
					<?php else : ?>
						<?php echo wc_placeholder_img( 'shop_thumbnail' ); ?>
					<?php endif; ?>
					</div>

					<!-- display values for one item -->
					<div data-item="<?php echo $i; ?>" class="proforma proforma_<?php echo $i; ?>">
						<h4><?php _e( 'Edit Item', 'envoimoinscher' ); ?> <a class="edit_item_international" href="#"><img src="<?php echo WC()->plugin_url(); ?>/assets/images/icons/edit.png" alt="<?php _e( 'Edit', 'woocommerce' ); ?>" width="14" /></a></h4>

						<?php
		
						if ( isset( $proforma_mandatory_fields[ 'proforma.description_en' ] ) ) {
							$name_en = '<p class="proforma_post_title_en">' . $champs[ 'proforma_' . $i . '_post_title_en' ][ 'label' ] . ' ' . get_post_meta ( $order->id, '_proforma_' . $i . '_post_title_en', true ) . '</p>';
							echo $name_en;
						}
						
						if ( isset( $proforma_mandatory_fields[ 'proforma.description_fr' ] ) ) {
							$name_fr = '<p class="proforma_post_title_fr">' . $champs[ 'proforma_' . $i . '_post_title_fr' ][ 'label' ] . ' ' . get_post_meta ( $order->id, '_proforma_' . $i . '_post_title_fr', true ) . '</p>';
							echo $name_fr;
						}
						
						if ( isset( $proforma_mandatory_fields[ 'proforma.nombre' ] ) ) {
							$quantity = '<p class="proforma_qty">' . $champs['proforma_'.$i.'_qty']['label'] . ' ' . get_post_meta ( $order->id, '_proforma_' . $i . '_qty', true ) . '</p>';
							echo $quantity;
						}

						if ( isset( $proforma_mandatory_fields[ 'proforma.valeur' ] ) ) {
							$item_subtotal = '<p class="proforma_price">' . $champs['proforma_'.$i.'_price']['label'] . ' ' . get_post_meta ( $order->id, '_proforma_' . $i . '_price', true ) . '€</p>';
							echo $item_subtotal;
						}
						
						if ( isset( $proforma_mandatory_fields[ 'proforma.origine' ] ) ) {
							$item_origin = '<p class="proforma_origin">' . $champs['proforma_'.$i.'_origin']['label'] . ' ' . get_post_meta ( $order->id, '_proforma_' . $i . '_origin', true ) . '</p>';
							echo $item_origin;
						}
						
						if ( isset( $proforma_mandatory_fields[ 'proforma.poids' ] ) ) {
							$item_subtotal_weight = '<p class="proforma_weight">' . $champs['proforma_'.$i.'_weight']['label'] . ' ' . get_post_meta ( $order->id, '_proforma_' . $i . '_weight', true ) . ' kg</p>';
							echo $item_subtotal_weight;
						}

						?>  
					</div>
					
					
					<!-- Display form for the edit item -->
					<div class="edit_proforma edit_proforma_<?php echo $i; ?>">
						<h4><?php _e( 'Edit Item', 'envoimoinscher' ); ?></h4>

						<?php
						
						if ( isset( $proforma_mandatory_fields[ 'proforma.description_en' ] ) ) {
							$name_en = '<p class="proforma_post_title_en"><label for="proforma_' . $i . '_post_title_en">' . $champs[ 'proforma_'.$i.'_post_title_en' ][ 'label' ] . ' </label><input type="text" id="proforma_' . $i . '_post_title_en" name="proforma_' . $i . '_post_title_en" value="' . get_post_meta ( $order->id, '_proforma_' . $i . '_post_title_en', true ) . '" /></p>';
							echo $name_en;
						}
						
						if ( isset( $proforma_mandatory_fields[ 'proforma.description_fr' ] ) ) {
							$name_fr = '<p class="proforma_post_title_fr"><label for="proforma_' . $i . '_post_title_fr">' . $champs[ 'proforma_' . $i . '_post_title_fr' ][ 'label' ] . '</label> <input type="text" id="proforma_' . $i . '_post_title_fr" name="proforma_' . $i . '_post_title_fr" value="' . get_post_meta ( $order->id, '_proforma_' . $i . '_post_title_fr', true ) . '" /></p>';
							echo $name_fr;
						}
						
						if ( isset( $proforma_mandatory_fields[ 'proforma.nombre' ] ) ) {
							$quantity = '<p class="proforma_qty"><label for="proforma_' . $i . '_qty">' . $champs[ 'proforma_' . $i . '_qty' ][ 'label' ] . '</label> <input type="number" id="proforma_' . $i . '_qty" name="proforma_' . $i . '_qty" value="' . get_post_meta ( $order->id, '_proforma_' . $i . '_qty', true ) . '" /></p>';
							echo $quantity;
						}
						
						if ( isset( $proforma_mandatory_fields[ 'proforma.valeur' ] ) ) {
							$item_subtotal = '<p class="proforma_price"><label for="proforma_' . $i . '_price">' . $champs[ 'proforma_' . $i . '_price' ][ 'label' ] . '</label> <input type="text" id="proforma_' . $i . '_price" name="proforma_' . $i . '_price" value="' . get_post_meta ( $order->id, '_proforma_' . $i . '_price', true ) . '" /> €</p>';
							echo $item_subtotal;
						}
						
						$display_countries =  WC()->countries->get_countries();
						
						if ( isset( $proforma_mandatory_fields[ 'proforma.origine' ] ) ) {
							$select_country = '<p class="proforma_origin"><label for="proforma_' . $i . '_origin">' . $champs[ 'proforma_' . $i . '_origin' ][ 'label' ] . ' </label><select name="proforma_' . $i . '_origin" id="proforma_' . $i . '_origin">';
							foreach ($display_countries as $country_code => $country) {
								if( $country == get_post_meta ( $order->id, '_proforma_' . $i . '_origin', true ) ) {
									$select_country .= '<option value="'. $country_code .'" selected>' . $country . '</option>';
								} else {
									$select_country .= '<option value="'. $country_code .'">' . $country . '</option>';
								}
							}
							$select_country .= '</select></p>';
							echo $select_country;
						}
						
						if ( isset( $proforma_mandatory_fields[ 'proforma.poids' ] ) ) {
							$item_subtotal_weight = '<p class="proforma_weight"><label for="proforma_' . $i . '_weight">' . $champs[ 'proforma_' . $i . '_weight' ][ 'label' ] . '</label> <input type="text" id="proforma_' . $i . '_weight" name="proforma_' . $i . '_weight" value="' . get_post_meta ( $order->id, '_proforma_' . $i . '_weight', true ) . '" /> kg</p>'; 
							echo $item_subtotal_weight;
						}
						?>  
					</div>
				</div>
				<?php 
				
				$i++;} ?>
				
		</div> 
		<?php
	}

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		global $the_order, $wpdb;
		self::$order = false;
		self::$items = false;
		$rate = false;
		$carrier_code = false;
		$activated_services = envoimoinscher_model::get_enabled_shipping_methods();
		$eligible_services = array(); // TODO
		self::$emc_carrier = false;
			
		// Get the actual order
		if ( ! is_object( $the_order ) ) {
			self::$order = wc_get_order( $post->ID );
		}
		else {
			self::$order = $the_order;
		}
		
		// Get order items
		self::$items = self::$order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
		
		// init fields
		self::init_shipping_info();
		
		// Get the selected service
		if( get_post_meta( self::$order->id, '_emc_carrier', true ) != '' ){
			$carrier_code = get_post_meta( self::$order->id, '_emc_carrier', true );
			$carrier_settings = get_option('woocommerce_'.$carrier_code.'_settings');
			
			// Get the associated carrier object
			$methods = WC()->shipping()->get_shipping_methods();
			self::$emc_carrier = $methods[$carrier_code];
			
			// Load the EMC service
			$service = envoimoinscher_model::get_service_by_carrier_code($carrier_code);
		}
		else{
			foreach(self::$order->get_shipping_methods() as $value){
				$carrier_code = $value['method_id'];
			}
		}
		
		if( get_post_meta( self::$order->id, '_emc_carrier', true ) == '' ) {
			self::ouput_no_emc_carrier_selected();
		}
		
		// wp_nonce_field( 'woocommerce_save_data', 'woocommerce_meta_nonce' );
		
		$offers = envoimoinscher_model::check_rates_from_order ( self::$order );
		if( is_array( $offers ) ) {
			if ( isset ( $offers[ get_post_meta( self::$order->id, '_emc_carrier', true ) ] ) ) {
				self::$emc_offer = $offers[ get_post_meta( self::$order->id, '_emc_carrier', true ) ];
				$rate = self::$emc_offer ['price']['tax-exclusive'] ;
				if ( isset (self::$emc_offer['options']['assurance']['parameters']) ) {
					$insurance_options = self::$emc_offer['options']['assurance']['parameters'];
					$insurance_price = (float)self::$emc_offer['insurance']['tax-exclusive'];
				}
			}
			elseif ( get_post_meta( self::$order->id, '_emc_carrier', true ) != '' ) {
				?>
				<div class="red">
					<p><?php _e('Your offer is no longer available. Please choose another one.', 'envoimoinscher') ;?></p>
				</div>
				<?php 
			}
		}
		else{
			echo '<div class="red">' . $offers . '</div>';	
		}

		?>
		<div class="panel-wrap woocommerce panel">
			<div id="order_shipping">
				<?php if( get_post_meta( self::$order->id, '_emc_ref', true ) ) : ?>
				<div class="already_sent">
					<div class="reference">
						<span class="green"><?php _e( 'Your order has already been sent.', 'envoimoinscher' ); ?></span>
						<?php echo '<div class="emc_ref">' . sprintf(__( 'EnvoiMoinsCher reference: %s.', 'envoimoinscher' ), '<a href="http://www.envoimoinscher.com/suivi-colis.html?reference=' . get_post_meta( self::$order->id, '_emc_ref', true ) . '" target="_blank">' . get_post_meta( self::$order->id, '_emc_ref', true ) . '</a>' ) . '</div>'; ?>
						<?php if ( get_post_meta( self::$order->id, '_carrier_ref', true ) ) {
										echo '<div class="carrier_ref">';
										echo sprintf(__( 'Customer tracking number (available in the front-office > order view page): %s.', 'envoimoinscher' ), get_post_meta( self::$order->id, '_carrier_ref', true ) ) . '</div>'; 
									}
						?>
					</div>
					<div id="emc_documents" class="<?php if ( get_post_meta( self::$order->id, '_label_url', true ) ) echo 'labels_available'; ?>">
						<div class="label_url" <?php if ( !get_post_meta( self::$order->id, '_label_url', true ) ) echo 'style="display:none"'; ?>><?php echo sprintf(__('Your waybill is available. Download it %s.', 'envoimoinscher' ), '<a href="'.get_post_meta( self::$order->id, '_label_url', true ).'" target="_blank">' . __('here', 'envoimoinscher') . '</a>' ); ?></div>
						<div class="remise" <?php if ( !get_post_meta( self::$order->id, '_remise', true ) ) echo 'style="display:none"'; ?>><?php echo sprintf(__('Your delivery waybill is available. Download it %s.', 'envoimoinscher' ), '<a href="'.get_post_meta( self::$order->id, '_remise', true ).'" target="_blank">' . __('here', 'envoimoinscher') . '</a>' ); ?></div>
						<div class="manifest" <?php if ( !get_post_meta( self::$order->id, '_manifest', true ) ) echo 'style="display:none"'; ?>><?php echo sprintf(__('Your manifest is available. Download it %s.', 'envoimoinscher' ), '<a href="'.get_post_meta( self::$order->id, '_manifest', true ).'" target="_blank">' . __('here', 'envoimoinscher') . '</a>' ); ?></div>
						<div class="connote" <?php if ( !get_post_meta( self::$order->id, '_connote', true ) ) echo 'style="display:none"'; ?>><?php echo sprintf(__('Your connote is available. Download it %s.', 'envoimoinscher' ), '<a href="'.get_post_meta( self::$order->id, '_connote', true ).'" target="_blank">' . __('here', 'envoimoinscher') . '</a>' ); ?></div>
						<div class="proforma" <?php if ( !get_post_meta( self::$order->id, '_proforma', true ) ) echo 'style="display:none"'; ?>><?php echo sprintf(__('Your pro-forma invoice is available. Download it %s.', 'envoimoinscher' ), '<a href="'.get_post_meta( self::$order->id, '_proforma', true ).'" target="_blank">' . __('here', 'envoimoinscher') . '</a>' ); ?></div>
						<div class="b13a" <?php if ( !get_post_meta( self::$order->id, '_b13a', true ) ) echo 'style="display:none"'; ?>><?php echo sprintf(__('Your B13A export declaration is available. Download it %s.', 'envoimoinscher' ), '<a href="'.get_post_meta( self::$order->id, '_b13a', true ).'" target="_blank">' . __('here', 'envoimoinscher') . '</a>' ); ?></div>
					</div>
				</div>
				<?php endif; ?>
			
				<?php if ( get_post_meta( self::$order->id, '_emc_carrier', true ) != '' ) self::output_carrier_selection($rate); ?>
					
				<div class="order_shipping_column_container">

					<div class="order_shipping_column">
						
						<!-- carrier selection -->
						<h4><?php _e( 'Chosen carrier', 'envoimoinscher' ); ?></h4>
						
						<div class="carrier_select">
							<select name="_emc_carrier" id="_emc_carrier">
								<?php 
									if ( get_post_meta( self::$order->id, '_emc_carrier', true ) == '' ) echo '<option>' . __( 'Please select a carrier', 'envoimoinscher' ) . '</option>';
									foreach( $offers as $key => $value ){
										echo '<option value="'.$key.'" ';
										if ( $key == $carrier_code ) echo 'selected';
										echo '>'.$value['service']['code'].' - '.wc_price( $value['price']['tax-exclusive'], array('ex_tax_label'=>true) ).'</option>';
									}
								?>
							</select>
						
						</div><!-- eod carrier_select -->
						
						<h4><?php _e( 'Shipment information', 'envoimoinscher' ); ?> <a class="edit_shipment_info" href="#"><img src="<?php echo WC()->plugin_url(); ?>/assets/images/icons/edit.png" alt="<?php _e( 'Edit', 'woocommerce' ); ?>" width="14" /></a></h4>
							
						<!-- display values -->
						<div class="shipment_info">

							<?php						
							foreach ( self::$content_description as $key => $field ) {
								if ( isset( $field['show'] ) && false === $field['show'] ) {
									continue;
								}
								if( $key == '_desc_value' ) {
									$value = wc_price( get_post_meta( self::$order->id, $key, true), array('ex_tax_label'=>true) );
								}
								else {
									$value = get_post_meta( self::$order->id, $key, true);
								}
								echo '<p class="form-field form-field-wide"><span>'. $field['label'] . ' '. $value .'</span></p>';
							}
							
							if( isset($service) && $service->srv_pickup_point ) {
								foreach ( self::$parcel_pickup_point as $key => $field ) {
									if ( isset( $field['show'] ) && false === $field['show'] ) {
										continue;
									}

									echo '<p class="form-field form-field-wide"><span>'. $field['label'] . ' ';
									
									echo '<span>'. get_post_meta( $post->ID, $key, true ) . '</span></span></p>';
								}
							}
							
							if( isset($service) && $service->srv_dropoff_point ) {
								foreach ( self::$parcel_dropoff_point as $key => $field ) {
									if ( isset( $field['show'] ) && false === $field['show'] ) {
										continue;
									}

									echo '<p class="form-field form-field-wide"><span>'. $field['label'] . ' ';
									
									echo '<span>'. get_post_meta( $post->ID, $key, true ) . '</span></span></p>';
								}
							}
							
						?>
						</div><!-- eod shipment_info -->
						
						<!-- display form -->
						<div class="edit_shipment_info">
							
							<?php 
							foreach ( self::$content_description as $key => $field ) {
								echo '<div class="form-field form-field-wide"><span><span>'.$field['label'].'</span> ';
								if( $key == '_desc_content' ) {
									echo '<textarea name="'.$key.'" id="'.$key.'">'.get_post_meta( self::$order->id, $key, true).'</textarea></span></div>';
								}
								else {
									echo '<input type="text" name="'.$key.'" id="'.$key.'" value="'.get_post_meta( self::$order->id, $key, true).'" size=3 /></span></div>';
								}
							}
							
							if( isset($service) && $service->srv_pickup_point ) {
								foreach ( self::$parcel_pickup_point as $key => $field ) {
									echo '<div class="form-field form-field-wide"><span><span>'.$field['label'].'</span> ';
									echo '<input type="text" name="'.$key.'" id="'.$key.'" value="'.get_post_meta( $post->ID, $key, true ).'" size=8 /> </span>';
									echo '<a href="//www.envoimoinscher.com/choix-relais.html?cp='.self::$order->shipping_postcode.'&ville='.self::$order->shipping_city.'&country='.self::$order->shipping_country.'&srv='.$service->srv_code.'&ope='.$service->ope_code.'" target="_blank">'.__( 'Get code', 'envoimoinscher' ).'</a></div>';
								}
							}
							
							if( isset($service) && $service->srv_dropoff_point ) {
								foreach ( self::$parcel_dropoff_point as $key => $field ) {
									echo '<div class="form-field form-field-wide"><span><span>'.$field['label'].'</span> ';
									echo '<input type="text" name="'.$key.'" id="'.$key.'" value="'.get_post_meta( $post->ID, $key, true ).'" size=8 /> </span>';
									echo '<a href="//www.envoimoinscher.com/choix-relais.html?cp='.get_option('EMC_POSTALCODE').'&ville='.get_option('EMC_CITY').'&country='.'FR'.'&srv='.$service->srv_code.'&ope='.$service->ope_code.'" target="_blank">'.__( 'Get code', 'envoimoinscher' ).'</a></div>';
								}			
							}
							?>
							
						</div><!-- eod edit_shipment_info -->
						
						<div>
							<?php
								foreach ( self::$pickup_date as $key => $field ) {
									if ( isset( $field['show'] ) && false === $field['show'] ) {
										continue;
									}
							?>
							<p class="form-field form-field-wide"><span><?php echo $field['label']; ?></span>
								<input type="text" class="date-picker" name="<?php echo $key;?>" id="<?php echo $key;?>" maxlength="10" value="<?php echo date_i18n( 'Y-m-d', get_post_meta( $post->ID, $key, true ) ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
							</p>
							<?php
								}
							?>
						</div><!-- eod pickup_date -->
						
					</div><!-- eod order_shipping_column -->

					<div class="order_shipping_column">
						<h4><?php _e( 'Dimensions', 'envoimoinscher' ); ?> <a class="edit_dimensions" href="#"><img src="<?php echo WC()->plugin_url(); ?>/assets/images/icons/edit.png" alt="<?php _e( 'Edit', 'woocommerce' ); ?>" width="14" /></a></h4>
						
						<!-- display values -->
						<div class="dimensions">
								<?php
								envoimoinscher_model::get_weight_from_order(self::$order->id);
								foreach ( self::$dimensions as $key => $field ) {
									if ( isset( $field['show'] ) && false === $field['show'] ) {
										continue;
									}

									echo '<p class="form-field form-field-wide"><span>'. $field['label'] . ' ';
									
									if( get_post_meta( $post->ID, $key, true ) != '' ) {
										echo '<span>'. get_post_meta( $post->ID, $key, true ) .' '. $field['unit'] . '</span></span></p>';
									}
									else{
										echo '<span style="color:red">'. sprintf( __( 'No %s defined yet.', 'envoimoinscher' ), $field['label_simple'] ) .'</span></span></p>';
									}
								}
								?>
						</div><!-- eod dimensions -->
						
						<!-- display form -->
						<div class="edit_dimensions">
							<?php 
							foreach ( self::$dimensions as $key => $field ) {
								echo '<div class="form-field form-field-wide"><span><span>'.$field['label'].'</span> ';
								echo '<input type="text" name="'.$key.'" id="'.$key.'" value="'.get_post_meta( $post->ID, $key, true ).'" size=3 /> '.$field['unit'].'</span></div>';
							}
							?>
						</div><!-- eod edit_dimensions -->
						
					</div><!-- eod order_shipping_column -->
						
					<?php if ( isset ($insurance_options) ) : ?>
					<div class="order_shipping_column">
						<h4><?php _e( 'Insurance', 'envoimoinscher' ); ?></h4>
	
						<div>
							<?php 
							foreach ( self::$insurance as $key => $field ) {
								echo '<p>';
								echo '<input type="checkbox" id="'.$key.'" name="'.$key.'"';
								if ( get_post_meta( $post->ID, $key, true ) ) echo 'checked';
								echo '/>'.$field['label'].' (<span id="insurance_rate">'.wc_price( $insurance_price, array('ex_tax_label'=>false) ).'</span>)</p>';
								echo '<span id="insurance_rate_unformatted">'.$insurance_price.'</span>';
							}
							?>
						</div><!-- eod insurance -->
						
						<div class="edit_insurance">
							<?php
								foreach($insurance_options as $option) {
									if( empty($option['values']) ) continue;
									$default = get_post_meta( $post->ID, '_'.str_replace('.', '_', $option['code']), true );
									echo '<div class="form-field form-field-wide"><label for="_'.$option['code'].'">'.envoimoinscher_model::get_english($option['label']).'</label>';
									echo '<select name="_'.$option['code'].'" id="_'.$option['code'].'">';
									foreach($option['values'] as $key => $value) {
										echo '<option value="'.$key.'" ';
										if( $key == $default ) echo 'selected';
										echo '>'.envoimoinscher_model::get_english($value).'</option>';
									}
									echo '</select></div>';				
								}
							?>
						</div><!-- eod edit_insurance -->
					</div><!-- eod order_shipping_column -->
					<?php endif; ?>
					
				</div><!-- eod order_shipping_column_container -->
				<div class="clear"></div>
				
				<?php self::output_proforma( self::$order, $offers ); ?>
				
			</div><!-- eod order_shipping -->
		</div><!-- eod panel -->
		<?php
	}
  
	/**
	 * Save meta box data
	 */
	public static function save( $post_id, $post ) {
		global $the_order;

		if ( ! is_object( $the_order ) ) {
			$the_order = wc_get_order( $post->ID );
		}

		self::$order = $the_order;
		self::$items = self::$order->get_items();
		
		if( isset($_POST[ '_emc_carrier' ]) ) {
			$carrier_code = $_POST[ '_emc_carrier' ];
			
			$service = envoimoinscher_model::get_service_by_carrier_code($carrier_code);
			
			if( $service != false ) {
				global $wpdb;

				self::init_shipping_info();

				// Add key
				add_post_meta( $post_id, '_order_key', uniqid( 'order_' ), true );

				if ( self::$content_description ) {
					foreach ( self::$content_description as $key => $field ) {
						update_post_meta( $post_id, $key, wc_clean( $_POST[ $key ] ) );
					}
				}
				
				if ( $carrier_code != get_post_meta( $post_id, '_emc_carrier', true ) ) {
					// if carrier has changed, reinitialize parcel points
					update_post_meta( $post_id, '_emc_carrier', wc_clean( $_POST[ '_emc_carrier' ] ) );
					$carrier_settings = get_option('woocommerce_'.$carrier_code.'_settings');

					if ( $service->srv_dropoff_point ) {
						if ( get_post_meta( $post_id, '_dropoff_point_' . $carrier_code , true ) ) {
							$dropoff_point = get_post_meta( $post_id, '_dropoff_point_' . $carrier_code , true );	
						}
						else{
							$dropoff_point = isset( $carrier_settings['default_dropoff_point'] ) ? $carrier_settings['default_dropoff_point'] : '';
						}
					}
					else{
						$dropoff_point = 'POST';
					}

					update_post_meta( $post_id, '_dropoff_point', $dropoff_point );		
					
					if ( get_post_meta( $post_id, '_pickup_point_' . $carrier_code , true ) ) {
						update_post_meta( $post_id, '_pickup_point', get_post_meta( $post_id, '_pickup_point_' . $carrier_code , true ) );	
					}
					else{
						update_post_meta( $post_id, '_pickup_point', '' );		
					}
				}
				else {
					// else update parcel points info
					if( $service->srv_dropoff_point ) {
						foreach ( self::$parcel_dropoff_point as $key => $field ) {
							update_post_meta( $post_id, $key, wc_clean( $_POST[ $key ] ) );
							update_post_meta( $post_id, '_dropoff_point_' . $carrier_code , wc_clean( $_POST[ $key ] ) );
						}
					}
					
					if( $service->srv_pickup_point ) {
						foreach ( self::$parcel_pickup_point as $key => $field ) {
							update_post_meta( $post_id, $key, wc_clean( $_POST[ $key ] ) );
							update_post_meta( $post_id, '_pickup_point_' . $carrier_code , wc_clean( $_POST[ $key ] ) );
						}
					}
				}
				
				if ( self::$pickup_date ) {
					foreach ( self::$pickup_date as $key => $field ) {
						update_post_meta( $post_id, $key, wc_clean( strtotime (  $_POST[ $key ] ) ) );
					}
				}
				
				if ( self::$dimensions ) {
					foreach ( self::$dimensions as $key => $field ) {
						update_post_meta( $post_id, $key, wc_clean( $_POST[ $key ] ) );
					}
				}
				
				if ( self::$insurance ) {
					foreach ( self::$insurance as $key => $field ) {
						$value = 0;
						if ( isset ($_POST[ '_insurance' ]) ) $value = 1;
						update_post_meta( $post_id, '_insurance', $value );
						if ( isset ($_POST[ '_assurance_emballage' ]) ) update_post_meta( $post_id, '_assurance_emballage', $_POST[ '_assurance_emballage' ] );
						if ( isset ($_POST[ '_assurance_materiau' ]) ) update_post_meta( $post_id, '_assurance_materiau', $_POST[ '_assurance_materiau' ] );
						if ( isset ($_POST[ '_assurance_protection' ]) ) update_post_meta( $post_id, '_assurance_protection', $_POST[ '_assurance_protection' ] );
						if ( isset ($_POST[ '_assurance_fermeture' ]) ) update_post_meta( $post_id, '_assurance_fermeture', $_POST[ '_assurance_fermeture' ] );
					}
				}
				
				if ( self::$shipping_international ) {
					if ( isset ( $_POST[ 'proforma_reason' ] ) ) update_post_meta( $post_id, '_proforma_reason', $_POST[ 'proforma_reason' ] );  
					
					$display_countries =  WC()->countries->get_countries();
					
					foreach ( self::$shipping_international as $key => $field ) {
						self::$items = self::$order->get_items();
						for ( $i = 1; $i <= count( self::$items ); $i++ ) {
							if ( isset ( $_POST[ 'proforma_' . $i . '_post_title_en' ]) ) update_post_meta( $post_id, '_proforma_' . $i . '_post_title_en', $_POST[ 'proforma_' . $i . '_post_title_en' ] );
							if ( isset ( $_POST[ 'proforma_' . $i . '_post_title_fr' ]) ) update_post_meta( $post_id, '_proforma_' . $i . '_post_title_fr', $_POST[ 'proforma_' . $i . '_post_title_fr' ] );
							if ( isset ( $_POST[ 'proforma_' . $i . '_qty' ]) ) update_post_meta( $post_id, '_proforma_' . $i . '_qty', $_POST[ 'proforma_' . $i . '_qty' ] );
							if ( isset ( $_POST[ 'proforma_' . $i . '_price' ]) ) update_post_meta( $post_id, '_proforma_' . $i . '_price', $_POST[ 'proforma_' . $i . '_price' ] );			
							if ( isset ( $_POST[ 'proforma_' . $i . '_origin' ]) ) update_post_meta( $post_id, '_proforma_' . $i . '_origin', $display_countries[$_POST[ 'proforma_' . $i . '_origin' ]] );
							if ( isset ( $_POST[ 'proforma_' . $i . '_weight' ]) ) update_post_meta( $post_id, '_proforma_' . $i . '_weight', $_POST[ 'proforma_' . $i . '_weight' ] );
						}
					}
				}
				
				wc_delete_shop_order_transients( $post_id );
			}
		}
	}
}