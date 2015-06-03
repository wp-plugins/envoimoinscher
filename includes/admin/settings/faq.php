<?php

$faq = array (
	0 => array(
		'category' 	=> __( 'Setup, activation and testing', 'envoimoinscher' ),
		'questions' => array( 
			0 => array( 
				'question' 	=> __( 'Can I test my plugin before activation on front office?', 'envoimoinscher' ),
				'answer' 		=> __( 'You can try your settings before switching to production. Make sure your environment option is on "Test" in your "My account" tab. A simulation section allows you to do test quotations with your catalog items and check front-office shipping costs.', 'envoimoinscher' ),
			),
			1 => array( 
				'question' 	=> __( 'How to enable production environment?', 'envoimoinscher' ),
				'answer' 		=> __( 'To enable production environment, you must switch the environment option in your "My account" tab to "Prod." and change your API key to the production API key.', 'envoimoinscher' ),
			),
			2 => array( 
				'question' 	=> __( 'I have got an "Invalid account payment method" error in production environment: what should i do?', 'envoimoinscher' ),
				'answer' 		=> __( 'You have to enable the deferred payment to use the module in production environment. You can enable it in your account page / preferences on the EnvoiMoinsCher site, or contact us at the following address: <a href="mailto:compta@envoimoinscher.com">compta@envoimoinscher.com</a>. You must give us your login, iban and a debit authorisation.', 'envoimoinscher' ),
			),
			3 => array( 
				'question' 	=> __( 'I have got an "Error : Contact our billing service" error: what should i do?', 'envoimoinscher' ),
				'answer' 		=> __( 'This error suggests a payment issue. You may have reached your account spend cap. Contact our accounting department at <a href="mailto:compta@envoimoinscher.com">compta@envoimoinscher.com</a> and give us your EnvoiMoinsCher login.', 'envoimoinscher' ),
			),
		),
	),
	
	1 => array(
		'category' 	=> __( 'Plugin settings', 'envoimoinscher' ),
		'questions' => array( 
			0 => array( 
				'question' 	=> __( 'How should I configure the "My account" tab?', 'envoimoinscher' ),
				'answer' 		=> __( 'Please enter the same login and password you have chosen to create your account at <a href="http://www.boxtale.co.uk" target="_blank">www.boxtale.co.uk</a>. Thanks to this login information you can also access your personal account on the website. The API key was provided by our IT team following your request.<ul><li><strong>First choose the "test" API key</strong> to configure and test the plugin.</li><li><strong>When you are done configurating and testing, you can use your "live" API key.</strong> If the billing company is the same for your websites, you can use the same "live" API key on all websites. If not, we advice you to create another account and request a new API key.</li></ul>', 'envoimoinscher' ),
			),
			1 => array( 
				'question' 	=> __( 'How should I configure the "Shipping description" tab?', 'envoimoinscher' ),
				'answer' 		=> __( '<ul><li>Select the type of <strong>shipment content</strong> from the drop-down list. If you distribute several categories, please choose the main one. <span style="color:red;">Please pay attention: certain types of shipment content can be refused by some carriers.</span> Check the website or contact our client service to learn about possible restrictions.</li><li>You should select the <strong>packaging type</strong> only if you have activated Colissimo amongst your carriers. If the category "<strong>Tube</strong>" is selected, 6 euros will be added to every shipment.</li></ul>', 'envoimoinscher' ),
			),
			2 => array( 
				'question' 	=> __( 'How should I configure the "Carriers" tab?', 'envoimoinscher' ),
				'answer' 		=> __( '<ul><li>Click on "Reload carriers from API".</li><li>Select the carriers and activate them.</li><li>Click on "<strong>Edit</strong>" to set up a <strong>rate or the API\'s real price</strong>, select a <strong>drop-off point</strong>, configure a <strong>tracking link</strong> per carrier.</li></ul>', 'envoimoinscher' ),
			),
			3 => array( 
				'question' 	=> __( 'What is the difference between "simple" and "advanced" carriers? How should I configure "Advanced carriers"?', 'envoimoinscher' ),
				'answer' 		=> __( '<strong>Simple carriers</strong>: prices are calculated from grid based on parcel weight.<br/><strong>Advanced carriers</strong>: prices are calculated from grid based or parcel weight and size. Because parcel volume cannot be calculated during customer checkout, you must fill in the "<strong>Weight options</strong>" table. The plugin will use this table to get dimensions according to shopping cart weight and will display relevant shipping costs. Without customization, the default dimensions provided by Boxtale will be used.', 'envoimoinscher' ),
			),
			4 => array( 
				'question' 	=> __( 'How are the real price and the rate set?', 'envoimoinscher' ),
				'answer' 		=> __( '<strong>Rate:</strong> shipping fees fixed by yourself.<br/><strong>Real price:</strong> shipping fees calculated automatically by Boxtale based on the shopping basket weight.', 'envoimoinscher' ),
			),
		),
	),
	
	2 => array(
		'category' 	=> __( 'Shipping, tracking and insurance', 'envoimoinscher' ),
		'questions' => array( 
			0 => array( 
				'question' 	=> __( 'Where can I send my orders?', 'envoimoinscher' ),
				'answer' 		=> __( 'Go to "<strong>WooCommerce</strong>" tab and click on "<strong>Orders</strong>".', 'envoimoinscher' ),
			),
			1 => array( 
				'question' 	=> __( 'What type of orders can I send?', 'envoimoinscher' ),
				'answer' 		=> __( 'You can send all your orders marked "<strong>On hold</strong>" or "<strong>Processing</strong>" either one by one or in bulk.', 'envoimoinscher' ),
			),
			2 => array( 
				'question' 	=> __( 'How can I ship my EnvoiMoinsCher orders one by one? ', 'envoimoinscher' ),
				'answer' 		=> __( '<ul><li>Click on "<strong>View</strong>" on the right end of order line. The "<strong>Edit Order</strong>" page is displayed. Default settings will be applied if you want to send right away. If needed, you can modify some data (carrier, weight-dimensions, ...) in the form in the middle of the "<strong>Edit Order</strong>" page => "<strong>Shipping Options - EnvoiMoinsCher</strong>".</li><li><strong>Choose "Send using EnvoiMoinsCher"</strong> in the drop-down list in the top right-hand corner of the page and click on "<strong>Apply</strong>".</li><li><strong>If the shipment is successful,</strong> the order status will be changed to "<strong>Awaiting Shipment</strong>" and a <strong>confirmation message</strong> will be displayed.</li></ul>If any problem occurs, you\'ll see an alert notice.', 'envoimoinscher' ),
			),
			3 => array( 
				'question' 	=> __( 'How can I ship in bulk with EnvoiMoinsCher carriers?', 'envoimoinscher' ),
				'answer' 		=> __( '<ul><li><strong>Tick the box in front of the orders you want to ship.</strong></li><li><strong>Choose "Send using EnvoiMoinsCher"</strong> in the bulk action drop-down list in the top left-hand corner of the page and <strong>click on "Apply"</strong>.</li><li><strong>If the shipments are successful,</strong> the orders statuses will be changed to "<strong>Awaiting Shipment</strong>" and a <strong>confirmation message</strong> will be displayed.</li></ul>If any problem occurs, you\'ll see an alert notice informing that you should correct the invalid orders one by one. Please note that as a security, you cannot send twice the same order using bulk actions.', 'envoimoinscher' ),
			),
			4 => array( 
				'question' 	=> __( 'How can I download my waybills?', 'envoimoinscher' ),
				'answer' 		=> __( 'You have three options to get your waybills: <strong>from WooCommerce</strong>, <strong>by email</strong> or in your <strong>Boxtale personal account</strong>, "<strong>My shipments</strong>" tab.<ul><li><strong>From WooCommerce :</strong> Go to the "WooCommerce" tab in your WordPress back-office => "Orders" / using "Awaiting Shipment" status filter. <strong>To download one or several waybills at once: select the orders</strong> you want to download by checking the box, <strong>choose "Download all waybills"</strong> in the bulk action drop-down list in the top left-hand corner of the page and <strong>click on "Apply"</strong>. A download link will also appear on your order page view: "<strong>Shipping Options - EnvoiMoinsCher</strong>" => "<strong>Your waybill is available. Download it here.</strong>".</li><li><strong>By email:</strong> You can choose to receive the waybill by email. This option configuration is available either in your EnvoiMoinsCher personal account or in WooCommerce: WooCommerce => EnvoiMoinsCher => Settings.</li><li><strong>From you EnvoiMoinsCher account, "My shipments" tab</strong></li></ul>', 'envoimoinscher' ),
			),
			5 => array( 
				'question' 	=> __( 'I can\'t generate my waybill.', 'envoimoinscher' ),
				'answer' 		=> __( '<i>Did I wait long enough?</i> Depending on the carrier, generation can take up to 5 minutes, so you have to wait a bit.<br/><br/><i>Am I in a test environment?</i> In this case, the order was not accepted and the waybill will not be generated. If your order was real, you should switch to production environment ("My account" tab in EnvoiMoinsCher settings) and re-send your order. Alternatively, you can login in to the Boxtale site and send your order directly from the site (do not forget to log in for the order to be added to your bill).<br/><br/>Contact technical service <a href="mailto:informationAPI@envoimoinscher.com">informationAPI@envoimoinscher.com</a> stating your problem and an access to your back office (url, login, password) to speed up the solving of your problem. You\'ll receive a response as soon as possible.', 'envoimoinscher' ),
			),
			6 => array( 
				'question' 	=> __( 'How can I track my shipment? How can I set up shipment tracking for my customers?', 'envoimoinscher' ),
				'answer' 		=> __( 'Tracking information is displayed in your order notes. In order to display a tracking URL for your customers in front office, you must configure the carrier tracking URLs of your carriers: <strong>WooCommerce</strong> => <strong>EnvoiMoinsCher</strong> => <strong>Carrier</strong> => <strong>Edit</strong> => <strong>Tracking URL</strong>.<br/>For information, here are tracking URLs which can be used with the EnvoiMoinsCher plugin. Please note that these URLs are provided for information only and can be changed without notice.<ul><li>Chronopost - <a href="http://www.chronopost.fr/transport-express/livraison-colis/cache/bypass/pid/701" target="_blank">http://www.chronopost.fr/transport-express/livraison-colis/cache/bypass/pid/701</a></li><li>Colis Priv&eacute; - <a href="https://www.colisprive.com/moncolis/pages/detailColis.aspx?numColis=@" target="_blank">https://www.colisprive.com/moncolis/pages/detailColis.aspx?numColis=@</a></li><li>Mondial relay - <a href="http://www.mondialrelay.fr/suivi-de-colis/" target="_blank">http://www.mondialrelay.fr/suivi-de-colis/</a></li><li>Relais colis - <a href="http://www.relaiscolis.com/index.php/application-suivi-colis" target="_blank">http://www.relaiscolis.com/index.php/application-suivi-colis</a></li><li>UPS - <a href="http://wwwapps.ups.com/WebTracking/track?HTMLVersion=5.0&loc=fr_FR&Requester=UPSHome&WBPM_lid=homepage%2Fct1.html_pnl_trk&track.x=Suivi&trackNums=@" target="_blank">http://wwwapps.ups.com/WebTracking/track?HTMLVersion=5.0&loc=fr_FR&Requester=UPSHome&WBPM_lid=homepage%2Fct1.html_pnl_trk&track.x=Suivi&trackNums=@</a></li><li>DHL Express - <a href="http://www.dhl.fr/content/fr/fr/dhl_express/suivi_expedition.shtml?brand=DHL&AWB=@" target="_blank">http://www.dhl.fr/content/fr/fr/dhl_express/suivi_expedition.shtml?brand=DHL&AWB=@</a></li><li>Fedex - <a href="https://www.fedex.com/fedextrack/?tracknumbers=@" target="_blank">https://www.fedex.com/fedextrack/?tracknumbers=@</a></li><li>La Poste - <a href="http://www.colissimo.fr/portail_colissimo/suivre.do?language=fr_FR" target="_blank">http://www.colissimo.fr/portail_colissimo/suivre.do?language=fr_FR</a></li><li>TNT - <a href="http://www.tnt.fr/public/suivi_colis/recherche/index.do;jsessionid=78654DB3C246ADA008279CE05E802C7A" target="_blank">http://www.tnt.fr/public/suivi_colis/recherche/index.do;jsessionid=78654DB3C246ADA008279CE05E802C7A</a></li></ul>', 'envoimoinscher' ),
			),
			7 => array( 
				'question' 	=> __( 'Where can I find the tracking number of my shipments?', 'envoimoinscher' ),
				'answer' 		=> __( 'For the buyer: the buyer will have access to the link on his order page in his account on your website (if you have configured the tracking url for the carrier).<br/><br/>For the seller: you can find both EnvoiMoinsCher reference and carrier tracking number on the "Edit Order" page => "Shipping Options - EnvoiMoinsCher". You can also track the shipment from your account on EnvoiMoinsCher.com.', 'envoimoinscher' ),
			),
			8 => array( 
				'question' 	=> __( 'How can I insure my shipments Ad Valorem?', 'envoimoinscher' ),
				'answer' 		=> __( '<ol><li>Insurance will only be automatically added to your shipping options if in the configuration of EnvoiMoinsCher plugin / "Shipping description" tab, you check the "Use AXA Insurance" option. Be careful if you use this option that insurance is <strong>not</strong> automatically added to the carrier price given to your customer in your shop.</li><li>You can also add the option after the order was created in your order view page: <strong>WooCommerce</strong> => <strong>Orders</strong> => <strong>View</strong> => <strong>Shipping Options - EnvoiMoinsCher</strong> => <strong>Insurance</strong>.</li></ol>', 'envoimoinscher' ),
			),
			9 => array( 
				'question' 	=> __( 'What are the specifics for shipping abroad?', 'envoimoinscher' ),
				'answer' 		=> __( 'For shipments abroad, you must describe the contents of your shipment both in English and in the language of the parcel\'s country of origin.<br/><br/>Please note the following:<ul><li>For shipments abroad, the plugin does not block shipments if the content description is not translated to English. Please keep an eye on the translations.</li><li>You must attach to your package a commercial invoice in 5 copies for all international shipments (outside the EU).</li><li>Any specific documentation related to a special country or product is your responsibility.</li></ul>', 'envoimoinscher' ),
			),
		),
	),
	
	3 => array(
		'category' 	=> __( 'Updates', 'envoimoinscher' ),
		'questions' => array( 
			0 => array( 
				'question' 	=> __( 'Will the update affect my configuration?', 'envoimoinscher' ),
				'answer' 		=> __( 'The update does not affect your configuration: settings, carriers...', 'envoimoinscher' ),
			),
			1 => array( 
				'question' 	=> __( 'Can I benefit from new carrier offers available through updates?', 'envoimoinscher' ),
				'answer' 		=> __( 'No, you should update your carrier list manually on your carriers tab.', 'envoimoinscher' ),
			),
		),
	),
	
	4 => array(
		'category' 	=> __( 'Any other question?', 'envoimoinscher' ),
		'questions' => array( 
			0 => array( 
				'question' 	=> '',
				'answer' 		=> '',
				'comment' 		=> __( 'If you do not find an answer to your questions in this FAQ, please contact our technical support (<a href="mailto:informationapi@envoimoinscher.com">informationapi@envoimoinscher.com</a>) and explain your problem. Please provide us a superadmin access (url, login, password) so we can have a look at your back office. To do so, go to the Users tab > Add New, create a user with the "Administrator" role.', 'envoimoinscher' ),
			),
		),
	),
);