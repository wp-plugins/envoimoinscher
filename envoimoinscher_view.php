<?php
class envoimoinscher_view{
	
	static function display_google_map_container() {
		return  '<div id="map-container">'
		      . '<p><a class="emc-close-map">'.__( 'Close map', 'envoimoinscher' ).'</a></p>'
		      . '<div id="map-canvas"></div>'
		      . '</div>';
	}
}
?>