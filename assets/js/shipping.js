var emc_parcels = null;
var infowindow = null;
var carrier_code = '';
var map = null;
var geocoder = null;
var markers = [];
var parcels_info = [];

jQuery(window).load(function(){
	
	// see function load_pickup_point_js in class envoimoinscher
	jQuery('body').append(map_container);
	
	// close map if selected carrier is changed
	jQuery('body').delegate('input.shipping_method', 'change', close_map);
	
	jQuery('body').delegate('.select-parcel','click',function(){
			
		init_map();
		carrier_attributes = jQuery(this).attr('id').split('_');
		carrier_code = carrier_attributes[1]+'_'+carrier_attributes[2];
		jQuery.ajax({
			url:ajaxurl,
			data: { action: 'get_points', security: ajax_nonce, carrier_code: carrier_code },
			dataType:'json',
			timeout:15000,
			error:error_pickup_points,
			success:show_pickup_points
		});
		if(typeof jQuery(this).attr("shown") == "undefined"){
			google.maps.event.trigger(map, 'resize');
		}
	});
	
	jQuery('#map-canvas').delegate('.emc-select-point','click',select_pickup_point);
	
	jQuery('.emc-close-map').click(close_map);
});

/*
 * Initialize the google map for a new display
 */
function init_map() {
	jQuery('#map-container').css('display','block');
  var options = {
    zoom: 11, 
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };
	map = new google.maps.Map(document.getElementById("map-canvas"), options);
  geocoder = new google.maps.Geocoder();
	infowindow = new google.maps.InfoWindow();
	google.maps.event.trigger(map, 'resize');
}

/*
 * Close and clear the google map
 */
function close_map() {
	jQuery('#map-container').css('display','none');
	jQuery('#map-canvas').html('');
  for (var i = 0; i < markers.length; i++) {
    markers[i].setMap(null);
  }
	markers = [];
	parcels_info = [];
}

/*
 * We update the zoom level to the best fit 
 */
function update_zoom_map() {
	// zoom only if we have all markers
	if (emc_parcels.length == 0 ||  (emc_parcels.length != 0 && markers.length < emc_parcels.length))
	{
		return;
	}	
	var bounds = new google.maps.LatLngBounds();
	
	for(var i = 0;i<markers.length;i++) {
		if (typeof markers[i] != 'undefined')
			bounds.extend(markers[i].getPosition());
	}
	map.setCenter(bounds.getCenter());	
	google.maps.event.addDomListener(window, 'resize', function() {
			map.setCenter(bounds.getCenter());	
	});
	map.fitBounds(bounds);
	map.setZoom(map.getZoom()-1); 
	// if only 1 marker, unzoom
	if(map.getZoom()> 15){
		map.setZoom(15);
	}	
	google.maps.event.trigger(map, 'resize');
}

/*
 * Now that we have all the parcel points, we display them
 */
function show_pickup_points(parcel_points) {
	emc_parcels = parcel_points;
	
	// add parcel point markers
  for (i in parcel_points){
		point = parcel_points[i];
    (function(i) {
      var address = point.address;
      var city = point.city;
      var zipcode = point.zipcode;
			var name = point.name;
      info ='<b>'+name+'</b><br/>'+
			      '<u class="emc-select-point" data="'+i+'">'+lang['I want this pickup point']+'</u><br/>'+
						'<span>'+point.address+', '+point.zipcode+' '+point.city+'</span><br/>'+
						'<div class="emc-opening-hours"><table>';
						
			for (j in point.days){
				day = point.days[j];
				am = day.open_am != "" && day.close_am != "";
				pm = day.open_pm != "" && day.close_pm != "";
				if (am || pm) {
					info += '<tr>';
					info += '<td><b>'+lang['day_'+day.weekday]+'</b> : </td>';
					info += '<td>';
					if (am) {
						info += '<span>'+(lang['From %1 to %2'].replace('%1',day.open_am.substring(0,5)).replace('%2',day.close_am.substring(0,5)))+'</span>';
					}
					if (pm) {
						if (am) {
							info += '<span>'+(lang[' and %1 to %2'].replace('%1',day.open_pm.substring(0,5)).replace('%2',day.close_pm.substring(0,5)))+'</span><br/>';
						}
						else {
							info += '<span>'+(lang['From %1 to %2'].replace('%1',day.open_pm.substring(0,5)).replace('%2',day.close_pm.substring(0,5)))+'</span><br/>';
						}
					}
					info += '</td></tr>';
				}
			}
			info += '</table></div>';

			parcels_info[i] = info;
			
			if(geocoder)
      {
        geocoder.geocode({ 'address': address + ', ' + zipcode + ' ' + city }, function(results, status) {
          if(status == google.maps.GeocoderStatus.OK)   
          {
						map.setCenter(results[0].geometry.location);
						var marker = new google.maps.Marker({
							map: map, 
							position: results[0].geometry.location,
							title : name
						});
						marker.set("content", parcels_info[i]);
						google.maps.event.addListener(marker,"click",function() {
							infowindow.close();
							infowindow.setContent(this.get("content"));
							infowindow.open(map,marker);
						});
						markers[i] = marker;
						update_zoom_map();
          }
        });
      }
    })(i);
		
  }
  // remove info if we click on the map
	google.maps.event.addListener(map,"click",function() {
		infowindow.close();
	});
}

/*
 * We clicked on the "i want this parcel point" link on the google map marker's info
 */
function select_pickup_point(source){
	code = emc_parcels[jQuery(source.target).attr('data')].code.split('-',2)[1];
	name = emc_parcels[jQuery(source.target).attr('data')].name;
	jQuery('#input_'+carrier_code).html('<input type="hidden" name="_pickup_point" value="'+code+'"/>');
	jQuery('#emc-parcel-client').html(name);
	jQuery('#map-container').css('display','none');
	close_map();
}

function error_pickup_points(jqXHR, textStatus, errorThrown ) {
	alert(lang['Unable to load parcel points']+' : '+errorThrown);
}