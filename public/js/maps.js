
(function($){
	
	
	
	
	var oParsePosition = function (mPosition) {
		
		if (typeof mPosition == 'string') {
			mPosition = mPosition.split(',');
		}
		if (typeof mPosition == 'object' && typeof mPosition[0] != 'undefined') {
			mPosition = {nLat: parseFloat(mPosition[0]), nLon: parseFloat(mPosition[1])};
		}
		
		return new google.maps.LatLng(mPosition.nLat,mPosition.nLon);
		
	};
	
	
	
	
	$.fn.oMakeMap = function (oOptions, aMarkers) {
		
		var jThis = $(this);
		
		if (typeof oOptions.zoom == 'undefined') {
			oOptions.zoom = 13
		}
		if (typeof oOptions.center != 'undefined') {
			oOptions.center = oParsePosition(oOptions.center);
		}
		if (typeof oOptions.type != 'undefined') {
			oOptions.mapTypeId = google.maps.MapTypeId[oOptions.type.toUpperCase()];
		}
		
		var oMap = new google.maps.Map(jThis[0], oOptions);
		
		for (var iM = 0; iM < aMarkers.length; iM ++) {
			var oMarker = aMarkers[iM];
			oMarker.oMarker = new google.maps.Marker({
				map: oMap,
				position: new google.maps.LatLng(oMarker.nLat,oMarker.nLon),
				title: oMarker.sTitle,
			});
		}
		
		jThis.data('oMap', oMap);
		
		return oMap;
		
	};
	
	
	
	
	$.fn.oGetMap = function () {
		
		return $(this).data('oMap');
		
	};
	
	
	
	
})(jQuery);
