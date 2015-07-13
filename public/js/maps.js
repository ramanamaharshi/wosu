
(function($){
	
	$.fn.vMakeMap = function (oOptions, aMarkers) {
		
		var jThis = $(this);
		var dThis = jThis[0];
		
		if (typeof oOptions.zoom == 'undefined') {
			oOptions.zoom = 13
		}
console.log(oOptions.center);
		if (typeof oOptions.center != 'undefined') {
			if (typeof oOptions.center == 'string') {
				oOptions.center = oOptions.center.split(',');
			}
			if (typeof oOptions.center == 'object' && typeof oOptions.center[0] != 'undefined') {
				oOptions.center = {nLat: oOptions.center[0], nLon: oOptions.center[1]}
			}
			oOptions.center = new google.maps.LatLng(oOptions.center.nLat , oOptions.center.nLon);
		}
console.log(oOptions.center);
		if (typeof oOptions.type != 'undefined') {
			oOptions.mapTypeId = google.maps.MapTypeId[oOptions.type.toUpperCase()];
		}
console.log({oOptions: oOptions});
		
		var oMap = new google.maps.Map(dThis, oOptions);
		
		for (var iM = 0; iM < aMarkers.length; iM ++) {
			var oMarker = aMarkers[iM];
			new google.maps.Marker({
				title: oMarker.sTitle,
				position: new google.maps.LatLng(oMarker.nLat,oMarker.nLon),
				map: oMap,
			});
		}
		
		console.log(this, 'vMakeMap()');
		
	}
	
})(jQuery);
