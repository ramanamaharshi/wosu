
(function($){
	
	
	
	
	var oParsePosition = function (mPosition) {
		
		if (!(typeof mPosition == 'object' && mPosition instanceof google.maps.LatLng)) {
			if (typeof mPosition == 'string') {
				mPosition = mPosition.split(',');
			}
			if (typeof mPosition == 'object') {
				if (typeof mPosition[0] != 'undefined') {
					mPosition = {iX: parseFloat(mPosition[1]) , iY: parseFloat(mPosition[0])};
				}
				if (typeof mPosition.iX != 'undefined') {
					mPosition = {nLat: mPosition.iY , nLon: mPosition.iX};
				}
			}
			mPosition = new google.maps.LatLng(mPosition.nLat , mPosition.nLon);
		}
		
		return mPosition;
		
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
		
		oMap.oBindings = {};
		oMap.vBind = function (sEvent, fCallback) {
			if (typeof oMap.oBindings[sEvent] == 'undefined') {
				oMap.oBindings[sEvent] = [];
				google.maps.event.addListener(oMap, sEvent, function(){
					for (var iB = 0; iB < oMap.oBindings[sEvent].length; iB ++) {
						var f = oMap.oBindings[sEvent][iB];
						f.call(oMap, sEvent, arguments);
					}
				});
			}
			oMap.oBindings[sEvent].push(fCallback);
		}
		
		for (var iM = 0; iM < aMarkers.length; iM ++) {( function(){
			
			var oMarker = aMarkers[iM];
			
			oMarker.map = oMap;
			if (typeof oMarker.position != 'undefined') {
				oMarker.position = oParsePosition(oMarker.position);
			}
			if (typeof oMarker.icon != 'undefined') {
				if (typeof oMarker.icon.bounds != 'undefined') {
					oMarker.icon.origin = [oMarker.icon.bounds[0], oMarker.icon.bounds[1]];
					oMarker.icon.size = [oMarker.icon.bounds[2], oMarker.icon.bounds[3]];
				}
				var oIconAttrs = {scaledSize: 'Size', origin: 'Point', size: 'Size', anchor: 'Point'};
				for (var sAttr in oIconAttrs) {
					var sClass = oIconAttrs[sAttr];
					if (typeof oMarker.icon[sAttr] != 'undefined' && !(oMarker.icon[sAttr] instanceof google.maps[sClass])) {
						oMarker.icon[sAttr] = new google.maps[sClass](oMarker.icon[sAttr][0],oMarker.icon[sAttr][1]);
					}
				}
			}
			
			oMarker.oMarker = new google.maps.Marker(oMarker);
			
			oMarker.oMarker.oBindings = {};
			oMarker.oMarker.vBind = function (sEvent, fCallback) {
				var gMarker = oMarker.oMarker;
				if (typeof gMarker.oBindings[sEvent] == 'undefined') {
					gMarker.oBindings[sEvent] = [];
					google.maps.event.addListener(gMarker, sEvent, function(){
						for (var iB = 0; iB < gMarker.oBindings[sEvent].length; iB ++) {
							var f = gMarker.oBindings[sEvent][iB];
							f.call(oMarker, sEvent, arguments);
						}
					});
				}
				gMarker.oBindings[sEvent].push(fCallback);
			};
			
		})(); }
		
		jThis.data('oMap', oMap);
		
		return oMap;
		
	};
	
	
	
	
	$.fn.oGetMap = function () {
		
		return $(this).data('oMap');
		
	};
	
	
	
	
})(jQuery);
