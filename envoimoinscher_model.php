<?php
class envoimoinscher_model{
	
	static protected $logger;
	
	/**
	 * Create a new logger for the module
	 */
	static function init_logger() {
		self::$logger = new WC_Logger();
	}
	
	/**
	 * Check if the plugin's tables exists
	 * @return true if all tables exists, false otherwise
	 */
	static function is_plugin_installed() {
		global $wpdb;
		// delete module tables inserted from create_database
		$sql = array();
		$sql[] = 'SHOW TABLES LIKE "'.$wpdb->prefix.'emc_categories"';
		$sql[] = 'SHOW TABLES LIKE "'.$wpdb->prefix.'emc_dimensions"';
		$sql[] = 'SHOW TABLES LIKE "'.$wpdb->prefix.'emc_documents"';
		$sql[] = 'SHOW TABLES LIKE "'.$wpdb->prefix.'emc_operators"';
		$sql[] = 'SHOW TABLES LIKE "'.$wpdb->prefix.'emc_services"';
		$sql[] = 'SHOW TABLES LIKE "'.$wpdb->prefix.'emc_orders"';
		$sql[] = 'SHOW TABLES LIKE "'.$wpdb->prefix.'emc_orders_errors"';
		$sql[] = 'SHOW TABLES LIKE "'.$wpdb->prefix.'emc_tracking"';
		$sql[] = 'SHOW TABLES LIKE "'.$wpdb->prefix.'emc_cache_pricing"';
		$sql[] = 'SHOW TABLES LIKE "'.$wpdb->prefix.'emc_scales"';
		foreach($sql as $query) {
			$wpdb->get_results($query,OBJECT);
			self::handle_sql_error();
			if ($wpdb->num_rows != 1) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Remove the plugin's tables
	 */
	static function delete_database() {
		global $wpdb;
		// delete module tables inserted from create_database
		$sql = array();
		$sql[] = 'DROP TABLE IF EXISTS `'.$wpdb->prefix.'emc_categories`';
		$sql[] = 'DROP TABLE IF EXISTS `'.$wpdb->prefix.'emc_dimensions`';
		$sql[] = 'DROP TABLE IF EXISTS `'.$wpdb->prefix.'emc_operators`';
		$sql[] = 'DROP TABLE IF EXISTS `'.$wpdb->prefix.'emc_services`';
		$sql[] = 'DROP TABLE IF EXISTS `'.$wpdb->prefix.'emc_orders`';
		$sql[] = 'DROP TABLE IF EXISTS `'.$wpdb->prefix.'emc_scales`';

		foreach($sql as $query) {
			$wpdb->query($query,OBJECT);
			if ($wpdb->last_error != '') {
				self::handle_sql_error();
			}
		}
		
		// delete cache
		self::flush_rates_cache();

		// delete configuration
		$query = 'DELETE FROM wp_options where option_name like "EMC_%";';
		
		$wpdb->query($query,OBJECT);
		if ($wpdb->last_error != '') {
			self::handle_sql_error();
		}
		
		return true;
	}
	
	/**
	 * Create the plugin's tables
	 * If there is an error during the tables creation, all the tables are removed
	 * @return true if the creation worked, false otherwise
	 */
	static function create_database() {
		global $wpdb;
		
		// remove tables if last installation failed badly
		self::delete_database();
		
		// create module tables
		$sql = array();
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'emc_categories` (
								`cat_id` int(11) NOT NULL,
								`cat_group` int(11) NOT NULL,
								`cat_name` varchar(100) NOT NULL,
								PRIMARY KEY (`cat_id`)
							) DEFAULT CHARSET=utf8;';
		$sql[] = 'INSERT INTO `'.$wpdb->prefix.'emc_categories` (`cat_id`, `cat_group`, `cat_name`) VALUES
							(10000, 0, "Books and documents"),
							(10100, 10000, "Documents without commercial value"),
							(10120, 10000, "Newspapers"),
							(10130, 10000, "Magazines, journals"),
							(10140, 10000, "Technical manuals"),
							(10150, 10000, "Books"),
							(10160, 10000, "Passports"),
							(10170, 10000, "Plane tickets"),
							(10180, 10000, "X-rays"),
							(10190, 10000, "Photographs"),
							(10200, 10000, "Internal company mail"),
							(10210, 10000, "Business proposals"),
							(10220, 10000, "Promotional materials"),
							(10230, 10000, "Catalogues, annual reports"),
							(10240, 10000, "Computer printouts"),
							(10250, 10000, "Plans, designs"),
							(10260, 10000, "Printed documents"),
							(10280, 10000, "Templates"),
							(10290, 10000, "Labels, stickers"),
							(10300, 10000, "Tender documents");';
		$sql[] = 'INSERT INTO `'.$wpdb->prefix.'emc_categories` (`cat_id`, `cat_group`, `cat_name`) VALUES
							(20000, 0, "Food and perishables"),
							(20100, 20000, "Non-perishable foodstuffs"),
							(20102, 20000, "Fresh and perishable produce"),
							(20103, 20000, "Refrigerated products"),
							(20105, 20000, "Frozen products"),
							(20110, 20000, "Non-alcoholic beverages"),
							(20120, 20000, "Alcoholic beverages"),
							(20130, 20000, "Plants, flowers, seeds"),
							(30000, 0, "Products"),
							(30100, 30000, "Cosmetics, well-being products"),
							(30200, 30000, "Pharmaceuticals, medications"),
							(30300, 30000, "Chemicals, drugs, cleaning products"),
							(50190, 30000, "Tobacco"),
							(50200, 30000, "Perfumes"),
							(40000, 0, "Clothing and accessories"),
							(40100, 40000, "Shoes"),
							(40110, 40000, "Fabrics, new clothes"),
							(40120, 40000, "Used clothes"),
							(40125, 40000, "Clothing/fashion accessories"),
							(40130, 40000, "Leather, skins, leather goods");';
		$sql[] = 'INSERT INTO `'.$wpdb->prefix.'emc_categories` (`cat_id`, `cat_group`, `cat_name`) VALUES
							(40150, 40000, "Costume jewellery"),
							(50160, 40000, "Jewellery, precious objects"),
							(50000, 0, "Equipment and appliances"),
							(50100, 50000, "Medical equipment"),
							(50110, 50000, "IT, high-tech and fixed-line telephony equipment"),
							(50113, 50000, "Mobile telephony and accessories"),
							(50114, 50000, "Televisions, computer screens"),
							(50120, 50000, "Other devices and equipment"),
							(50130, 50000, "Digital media, CD, DVD"),
							(50140, 50000, "Spare parts and accessories (auto)"),
							(50150, 50000, "Spare parts and accessories (other)"),
							(50170, 50000, "Watches, timepieces (excluding jewellery)"),
							(50330, 50000, "Camping and fishing items"),
							(50350, 50000, "Sporting apparel (excluding clothing)"),
							(50360, 50000, "Musical instruments and accessories"),
							(50380, 50000, "Heating and boiler equipment"),
							(50390, 50000, "Laboratory, optical and measuring equipment"),
							(50395, 50000, "Electrical equipment, transformers, cables"),
							(50400, 50000, "Office supplies, stationery, refills"),
							(50420, 50000, "Engines, gearboxes");';
		$sql[] = 'INSERT INTO `'.$wpdb->prefix.'emc_categories` (`cat_id`, `cat_group`, `cat_name`) VALUES
							(50430, 50000, "Motor cycles, scooters"),
							(50440, 50000, "Bicycles, non-motorised cycles"),
							(50450, 50000, "Tooling, tools, DIY equipment"),
							(50490, 50000, "Plumbing equipment, plastic tubes"),
							(50500, 50000, "Hardware, valves, locks"),
							(60000, 0, "Furniture and decoration"),
							(60100, 60000, "Home furniture"),
							(60102, 60000, "Office furniture"),
							(60105, 60000, "Dismantled and packaged furniture"),
							(60108, 60000, "Old (antique) furniture"),
							(60110, 60000, "Household electrical goods"),
							(60112, 60000, "Small household electrical goods, small domestic appliances"),
							(60120, 60000, "Listed objects or paintings, collectible items, mirrors, windows"),
							(60122, 60000, "Works of art and paintings of low value"),
							(60124, 60000, "Lamps, lighting"),
							(60126, 60000, "Carpets"),
							(60128, 60000, "Linens, curtains, sheets"),
							(60129, 60000, "Toilets, glasses, crystal glassware, dishware, ornaments"),
							(60130, 60000, "Other fragile objects and sculptures"),
							(70000, 0, "Personal belongings, gifts");';
		$sql[] = 'INSERT INTO `'.$wpdb->prefix.'emc_categories` (`cat_id`, `cat_group`, `cat_name`) VALUES
							(50180, 70000, "Gifts, corporate gifts"),
							(70100, 70000, "Luggage, suitcases, trunks"),
							(70200, 70000, "Small-scale removals, boxes, personal effects");';
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'emc_dimensions` (
								`dim_id` int(3) NOT NULL AUTO_INCREMENT,
								`dim_length` int(3) NOT NULL,
								`dim_width` int(3) NOT NULL,
								`dim_height` int(3) NOT NULL,
								`dim_weight_from` float NOT NULL,
								`dim_weight` float NOT NULL,
								PRIMARY KEY (`dim_id`)
							)  DEFAULT CHARSET=utf8;';
		$sql[] = 'INSERT INTO '.$wpdb->prefix.'emc_dimensions (`dim_id`, `dim_length`, `dim_width`, `dim_height`, `dim_weight_from`, `dim_weight`) VALUES
							(1, 18, 18, 18, 0, 1),
							(2, 22, 22, 22, 1, 2),
							(3, 26, 26, 26, 2, 3),
							(4, 28, 28, 28, 3, 4),
							(5, 31, 31, 31, 4, 5),
							(6, 33, 33, 33, 5, 6),
							(7, 34, 34, 34, 6, 7),
							(8, 36, 36, 36, 7, 8),
							(9, 37, 37, 37, 8, 9),
							(10, 39, 39, 39, 9, 10),
							(11, 44, 44, 44, 10, 15),
							(12, 56, 56, 56, 15, 20),
							(13, 57, 57, 57, 20, 50);';
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'emc_operators` (
								`ope_id` int(2) NOT NULL AUTO_INCREMENT,
								`ope_name` varchar(100) NOT NULL,
								`ope_code` char(4) NOT NULL,
								PRIMARY KEY (`ope_id`)
							)  DEFAULT CHARSET=utf8;';
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'emc_services` (
								`srv_id` int(11) NOT NULL AUTO_INCREMENT,
								`srv_ope_id` int(11) NOT NULL,
								`srv_id_wc_carrier` int(11) NOT NULL DEFAULT 0,
								`srv_code` varchar(40) NOT NULL,
								`srv_name` varchar(100) NOT NULL,
								`srv_description` varchar(150) NOT NULL,
								`srv_name_bo` varchar(100) NOT NULL,
								`srv_description_bo` varchar(150) NOT NULL,
								`srv_pickup_point` int(1) NOT NULL,
								`srv_dropoff_point` int(1) NOT NULL,
								`srv_family` int(1) NOT NULL,
								`srv_zone` int(1) NOT NULL,
								PRIMARY KEY (`srv_id`)
							) DEFAULT CHARSET=utf8;';
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'emc_orders` (
								`ord_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
								`ord_id_wp_order` int(10) unsigned NOT NULL,
								`ord_ope_id` int(10) unsigned NOT NULL,
								`ord_srv_id` int(10) unsigned NOT NULL,
								`ord_price_ht` float NOT NULL,
								`ord_price_ttc` float NOT NULL,
								`ord_ref_ope` varchar(20) NOT NULL,
								`ord_ref_emc` char(20) NOT NULL,
								`ord_info` varchar(20) NOT NULL,
								`ord_date_order` datetime NOT NULL,
								`ord_date_collect` datetime NOT NULL,
								`ord_date_del` datetime NOT NULL,
								`ord_date_del_real` datetime NOT NULL,
								`ord_tracking` CHAR(255) NOT NULL,
								`ord_parcel` INT(4) NOT NULL,
								`ord_base_url` VARCHAR(255) NOT NULL,
								PRIMARY KEY (`ord_id`)
							) DEFAULT CHARSET=utf8;';
		$sql[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'emc_scales` (
								 	`id` int(11) NOT NULL AUTO_INCREMENT,
								  `shipping_method` varchar(255) DEFAULT NULL,
								  `rate_from` float DEFAULT NULL,
								  `rate_to` float DEFAULT NULL,
								  `country` varchar(255) DEFAULT NULL,
								  `zone` varchar(45) DEFAULT NULL,
								  `type` varchar(45) DEFAULT NULL,
								  `rate_price` float DEFAULT NULL,
								  PRIMARY KEY (`id`)
								) DEFAULT CHARSET=utf8;';
		

		foreach($sql as $query) {
			$wpdb->query($query,OBJECT);
			if ($wpdb->last_error != '') {
				self::handle_sql_error();
				self::delete_database();
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Default options
	 *
	 * Sets up the default options used on the settings page
	 *
	 * @access public
	 */
	static function create_options() {
		// Include settings so that we can run through defaults
		include_once( 'includes/admin/class-emc-admin-settings.php' );

		// to get the client module version of EMC
		add_option('EMC_VERSION',EMC_VERSION);

		$settings = emc_admin_settings::get_settings_pages();

		foreach ( $settings as $section ) {
			if ( ! method_exists( $section, 'get_settings' ) ) {
				continue;
			}

			$section_settings = $section->get_settings();

			foreach ( $section_settings as $value ) {
				if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
					$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
					add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
				}
			}
		}
		return true;
	}

	/**************************/
	/*********** DB ***********/
	/**************************/
	
	/**
	 * @return list of all shipping categories
	 */
	static function get_categories() {
		global $wpdb;
		$result = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'emc_categories WHERE cat_group != 0',OBJECT);
		self::handle_sql_error();
		return $result;	
	}
	
	/**
	 * @return list of all emc services
	 */
	static function get_services() {
		global $wpdb;
		$result = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'emc_services AS services JOIN '.$wpdb->prefix.'emc_operators AS operators ON srv_ope_id = ope_id',OBJECT);
		self::handle_sql_error();
		return $result;
	}
	
	/**
	 * @return list of all emc operators
	 */
	static function get_operators() {
		global $wpdb;
		$result = $wpdb->get_results( 'SELECT * FROM '.$wpdb->prefix.'emc_operators', OBJECT );
		self::handle_sql_error();
		return $result;
	}
	
	/**
	 * Get all enable shipping methods
	 * @param $emc_only : true if only envoimoinscher carriers are wanted
	 * @return list of all enabled shipping methods
	 */
	static function get_enabled_shipping_methods( $emc_only = true ) {
		$shipping_methods = WC()->shipping->load_shipping_methods();
		$enabled_methods = array();
		
		foreach( $shipping_methods as $method ) {
			if ( $method->enabled == 'yes' ) {
				if ( $emc_only && self::get_service_by_carrier_code( $method->id ) ) { 
					array_push( $enabled_methods, $method->id );
				}
				elseif ( $emc_only ) {}
				else { 
					array_push( $enabled_methods, $method->id ); 
				}
			}
		}
		
		return $enabled_methods;
	}
	
	/**
	 * Get all emc active shipping methods
	 * @return list of all emc active shipping methods
	 */
	static function get_emc_active_shipping_methods() {
		
		$emc_services = get_option('EMC_SERVICES');
		
		$active_methods = array();
		
		if (is_array($emc_services)) {
			foreach($emc_services[1] as $value) {
				array_push($active_methods, $value);
			}
			foreach($emc_services[2] as $value) {
				array_push($active_methods, $value);
			}
		}
		
		return $active_methods;
	}
	
	/**
	 * Get a service by his id
	 * @param $id : service id
	 * @return the asked service
	 */
	static function get_service_by_id($id) {
		global $wpdb;
		$result = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'emc_services WHERE srv_id = %d',array($id)),OBJECT);
		self::handle_sql_error();
		return $result;
	}
	
	/**
	 * Get a service by his carrier code
	 * @param $carrier_code : operator_service (ex: MONR_CpourToi)
	 * @return the asked service if it exists or false if not
	 */
	static function get_service_by_carrier_code($carrier_code) {
		global $wpdb;
		$carrier = explode("_", $carrier_code);
		if ( count($carrier) != 2 ) return false;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'emc_services JOIN '.$wpdb->prefix.'emc_operators ON srv_ope_id = ope_id WHERE ope_code = %s AND srv_code = %s',array($carrier[0], $carrier[1]));
		$result = $wpdb->get_results($sql,OBJECT);
		self::handle_sql_error();
		return isset( $result[0] ) ? $result[0] : false;
	}
	
	/**
	 * Checks if a service is emc
	 * @param $service
	 * @return boolean
	 */
	static function is_emc_service($service) {
		if( !self::get_service_by_carrier_code($service)){
			return false;
		}
		else {
			return true;
		}
	}
	
	/**
	 * Get an operator from a service's id
	 * @param $id : id of the service associated to the operator
	 * @return the operator associated to the given service
	 */
	static function get_operator_by_service_id($id) {
		global $wpdb;
		$result = $wpdb->get_results($wpdb->prepare('SELECT o.* FROM '.$wpdb->prefix.'emc_operators o join '.$wpdb->prefix.'emc_services s on o.ope_id = s.srv_ope_id where srv_id = %d',array($id)),OBJECT);
		self::handle_sql_error();
		return $result;
	}
	/**
	 * Get an operator from his code
	 * @param $code : operator's code
	 * @return the operator associated to the given code
	 */
	static function get_operator_by_code($code) {
		global $wpdb;
		$result = $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'emc_operators o WHERE o.ope_code = %s',array($code)),OBJECT);
		self::handle_sql_error();
		return $result;
	}
	
	/**
	 * Insert / update scales rates
	 * @params array : post values from admin carrier
	 * @return void
	 */
	static function save_scale_data($params)
	{
		global $wpdb;
		$shipping_method 	= $params['shipping_method'];
		$type 						= $params['type'];
		// We delete carrier scale info before inserting	
		$wpdb->query('DELETE FROM '.$wpdb->prefix.'emc_scales WHERE shipping_method = "'.$shipping_method.'" AND type = "'.$type.'" ');
		// And we insert scales infos
		$lines = count ($params['rate_from']);
		for( $i = 0 ; $i < $lines; $i++){
			$wpdb->query('INSERT INTO '.$wpdb->prefix.'emc_scales (shipping_method, rate_from, rate_to, country, zone, type, rate_price) 
			VALUES ("'.$shipping_method.'" , '.$params['rate_from'][$i].', '.$params['rate_to'][$i].',"" ,"" , "'.$type.'", '.$params['rate_price'][$i].') ' );
		}
	}
	/**
	 * Get scales rates
	 * @params string $shipping_method
	 * @params string $type : weight / price 
	 * @return array
	 */
	static function get_scale_data($shipping_method, $type)
	{
		global $wpdb;	
		$result = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'emc_scales WHERE shipping_method = "'.$shipping_method.'" AND type = "'.$type.'" ',ARRAY_A);
		self::handle_sql_error();
		return $result;
	}
	/**
	* Gets all dimensions.
	* @return array List with dimensions.
	*/
	static function get_dimensions()
	{
		global $wpdb;
		$result = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'emc_dimensions',OBJECT);
		self::handle_sql_error();
		return $result;
	}
	
	/**
	 * Get the dimensions to use for a parcel's weight
	 * @param $weight : weight of the parcel
	 * @return dimensions to use with the parcel
	 */
	static function get_dim_from_weight($weight) {
		global $wpdb;
		$result = $wpdb->get_results($wpdb->prepare('SELECT dim_length AS length, dim_width AS width, dim_height AS height FROM '.$wpdb->prefix.'emc_dimensions WHERE dim_weight_from < %f AND dim_weight >= %f',array($weight,$weight)),OBJECT);
		self::handle_sql_error();
		return $result[0];
	}
	
	/**
	* Updates configured dimensions.
	* @param array $data New dimensions data.
	* @param int $id Id of dimensions to update.
	* @return void
	*/
	static function updateDimensions($data, $id)
	{
		global $wpdb;
		
		$where = array( 
			'dim_id' => $id,
		);		
		
		$wpdb->update( $wpdb->prefix.'emc_dimensions', $data, $where);
		self::handle_sql_error();
	}
	
	/**
	 * Get the weight from order
	 * @param $order_id
	 * @return weigth in kg
	 */
	static function get_weight_from_order($order_id) {

		$order = WC()->order_factory->get_order($order_id);
		
		$total_weight = 0;
		foreach( $order->get_items( 'line_item' ) as $value ) {
			$product_id = ( !empty($value['variation_id']) ? $value['variation_id'] : $value['product_id'] );
			$product_weight=self::get_product_weight($product_id);
			$total_weight += (int)$value['qty'] * (float)$product_weight;
		}
		return (float)$total_weight;
	}
	
	/**
	 * Get the parcel description from order
	 * @param $order_id
	 * @return content description
	 */
	static function get_description_from_order($order_id) {

		$order = WC()->order_factory->get_order($order_id);
		
		// if default content description is defined in settings
		if( get_option('EMC_CONTENT_AS_DESC') == 'yes' && get_option('EMC_NATURE') != '' ) {
			global $wpdb;
			$results = $wpdb->get_results( 'SELECT cat_name FROM '.$wpdb->prefix.'emc_categories WHERE cat_id = '.$wpdb->prepare('%s', get_option('EMC_NATURE')) ,OBJECT);
			self::handle_sql_error();
			return $results[0]->cat_name;
		}
		// else concatenate product info
		else {
			$description = '';
			foreach( $order->get_items( 'line_item' ) as $value ) {
				$product  = wc_get_product( $value['product_id'] );
				$description .= $product->get_title() . ';';
			}
			return $description;
		}
	}
	
	/**
	 * Load all available carriers
	 */
	static function load_carrier_list_api($ajax = true)
	{	
		global $wpdb;
		$result = array();
		
		// check services already installed
		$services = self::get_services();
		$operators = self::get_operators();

		// get services from envoimoinscher server
		require_once('env/WebService.php');
		require_once('env/CarriersList.php');
		$login = get_option('EMC_LOGIN');
		$pass = get_option('EMC_PASS');
		$key = get_option('EMC_KEY');
		$env = get_option('EMC_ENV');
		$lib = new EnvCarriersList(array('user' => $login, 'pass' => $pass, 'key' => $key));
		$lib->setPlatformParams(EMC_PLATFORM, WC_VERSION, EMC_VERSION);
		$lib->setEnv($env);
		$upload_dir = wp_upload_dir();
    $lib->setUploadDir($upload_dir['basedir']);
		$lib->getCarriersList(EMC_PLATFORM, EMC_VERSION);

		if ($lib->curl_error) {
			$return = __('An error occurred while updating your carrier list: ', 'envoimoinscher');
			foreach ($lib->resp_errors_list as $message) {
				$return .= '<br />'.$message['message'];
			}
			if ($ajax) {
				ob_end_clean();
				echo $return;
				die();
			}
			else {
				return $return;
			}
		}
		else if ($lib->resp_error) {
			$return = __('An error occurred while updating your carrier list: ', 'envoimoinscher');
			foreach ($lib->resp_errors_list as $message) {
				$return .= '<br />'.$message['message'];
			}
			if ($ajax) {
				ob_end_clean();
				echo $return;
				die();
			}
			else
				return $return;
		}
		
		$ope_no_change = array();
		$ope_to_delete = array();
		$ope_to_update = array();
		$ope_to_insert = array();
		$srv_no_change = array();
		$srv_to_delete = array();
		$srv_to_update = array();
		$srv_to_insert = array();

		$op_found = -1;
		$srv_found = -1;
			
		
		// sort transporters to add, to delete, to modify or to leave as is
		$last_ope_seen = ''; // in order to discard duplicates
		foreach ($lib->carriers as $carrier) {

			// we check if operator is different
			if ($last_ope_seen != $carrier['ope_code']) {
				$last_ope_seen = $carrier['ope_code'];
				// we compare the operator with the one in the list
				$op_found = -1;
				foreach ($operators as $id => $operator) {
					if ($operator->ope_code == $carrier['ope_code']) {
						$op_found = $id;
						if ($operator->ope_name != $carrier['ope_name'])
							$ope_to_update[count($ope_to_update)] = $carrier;
						else
							$ope_no_change[count($ope_no_change)] = $carrier;
						break;
					}
				}
				if ($op_found == -1)
					$ope_to_insert[count($ope_to_insert)] = $carrier;
				else
					unset($operators[$op_found]);
			}

			// we compare the service with the one in the list
			$srv_found = -1;
			foreach ($services as $id => $service) { 
				if ($service->ope_code == $carrier['ope_code'] && $service->srv_code == $carrier['srv_code'])	{
					$srv_found = $id;
					// we check if service is different
					if ($service->srv_name_bo != $carrier['srv_name'] ||
							$service->srv_name != $carrier['description_store'] ||
							$service->srv_description_bo != $carrier['label_store'] ||
							$service->srv_description != $carrier['description'] ||
							$service->srv_pickup_point != $carrier['parcel_pickup_point'] ||
							$service->srv_dropoff_point != $carrier['parcel_dropoff_point'] ||
							$service->srv_family != $carrier['family'] ||
							$service->srv_zone != $carrier['zone'])
						$srv_to_update[] = $carrier;
					else
						$srv_no_change[] = $carrier;
					break;
				}
			}
			if ($srv_found == -1)
				$srv_to_insert[] = $carrier;
			else
				unset($services[$srv_found]);
		}

		$srv_to_delete = $services;
		$ope_to_delete = $operators;

		
		// We update the database
		// Insert operators request
		$query1 = array();
		$sql = '';
		$first_line = true;
		if (count($ope_to_insert) > 0) {
			$sql = 'INSERT INTO '.$wpdb->prefix.'emc_operators VALUES';
			foreach ($ope_to_insert as $operator) {
				if (!$first_line)
					$sql .= ',';
				$first_line = false;
				$sql .= '(null,'.$wpdb->prepare('%s', $operator['ope_name']).','.$wpdb->prepare('%s', $operator['ope_code']).')';
			}
			$sql .= ';';
			$query1[] = $sql;
		}
		
		// Requête update operateurs
		foreach ($ope_to_update as $operator) {
			$sql = 'UPDATE '.$wpdb->prefix.'emc_operators SET
				 ope_name = '.$wpdb->prepare('%s', $operator['ope_name']).' WHERE ope_code = '.$wpdb->prepare('%s', $operator['ope_code']).';';
			$query1[] = $sql;
		}
		
		// Requête delete operateurs
		$first_line = true;
		if (count($ope_to_delete) > 0) {
			$sql = 'DELETE FROM '.$wpdb->prefix.'emc_operators WHERE ';
			foreach ($ope_to_delete as $operator)	{
				if (!$first_line) {
					$sql .= ' OR ';
				}
				$first_line = false;
				$sql .= 'ope_id = '.(int)$operator->ope_id;
			}
			$sql .= ';';
			$query1[] = $sql;
		}

		// We need to update operators first in order to get ope_id on services update
		foreach($query1 as $request) {
			$wpdb->query($request,OBJECT);
			if ($wpdb->last_error != '') {
				self::handle_sql_error();
				return $wpdb->last_error;
			}
		}

		$query2 = array();
		
		$ope_ids = array();
		$new_operators = self::get_operators();
		foreach($new_operators as $value) {
			$ope_ids[$value->ope_code] = $value->ope_id;
		}
		
		// Insert services request
		if (count($srv_to_insert) > 0) {
			$sql = 'INSERT INTO '.$wpdb->prefix.'emc_services VALUES';
			$first_line = true;
			foreach ($srv_to_insert as $service) {
				if (!$first_line)
					$sql .= ',';
				$first_line = false;

				$sql .= '(null,'.$wpdb->prepare('%d',$ope_ids[$service['ope_code']]).',0,'.$wpdb->prepare('%s', $service['srv_code']).
															','.$wpdb->prepare('%s', $service['description_store']).
															','.$wpdb->prepare('%s', $service['description']).
															','.$wpdb->prepare('%s', $service['srv_name']).
															','.$wpdb->prepare('%s', $service['label_store']).
															','.(int)$service['parcel_pickup_point'].
															','.(int)$service['parcel_dropoff_point'].
															','.(int)$service['family'].
															','.(int)$service['zone'].
															')';
			}
			$sql .= ';';
			$query2[] = $sql;
		}

		// Requête update services
		foreach ($srv_to_update as $service) {
			$sql = 'UPDATE '.$wpdb->prefix.'emc_services SET
										 srv_name_bo = '.$wpdb->prepare('%s', $service['srv_name']).'
										 ,srv_description_bo = '.$wpdb->prepare('%s', $service['label_store']).'
										 ,srv_description = '.$wpdb->prepare('%s', $service['description']).'
										 ,srv_name = '.$wpdb->prepare('%s', $service['description_store']).'
										 ,srv_pickup_point = '.(int)$service['parcel_pickup_point'].'
										 ,srv_dropoff_point = '.(int)$service['parcel_dropoff_point'].'
										 ,srv_family = '.(int)$service['family'].'
										 ,srv_zone = '.(int)$service['zone'].'
										 WHERE srv_code = '.$wpdb->prepare('%s', $service['srv_code']).'
										 AND srv_ope_id = '.$wpdb->prepare('%d',$ope_ids[$service['ope_code']]).';';
			$query2[] = $sql;
		}
		
		// Requête delete services
		if (count($srv_to_delete) > 0) {
			/*$sql = 'UPDATE '.$wpdb->prefix.'carrier SET deleted = 1 WHERE ';
			$first_line = true;
			foreach ($srv_to_delete as $service)
			{
				if (!$first_line)
					$sql .= ' OR ';
				$first_line = false;
				$sql .= 'id_carrier = '.(int)$service['id_carrier'];
			}
			$sql .= ';';
			$query[] = $sql;*/
			$sql = 'DELETE FROM '.$wpdb->prefix.'emc_services WHERE ';
			$first_line = true;
			foreach ($srv_to_delete as $service) {
				if (!$first_line)
					$sql .= ' OR ';
				$first_line = false;
				$sql .= 'srv_id = '.(int)$service->srv_id;
			}
			$sql .= ';';
			$query2[] = $sql;
		}

		foreach($query2 as $request) {
			$wpdb->query($request,OBJECT);
			if ($wpdb->last_error != '') {
				self::handle_sql_error();
				return $wpdb->last_error;
			}
		}

		$result = array();
		$result['offers_added'] = array();
		$result['offers_updated'] = array();
		$result['offers_deleted'] = array();
		foreach ($srv_to_insert as $service) {
			$result['offers_added'][count($result['offers_added'])] = $service['srv_name'];
		}
		foreach ($srv_to_update as $service) {
			$result['offers_updated'][count($result['offers_updated'])] = $service['srv_name'];
		}
		foreach ($srv_to_delete as $service) {
			$result['offers_deleted'][count($result['offers_deleted'])] = $service->srv_name_bo;
		}
		
		$date = new DateTime();
		update_option( 'EMC_LAST_CARRIER_UPDATE', $date->format('Y-m-d') );
		
		if ($ajax) {
			ob_end_clean();
			echo jsonEncode($result);
			die();
		}
		else
			return true;
	}
	
	/**
	 * Get all parcel points for an adress and a carrier
	 * 
	 *
	 */
	static function get_pickup_points($carrier_code,$city,$postal_code,$country) {
		
		$env = get_option('EMC_ENV');
		$parcels_code = 'emc_parcels_'.md5($carrier_code.$city.$postal_code.$country.$env);
		$parcels = get_transient($parcels_code);
		
		if ($parcels) {
			return $parcels;
		}
		
		require_once('env/WebService.php');
		require_once('env/ListPoints.php');
		$login = get_option('EMC_LOGIN');
		$pass = get_option('EMC_PASS');
		$key = get_option('EMC_KEY');
		
		$carrier_info = explode('_',$carrier_code);
		
		$params = array(
			'srv_code' => $carrier_info[1],
			'collecte'=> 'dest',
			'pays' => $country,
			'cp' => $postal_code,
			'ville' => $city
		);
		
		$lib = new EnvListPoints(array('user' => $login, 'pass' => $pass, 'key' => $key));
		$lib->setEnv($env);
		$upload_dir = wp_upload_dir();
    $lib->setUploadDir($upload_dir['basedir']);
		$lib->setPlatformParams(EMC_PLATFORM, WC_VERSION, EMC_VERSION);
		
		$lib->getListPoints($carrier_info[0], $params);
		
		if($lib->curl_error || $lib->resp_error) {
			self::log('Failed to load parcel points for "'.$carrier_code.'" for the address {city="'.$city.'",country="'.$country.'",postal_code="'.$postal_code.'"}');
			if ($lib->resp_error) {
				self::log('The library return the error : '.print_r($lib->resp_errors_list,true));
			} else if($lib->curl_error) {
				self::log('Curl return the error : '.$lib->curl_error_text);
			}
			return array();
		}
		else {
			set_transient($parcels_code, $lib->list_points, 3600);
			return $lib->list_points;
		}
	}
	
	/**
	 * Get offers from cache.
	 * @param array $params API request params.
	 * @returns md5 of relevant order params
	 */
	static function get_pricing_code($from, $to, $parcels, $params, $add_activated_services) {	
		
		$code  = isset($from['code_postal']) ? $from['code_postal'] : '';
		$code .= isset($from['ville'])       ? $from['ville'] : '';
		$code .= isset($from['pays'])        ? $from['pays'] : '';
		$code .= isset($from['type'])        ? $from['type'] : '';
		$code .= isset($to['code_postal']) ? $to['code_postal'] : '';
		$code .= isset($to['ville'])       ? $to['ville'] : '';
		$code .= isset($to['pays'])        ? $to['pays'] : '';
		$code .= isset($to['type'])        ? $to['type'] : '';
		$code .= serialize($parcels);
		$code .= serialize($params);
		$code .= $add_activated_services ? '1' : '0';
		
		return 'emc_quote_'.md5($code);
	}

	/**
	 * Return the quotation for the informations given
	 *
	 * @param $from    : departure address
	 * @param $to      : delivery address
	 * @param $parcels : parcel configuration
	 * @param $params  : additional parameters
	 * @param $add_activated_services : true : the function will do a multirequest on all activated service, else it will do a simple request
	 * @param $cache : true : if cache should be used
	 * @return mixed : if the quotation worked, return an array with all the available offers, if there is an error, return a string with the error message
	 */
	static function get_quotation($from, $to, $parcels, $params, $add_activated_services = true, $cache = true) {
		// Get quotation pricing code
		$pricing_code = self::get_pricing_code($from, $to, $parcels, $params, $add_activated_services);
		if($cache){
			$offers = get_transient($pricing_code);
		}
		else{
			$offers = false;
		}
		
		self::log('------------------------------------------------------------------------------------------------');
		self::log('Quotation - from '.$from['ville'].' '.$from['code_postal']. ' to '.$to['ville'].' '.$to['code_postal']);
		self::log('Quotation - pricing code : '.$pricing_code);
		if ($add_activated_services){
			self::log('Quotation - this is a quotation on all activated services');
		}
		else{
			self::log('Quotation - this is a quotation on '.$params['operator'].'_'.$params['service']);
		}
		
		// We already did this quotation, no need to do it again
		if ($offers) {
			self::log('Quotation - cache used.');
			return $offers;
		}
		
		// Create quotation object
		require_once('env/WebService.php');
		require_once('env/Quotation.php');
		$lib = new EnvQuotation(array('user' => get_option('EMC_LOGIN'), 'pass' => get_option('EMC_PASS'), 'key' => get_option('EMC_KEY')));
		$lib->setEnv(get_option('EMC_ENV'));
		$upload_dir = wp_upload_dir();
    $lib->setUploadDir($upload_dir['basedir']);
		$lib->setPlatformParams(EMC_PLATFORM, WC_VERSION, EMC_VERSION);

		// Initialize the quotation
		$lib->setPerson('expediteur', $from);
		$lib->setPerson('destinataire', $to);
		$lib->setType('colis', $parcels);

		// Do we need to add activated services ?
		if (!$add_activated_services) {
			$lib->getQuotation($params);
			$lib->getOffers(false);
		}
		else {
			$services = self::get_emc_active_shipping_methods();

			if (count($services) == 0){
				self::log('Quotation - No service activated');
				return $offers;
			}
			
			foreach ($services as $carrier_code)
			{
				list($operator, $service) = explode('_', $carrier_code);
				$params['operator'] = $operator;
				$params['service'] = $service;
				$lib->setParamMulti($params);
			}

			$lib->getQuotationMulti();
			$lib->getOffersMulti();
		}
		
		if(!$lib->curl_error) {
			if(!$lib->resp_error) {
				// Incredible, it worked
			
				$offers = array();
				$activated_services = get_option('EMC_SERVICES');
				
				foreach($lib->offers as $offer) { 	
					$carrier_code = $offer['operator']['code'].'_'.$offer['service']['code'];
					
					// only take offer if it is activated, and only once
					if(in_array($carrier_code, $activated_services[1])){
						foreach( $activated_services[1] as $key => $value ){
							if($value == $carrier_code) unset($activated_services[1][$key]);
						}
						$offers[$carrier_code] = $offer;									
					}
					elseif(in_array($carrier_code, $activated_services[2])){
						foreach( $activated_services[2] as $key => $value ){
							if($value == $carrier_code) unset($activated_services[1][$key]);
						}
						$offers[$carrier_code] = $offer;									
					}
				}
				
				set_transient($pricing_code, $offers, 4 * WEEK_IN_SECONDS );
				self::log('Quotation - '.count($offers). ' offer(s) found');
				return $offers;
			}
			else
			// Request error
			{
				$message = 'Invalid request : ';
				foreach($lib->resp_errors_list as $key => $error_message){
					if ($key > 0) {
						$message .= '<br/>';
					}
					$message .= $error_message['message'].' ('.$error_message['code'].')';
				}
				self::log('Quotation - result : '.$message);
				return  $message;
			}
		}
		else
		// cURL error
		{
			$message =  'Invalid request : ' . $lib->curl_error_text;
			self::log('Quotation - result : '.$message);
			return $message;
		}
	}
	
	/**
	 * Return article weight
	 * @return if exist, return article weight
	 */
	static function get_product_weight($product_id) {
		if( isset( $product_id ) && !empty( $product_id ) ) {
			$product  = wc_get_product( $product_id );
			if( $product->get_weight() && $product->get_weight() != '' ) {
				$weight = wc_format_decimal( wc_get_weight($product->get_weight(),'kg'), 2 );
			}
			else{
				$weight = get_option('EMC_AVERAGE_WEIGHT');
			}
			return $weight;
		}
	}
		
	/**
	 * Return the sum of all articles weight
	 * @return cart's weight
	 */
	static function get_cart_weight() {
		$weight = 0;
		foreach ( WC()->cart->get_cart() as $item_id => $values ) {
			$_product = $values['data'];
			$product_id = ( !empty($values['variation_id']) ? $values['variation_id'] : $_product->id );
			if($_product->needs_shipping()){
				$weight += self::get_product_weight($product_id)*$values['quantity'];
			}
		}
		return $weight;
	}
	
	/**
	 * @return sender's address
	 */
	static function get_sender_address() {
		return array(
			'pays'				=> 'FR',
			'code_postal'	=> get_option('EMC_POSTALCODE'),
			'ville'				=> get_option('EMC_CITY'),
			'type'				=> 'entreprise',
			'societe'			=> get_option('EMC_COMPANY'),
			'adresse'			=> get_option('EMC_ADDRESS'),
			'civilite'		=> get_option('EMC_CIV'),
			'prenom'			=> get_option('EMC_FNAME'),
			'nom'					=> get_option('EMC_LNAME'),
			'email'				=> get_option('EMC_MAIL'),
			'tel'					=> self::normalizeTelephone(get_option('EMC_TEL')),
			'infos'				=> get_option('EMC_COMPL')
		);
	}
	
	/**
	 * @return recipient's address for the current cart
	 */
	static function get_recipient_address() {
		$address 	= WC()->customer->get_shipping_address();
		$postcode = WC()->customer->get_shipping_postcode();	
		$city 		= WC()->customer->get_shipping_city();
		$country	= WC()->customer->get_shipping_country();
	
		return array(
			'prenom' 			=> wp_get_current_user()->user_firstname,
			'nom' 				=> wp_get_current_user()->user_lastname,
			'email' 			=> wp_get_current_user()->user_email,
			'adresse' 		=> !empty($address) 	?  $address 	: '15 rue Marsollier',
			'code_postal' => !empty($postcode) 	?  $postcode 	: '75002',
			'ville' 			=> !empty($city) 			?  $city  		: 'Paris',		
			'pays' 				=> !empty($country) 	?  $country  	: 'FR',
			'type'				=> 'particulier'
		);
	}
	
	/**
	 * Processes orders.
	 * @param array $order.
	 * @returns boolean
	 */
	static function make_order( $order ) {
		
		include_once('env/WebService.php');
		include_once('env/Quotation.php');
		
		$cotCl = new EnvQuotation(array('user' => get_option('EMC_LOGIN'), 'pass' => get_option('EMC_PASS'), 'key' => get_option('EMC_KEY')));
		$cotCl->setEnv( get_option( 'EMC_ENV' ) );
		$upload_dir = wp_upload_dir();
    $cotCl->setUploadDir($upload_dir['basedir']);
		$cotCl->setPlatformParams(EMC_PLATFORM, WC_VERSION, EMC_VERSION);
		
		self::initialize_default_params( $order );
		
		self::log('------------------------------------------------------------------------------------------------');
				
		$quot_info = self::get_order_params( $order );
		
		if ( get_post_meta( $order->id, '_insurance', true ) ) {
			$quot_info['params']['assurance.selected'] 		= 1;
			$quot_info['params']['assurance.emballage'] 	= get_post_meta( $order->id, '_assurance_emballage'		, true );
			$quot_info['params']['assurance.materiau'] 		= get_post_meta( $order->id, '_assurance_materiau'		, true );
			$quot_info['params']['assurance.protection'] 	= get_post_meta( $order->id, '_assurance_protection'	, true );
			$quot_info['params']['assurance.fermeture'] 	= get_post_meta( $order->id, '_assurance_fermeture'		, true );		
		}
		else{
			$quot_info['params']['assurance.selected'] = 0;
		}
				
		$cotCl->setPerson('expediteur', $quot_info['from']);
		$cotCl->setPerson('destinataire', $quot_info['to']);
		$cotCl->setType('colis', $quot_info['parcels']);
		
		// add proforma
    $proforma = array();
    
    // Get order items
		$items = $order->get_items();
    $i = 1;
    foreach ( $items as $item ) {
      $proforma[$i] = array(
        "description_en"  => get_post_meta( $order->id, '_proforma_' . $i . '_post_title_en', true ),
        "description_fr"  => get_post_meta( $order->id, '_proforma_' . $i . '_post_title_fr', true ),
        "nombre"          => get_post_meta( $order->id, '_proforma_' . $i . '_qty', true ),
        "valeur"          => (float)get_post_meta( $order->id, '_proforma_' . $i . '_price', true ),
        "origine"         => get_post_meta( $order->id, '_proforma_' . $i . '_origin', true ),
        "poids"           => (float)get_post_meta( $order->id, '_proforma_' . $i . '_weight', true ) - 0.01
      );
      $i++;
    }
    $quot_info['params']['raison'] = get_post_meta( $order->id, '_proforma_reason', true );
    $cotCl->setProforma($proforma);
		
		self::log('Make order - from '.$quot_info['from']['ville'].' '.$quot_info['from']['code_postal']. ' to '.$quot_info['to']['ville'].' '.$quot_info['to']['code_postal']);
		
		$orderPassed = $cotCl->makeOrder($quot_info['params']); 
				
		if(!$cotCl->curl_error) {
			if(!$cotCl->resp_error) {
				// success
				update_post_meta( $order->id, '_emc_ref', $cotCl->order['ref'] );
				$order->update_status( 'wc-awaiting-shipment', sprintf(__( 'Order passed with the reference %s.', 'envoimoinscher' ), $cotCl->order['ref'] ) );			
				self::log('Make order - '.print_r($cotCl->order,true));
				return $cotCl->order;
			}
			else {
				$message = __('Invalid request: ');
				foreach($cotCl->resp_errors_list as $key => $error_message){
					if ($key > 0) {
						$message .= ', ';
					}
					$message .= $error_message['message'].' ('.$error_message['code'].')';
				}
				self::log('Make order - '.$message);
				return $message;
			}
		}
		else{	
			// cURL error
			$message =  __('Invalid request: ') . $cotCl->curl_error_text;
			self::log('Make order - '.$message);
			return $message;
		}
	}
	
	/**
	 * redirect to order details with custom emc error message 
	 * @param string $emc_error_message
	 * @return void
	 */
	static function error_order_details_redirect( $emc_error_message ){
		global $post_id;		
		$query_string = array('emc_mess' => $emc_error_message, 'emc_notice' => 14, 'post' => $post_id, 'action' => 'edit');
		$redirect_url = $_SERVER['REQUEST_URI'].'?'.http_build_query($query_string);
		header('location: '.$redirect_url);
		exit;
	} 
	/**
	 * Check rates for order.
	 * @param array $offer.
	 * @returns rate
	 */
	static function check_rates_from_order ( $order ) {

		self::initialize_default_params( $order );
		
		$quot_info = self::get_order_params( $order );
		
		// insurance needs to be off in order to get select options
		$quot_info['params']['assurance.selected'] = 0;
		
		// if collect date is set in the past, set it to today, otherwise, leave it in the future
		$planned_date = strtotime($quot_info['params']['collecte']);
		if ( $planned_date < time() ) {
			$quot_info['params']['collecte'] = date('Y-m-d');
		}

		self::log('Quot info - '.print_r($quot_info,true));
		
		return self::get_quotation($quot_info['from'], $quot_info['to'], $quot_info['parcels'], $quot_info['params'], true);
	}
	
	/**
	 * Initialize default params for order.
	 * @param array $order.
	 * @returns array API params
	 */
	static function initialize_default_params( $order ) {

		if ( !get_post_meta( $order->id, '_dims_weight' ) || get_post_meta( $order->id, '_dims_weight', true ) == '') {
			$weight = self::get_weight_from_order($order->id);
			
			if( $weight != 0) {
				$dims = self::get_dim_from_weight($weight);
				update_post_meta( $order->id, '_dims_weight', $weight );
				update_post_meta( $order->id, '_dims_width', $dims->width );
				update_post_meta( $order->id, '_dims_length', $dims->length );
				update_post_meta( $order->id, '_dims_height', $dims->height );
			}
			else{
				self::log('initialize_default_params: weight is null!');
			}
		}
		
		if ( !get_post_meta( $order->id, '_desc_content' ) || get_post_meta( $order->id, '_desc_content', true ) == '' ) {
			update_post_meta( $order->id, '_desc_content', self::get_description_from_order($order->id) );
		}
		
		if ( !get_post_meta( $order->id, '_desc_value' ) || get_post_meta( $order->id, '_desc_value', true ) == '' ) {
			update_post_meta( $order->id, '_desc_value', $order->get_subtotal() );
		}
		
		if ( !get_post_meta( $order->id, '_dropoff_point' ) || get_post_meta( $order->id, '_dropoff_point', true ) == '' ) {
			foreach ($order->get_shipping_methods() as $key => $value ) {
				$carrier_code = $value['method_id'];
			}
			$carrier_settings = get_option('woocommerce_'.$carrier_code.'_settings');
			$dropoff_point = isset($carrier_settings['default_dropoff_point']) ? $carrier_settings['default_dropoff_point'] : 'POST';
			update_post_meta( $order->id, '_dropoff_point', $dropoff_point );
		}
		
		if ( !get_post_meta( $order->id, '_pickup_date' ) || get_post_meta( $order->id, '_pickup_date', true ) == '' ) {
			$pickup_J1 = get_option( 'EMC_PICKUP_J1' );
			$pickup_J2 = get_option( 'EMC_PICKUP_J2' );
			
			$date =	self::setCollectDate(
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
			);
			update_post_meta( $order->id, '_pickup_date', $date );
		}
		if ( !get_post_meta( $order->id, '_insurance' ) || get_post_meta( $order->id, '_insurance', true ) == '' ) {
			if( get_option ('EMC_ASSU') == 'yes' ){
				update_post_meta( $order->id, '_insurance', 1 );
			}
			else {
				update_post_meta( $order->id, '_insurance', 0 );
			}
			update_post_meta( $order->id, '_assurance_emballage', 'Boîte' );
			update_post_meta( $order->id, '_assurance_materiau', 'Carton' );
			update_post_meta( $order->id, '_assurance_protection', 'Sans protection particulière' );
			update_post_meta( $order->id, '_assurance_fermeture', 'Fermeture autocollante' );	
		}
		if ( !get_post_meta( $order->id, '_url_push' ) ) {
			$key_push = md5(get_post_meta( $order->id, '_shipping_last_name', true ).$order->id);
			$url_push = home_url().'?wc-api=envoimoinscher&order='.$order->id.'&key='.$key_push;
			update_post_meta( $order->id, '_url_push', $url_push );
			update_post_meta( $order->id, '_key_push', $key_push );
		}
		
		// add proforma default fields
		if ( !get_post_meta( $order->id, '_proforma_reason' ) ) {
			update_post_meta( $order->id, '_proforma_reason', 'sale');
		}
		// Get order items
		$items = $order->get_items();
		$i = 1;
		foreach ( $items as $item_id => $item ) {
			$_product  = $order->get_product_from_item( $item );
			
			if ( !get_post_meta( $order->id, '_proforma_' . $i . '_post_title_en' ) ) {
				update_post_meta( $order->id, '_proforma_' . $i . '_post_title_en', esc_html( $item[ 'name' ] ) );
			}
			if ( !get_post_meta( $order->id, '_proforma_' . $i . '_post_title_fr' ) ) {
				update_post_meta( $order->id, '_proforma_' . $i . '_post_title_fr', esc_html( $item[ 'name' ] ) );
			}
			if ( !get_post_meta( $order->id, '_proforma_' . $i . '_qty' ) ) {
				update_post_meta( $order->id, '_proforma_' . $i . '_qty', esc_html( $item[ 'qty' ] ) );
			}
			if ( !get_post_meta( $order->id, '_proforma_' . $i . '_price' ) ) {
				update_post_meta( $order->id, '_proforma_' . $i . '_price', esc_html( $item['line_subtotal'] ) / esc_html( $item['qty'] ) );
			}
			if ( !get_post_meta( $order->id, '_proforma_' . $i . '_origin' ) ) {
				update_post_meta( $order->id, '_proforma_' . $i . '_origin', __( 'France', 'envoimoinscher' ) );
			}
			if ( !get_post_meta( $order->id, '_proforma_' . $i . '_weight' ) ) {
				$weight = $_product->get_weight() ? wc_format_decimal( $_product->get_weight(), 2 ) : get_option('EMC_AVERAGE_WEIGHT');
				if( $weight == 0) {
					self::log('initialize_default_params: weight for proforma is null!');
					$weight = 0.02;	
				}
				update_post_meta( $order->id, '_proforma_' . $i . '_weight', wc_get_weight ( $weight, 'kg' ) );
			}
			$i++;
		}
	}
	
	/**
	 * Function to get default address for FO quotations.
	 */
	static function get_default_address() {
		return array(
			'adresse'     => '15 rue Marsollier',
			'code_postal' => '75002',
			'ville'       => 'Paris',
			'pays'        => 'FR',
			'type'        => 'particulier'
		);
	}
	
	/**
	 * Get API params from order.
	 * @param $order : the order we need to extract the parameters from.
	 * @returns array of all API params
	 */
	static function get_order_params( $order ) {
		$result = array();

		$result['params'] = array(
			'code_contenu' 							=> get_option( 'EMC_NATURE' ),
			'colis.description' 				=> get_post_meta( $order->id, '_desc_content', true ),
			'module'      							=> EMC_PLATFORM,
			'version'      							=> WC_VERSION,
			'type_emballage.emballage' 	=> get_option('EMC_WRAPPING'),
			'partnership'  							=> self::getPartnership(),
			'raison'										=> 'vente',
			'disponibilite.HDE'         => get_option( 'EMC_DISPO_HDE' ),
			'disponibilite.HLE'         => get_option( 'EMC_DISPO_HLE' ),
			'colis.valeur' 							=> get_post_meta( $order->id, '_desc_value', true ),
			'collecte' 									=> date('Y-m-d' , get_post_meta( $order->id, '_pickup_date', true ) ),
			'delai'        							=> 'aucun',
			'url_push' 									=> get_post_meta( $order->id, '_url_push', true ),
		);
		
		if ( get_post_meta( $order->id, '_emc_carrier', true ) != '' ){
			$carrier_code = get_post_meta( $order->id, '_emc_carrier', true );
			list($operator, $service) = explode("_", $carrier_code);	
			
			$result['params']['operator'] = $operator;
			$result['params']['service'] = $service;
			$result['params']['depot.pointrelais'] = $operator.'-'.get_post_meta( $order->id, '_dropoff_point', true );

			$carrier = self::get_service_by_carrier_code($carrier_code);
		
			if( $carrier->srv_pickup_point ){
				$result['params']['retrait.pointrelais']	= $operator.'-'.get_post_meta( $order->id, '_pickup_point', true );
			}
		}
		
		$result['from'] = array(
			'pays'        => 'FR',
			'code_postal' => get_option('EMC_POSTALCODE'),
			'ville'       => get_option('EMC_CITY'),
			'type'        => 'entreprise',
			'societe'     => get_option('EMC_COMPANY'),
			'adresse'     => get_option('EMC_ADDRESS'),
			'civilite'    => get_option('EMC_CIV'),
			'prenom'      => get_option('EMC_FNAME'),
			'nom'         => get_option('EMC_LNAME'),
			'email'       => get_option('EMC_MAIL'),
			'tel'         => self::normalizeTelephone(get_option('EMC_TEL')),
			'infos'       => get_option('EMC_COMPL')
		);

		
		$dest_type = 'particulier';

		$result['to'] = array(
			'pays'        => get_post_meta( $order->id, '_shipping_country', true ),
			'code_postal' => get_post_meta( $order->id, '_shipping_postcode', true ),
			'ville'       => get_post_meta( $order->id, '_shipping_city', true ),
			'type'        => $dest_type,
			'adresse'     => get_post_meta( $order->id, '_shipping_address_1', true )."|".get_post_meta( $order->id, '_shipping_address_2', true ),
			'civilite' 	  => 'M.',
			'prenom'      => get_post_meta( $order->id, '_shipping_first_name', true ),
			'nom'         => get_post_meta( $order->id, '_shipping_last_name', true ),
			'email'       => get_post_meta( $order->id, '_billing_email', true ),
			'societe'     => get_post_meta( $order->id, '_billing_company', true ),
			'tel'         => self::normalizeTelephone( get_post_meta( $order->id, '_billing_phone', true ) ),
			'infos'       => '',
		);

		$result['parcels'] = array(
			1 => array(
				'poids' 		=> get_post_meta( $order->id, '_dims_weight', true ),
				'longueur' 	=> get_post_meta( $order->id, '_dims_length', true ),
				'largeur' 	=> get_post_meta( $order->id, '_dims_width', true ),
				'hauteur' 	=> get_post_meta( $order->id, '_dims_height', true ),
			)
		);
		return $result;
	}
	
	/**
	 * Return the partnership code of the account
	 * @access public
	 * @return string
	 */
	static function getPartnership()
	{
		$partnership = get_option('EMC_PARTNERSHIP');
		if (!$partnership)
		{
			include_once('env/WebService.php');
			include_once('env/User.php');
		
			$login = get_option('EMC_LOGIN');
			$pass = get_option('EMC_PASS');
			$key = get_option('EMC_KEY');
			$env = get_option('EMC_ENV');

			$lib = new EnvUser(array('user' => $login, 'pass' => $pass, 'key' => $key));
			$lib->setEnv($env);
			$upload_dir = wp_upload_dir();
			$lib->setUploadDir($upload_dir['basedir']);
			$lib->getPartnership();

			$partnership = $lib->partnership;

			update_option('EMC_PARTNERSHIP', $partnership);
		}
		return $partnership;
	}
	
	/**
	 * Normalize telephone number (removes all non numerical characters).
	 * @access public
	 * @param string $tel Number to normalize
	 * @return string Telephone number normalized
	 */
	static function normalizeTelephone($tel)
	{
		$tel = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $tel));
		$tel = preg_replace('/[^0-9]/', '', $tel);
		return $tel;
	}
	
	/**
	 * Make collect date. We can't collect on Sunday.
	 * @access public
	 * @var array $delays Delays array.
	 * @return String Collect date.
	 */
	static function setCollectDate($delays)
	{
		$today = strtotime('Today');
		$time = strtotime(date('Y-m-d H:i'));

		foreach ($delays as $delay)
		{
			if ((int)$delay['to'] != 24)
				$time_to = strtotime(date('Y-m-d', $today).' '.(int)$delay['to'].':00');
			else
				$time_to = strtotime('Tomorrow');

			if ($time >= strtotime(date('Y-m-d', $today).' '.(int)$delay['from'].':00') && $time < $time_to)
			{
				$days_delay = $delay['j'];
				break;
			}
		}

		if (!isset($days_delay))
			$days_delay = (int)$delays[1] + 1;

		$result = strtotime('+'.$days_delay.'days', $time);

		if (date('N', $result) == 7)
			$result = strtotime('+1 day', $result);

		return $result;
	}
	
	/**
	 * Gets english for french API generated strings.
	 * @access public
	 * @var string.
	 * @return String english string if exists, french otherwise.
	 */
	static function get_english($string) {
		$translations = array(
			"type d'emballage utilisé" => __( 'Packing type', 'envoimoinscher' ),
			'Boîte' => __( 'Box', 'envoimoinscher' ),
			'Caisse' => __( 'Crate', 'envoimoinscher' ),
			'Bac' => __( 'Container', 'envoimoinscher' ),
			'Emballage isotherme' => __( 'Insulated packaging', 'envoimoinscher' ),
			'Etui' => __( 'Carrying case', 'envoimoinscher' ),
			'Malle' => __( 'Trunk', 'envoimoinscher' ),
			'Sac' => __( 'Bag/sack', 'envoimoinscher' ),
			'Tube' => __( 'Tube', 'envoimoinscher' ),
			'Matériau utilisé' => __( 'Materials', 'envoimoinscher' ),
			'Carton' => __( 'Cardboard box', 'envoimoinscher' ),
			'Bois' => __( 'Wood', 'envoimoinscher' ),
			'Carton blindé' => __( 'Reinforced cardboard', 'envoimoinscher' ),
			'Film opaque' => __( 'Opaque film', 'envoimoinscher' ),
			'Film transparent' => __( 'Transparent film', 'envoimoinscher' ),
			'Métal' => __( 'Metal', 'envoimoinscher' ),
			'Papier' => __( 'Paper', 'envoimoinscher' ),
			'Papier armé' => __( 'Reinforced paper', 'envoimoinscher' ),
			'Plastique et carton' => __( 'Plastic and cardboard', 'envoimoinscher' ),
			'Plastique' => __( 'Plastic', 'envoimoinscher' ),
			'Plastique opaque' => __( 'Opaque plastic', 'envoimoinscher' ),
			'Plastique transparent' => __( 'Transparent plastic', 'envoimoinscher' ),
			'Polystyrène' => __( 'Polystyrene', 'envoimoinscher' ),
			'Protection intérieure utilisée' => __( 'Interior protection', 'envoimoinscher' ),
			'Sans protection particulière' => __( 'No specific protection', 'envoimoinscher' ),
			'Calage papier' => __( 'Paper cushioning', 'envoimoinscher' ),
			'Bulles plastiques' => __( 'Plastic bubble wrap', 'envoimoinscher' ),
			'Carton antichoc' => __( 'Impact-resistant cardboard', 'envoimoinscher' ),
			'Coussin air' => __( 'Air cushion', 'envoimoinscher' ),
			'Coussin mousse' => __( 'Foam cushion', 'envoimoinscher' ),
			'Manchon carton (bouteille)' => __( 'Cardboard bottle protector', 'envoimoinscher' ),
			'Manchon mousse (bouteille)' => __( 'Foam bottle protector', 'envoimoinscher' ),
			'Matelassage' => __( 'Padding', 'envoimoinscher' ),
			'Plaque mousse' => __( 'Foam pad', 'envoimoinscher' ),
			'Coussin de calage' => __( 'Cushioning material', 'envoimoinscher' ),
			'Sachet bulles' => __( 'Padded mailer', 'envoimoinscher' ),
			'Fermeture utilisée' => __( 'Fastener used', 'envoimoinscher' ),
			'Fermeture autocollante' => __( 'Self-adhesive fastener', 'envoimoinscher' ),
			'Ruban adhésif' => __( 'Adhesive tape', 'envoimoinscher' ),
			'Agrafes' => __( 'Staples', 'envoimoinscher' ),
			'Clous' => __( 'Nails', 'envoimoinscher' ),
			'Collage' => __( 'Binding', 'envoimoinscher' ),
			'Ruban de cerclage' => __( 'Strapping tape', 'envoimoinscher' ),
			'Sangle ou feuillard' => __( 'Strap or coiled strip', 'envoimoinscher' ),
			'Agraphes et cerclage' => __( 'Stapling and strapping', 'envoimoinscher' ),
			'Clous et cerclage' => __( 'Nails and fastening', 'envoimoinscher' ),
			'Ficelles' => __( 'String', 'envoimoinscher' ),
		);

		if ( isset( $translations[$string] ) ) return $translations[$string];
		else return $string;
	}
	
	/**
	 * Flush offers cache
	 */
	static function flush_cache() {
		
		/* rewrite to work with transients */
		/*global $wpdb;
		$wpdb->delete($wpdb->prefix.'emc_cache_pricing',array(1=>1));
		self::handle_sql_error();*/
	}
	
	/**************************/
	/********** LOGS **********/
	/**************************/
	
	/**
	 * Check if there is an SQL error on the last request, if there is one, report it
	 */
	static function handle_sql_error() {
		global $wpdb;
		if ($wpdb->last_error != '') {
			self::log('Error on the last SQL query: '.$wpdb->last_error);
			self::log('The query was: '.$wpdb->last_query);
		}
	}
	
	/**
	 * Add the message to woocommerce log system
	 * Woocommerce add itself the message's date
	 * logs are stored in wc-logs/envoimoinscher-%md5(stuff)%.log
	 * @param $message : message to log
	 */
	static function log($message) {
		self::$logger->add('envoimoinscher',$message);
	}
	
	/**
	 * Insert documents links into emc_documents table
	 * @param array $params : links to save
	 * @return void
	 */
	public static function insert_documents_links( $query_string ){		
		global $wpdb;
		$params = array();
		parse_str( $query_string, $params );
		
		if ( get_post_meta( $params['order'], '_key_push', true ) == $params['key'] ){
			if ( isset( $params['carrier_reference'] ) ) 	update_post_meta($params['order'], '_carrier_ref', urldecode( $params['carrier_reference'] ) );
			if ( isset( $params['label_url'] ) ) 	update_post_meta($params['order'], '_label_url', urldecode( $params['label_url'] ) );
			if ( isset( $params['remise'] ) ) 		update_post_meta($params['order'], '_remise', urldecode( $params['remise'] ));
			if ( isset( $params['manifest'] ) ) 	update_post_meta($params['order'], '_manifest', urldecode( $params['manifest'] ));
			if ( isset( $params['connote'] ) )		update_post_meta($params['order'], '_connote', urldecode( $params['connote'] ));
			if ( isset( $params['proforma'] ) ) 	update_post_meta($params['order'], '_proforma', urldecode( $params['proforma'] ));
			if ( isset( $params['b13a'] ) ) 			update_post_meta($params['order'], '_b13a', urldecode( $params['b13a'] ));
		}
		exit;				
	}
	
	/**
	 * Insert tracking informations into emc_tracking table
	 * @param array $params : links to save
	 * @return void
	 */
	public static function insert_tracking_informations( $query_string ){		
		global $wpdb;
		$params = array();
		parse_str( $query_string, $params );
		
		if ( get_post_meta( $params['order'], '_key_push', true ) == $params['key'] ){			
			$emc_tracking = get_post_meta( $params['order'], '_emc_tracking');
			
			// Generate a tracking message if no one was given
			$text = urldecode( $params['text'] );
			if ( $text == '' ) {
				switch ( $params['etat'] ) {
					case 'CMD':
						$text = __( 'Order tracking: ordered.', 'envoimoinscher' );
						break;
					case 'ENV':
						$text = __( 'Order tracking: processing.', 'envoimoinscher' );
						break;
					case 'ANN':
						$text = __( 'Order tracking: cancelled.', 'envoimoinscher' );
						break;
					case 'LIV':
						$text = __( 'Order tracking: delivered.', 'envoimoinscher' );
						break;
					default:
						return false;
				}
			}

			// Generate the default date if no one was given
			$date = strtotime( $params['date'] );
			if ( $date == false)
				$date = time();
			
			$track_info = array(
				'trk_state' 				=> $params['etat'],
				'trk_date' 					=> date( 'Y-m-d H:i:s', $date ),
				'trk_text' 					=> $text,
				'trk_localisation' 	=> urldecode( $params['localisation'] ),
			);

			// add order note
			$order = WC()->order_factory->get_order($params['order']);		
			$order->add_order_note( $text );
			
			update_post_meta( $params['order'], '_emc_tracking', $emc_tracking );
		}
		exit;				
	}
	
	/**
	 * Function to flush rates cache
	 *
	 * @return void
	 */
	static function flush_rates_cache() {
		global $wpdb;
		
		$return = $wpdb->query( 
			"DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_emc_quote_%') 
				OR `option_name` LIKE ('_transient_timeout_emc_quote_%')
				OR `option_name` LIKE ('_transient_emc_parcels_%')
				OR `option_name` LIKE ('_transient_timeout_emc_parcels_%')	
				OR `option_name` LIKE ('_transient_wc_ship_%') 
				OR `option_name` LIKE ('_transient_timeout_wc_ship_%')" 
		);
		
		// Check if log exists for uninstall hook
		if( !isset(self::$logger) )	self::$logger = new WC_Logger();
		
		if ( $return ) {
			self::log('Offers cache was successfully flushed. ' . intval($return). ' rows where deleted.');
		}
		else{
			self::log('Offers cache is empty or couldn\'t be deleted.');
		}
		
		return $return;
	}
}
?>