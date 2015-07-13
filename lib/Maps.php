<?php
	
	
	
	
	class Maps {
		
		
		
		
		private static $sTable = 'geocode_cache';
		
		
		
		
		public static function oGetCoords ($sAddress) {
			
			$sAddressHash = Utilitu::sConditionalHash($sAddress);
			
			$oAddress = DirectDB::oSelectOne(self::$sTable, array('address_hash' => $sAddressHash));
			if (!$oAddress) {
				$sResponse = Curl::sGet('http://maps.google.com/maps/api/geocode/json?address=' . urlencode($sAddress) . '&sensor=false');
				$oResponse = json_decode($sResponse);
				$oFirstResult = $oResponse->results[0];
				$oFRC = $oFirstResult->geometry->location;
				DirectDB::iInsert(self::$sTable, array(
					'address_hash' => $sAddressHash,
					'fetched' => date('Y-m-d H:i:s'),
					'x' => '' . $oFRC->lng . '',
					'y' => '' . $oFRC->lat . '',
					'response' => $sResponse,
				));
				$oAddress = DirectDB::oSelectOne(self::$sTable, array('address_hash' => $sAddressHash));
			}
			
			$oCoords = new StdClass();
			$oCoords->iX = $oAddress->x;
			$oCoords->iY = $oAddress->y;
			
			return $oCoords;
			
		}
		
		
		
		
		public static function vInit () {
			
if (isset($_REQUEST['restructure'])) DirectDB::aQuery("DROP TABLE " . self::$sTable . ";");
			
			DirectDB::aQuery("
				CREATE TABLE IF NOT EXISTS `" . self::$sTable . "` (
					address_hash varchar(32) NOT NULL,
					fetched DATETIME,
					x varchar(32),
					y varchar(32),
					response text,
					PRIMARY KEY (`address_hash`)
				) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			");
			
		}
		
		
		
		
		public static function sMakeMapHtml ($aOptions = array(), $aMarkers = array()) {
			
			$sMap = '
				
				<style>
					html, body, #map-canvas {
						height: 100%;
						padding: 0;
						margin: 0;
					}
				</style>
				
				<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true"></script>
				
				<script>
					
					//<[CDATA[
					
					(function(){
						
						function initialize() {
							
							var oOptions = ' . json_encode($aOptions) . ';
							var aMarkers = ' . json_encode($aMarkers) . ';
							
							if (typeof oOptions.zoom == "undefined") {
								oOptions.zoom = 13
							}
							if (typeof oOptions.center != "undefined") {
								if (typeof oOptions.center == "string") {
									oOptions.center = oOptions.center.split(",");
								}
								if (typeof oOptions == "object" && typeof oOptions[0] != "undefined") {
									oOptions.center = {nLat: oOptions.center[0], nLon: oOptions.center[1]}
								}
								oOptions.center = new google.maps.LatLng(oOptions.center.nLat , oOptions.center.nLon);
							}
							if (typeof oOptions.type != \'undefined\') {
								oOptions.mapTypeId = google.maps.MapTypeId[oOptions.type.toUpperCase()];
							}
							
							var map = new google.maps.Map(document.getElementById(\'map-canvas\'), oOptions);
							for (var iM = 0; iM < aMarkers.length; iM ++) {
								var oMarker = aMarkers[iM];
								new google.maps.Marker({
									title: oMarker.sTitle,
									position: new google.maps.LatLng(oMarker.nLat,oMarker.nLon),
									map: map,
								});
							}
							
						}
						
						google.maps.event.addDomListener(window, \'load\', initialize);
						
					})();
					
					//]]>
					
				</script>
				
				<div id="map-canvas"></div>
				
			';
			
			return $sMap;
			
		}
		
		
		
		
	}
	
	
	
	
?>