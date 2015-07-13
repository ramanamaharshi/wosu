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
		
		
		
		
		public static function sMakeMapHtml ($mCoords, $aOptions = array(), $aMarkers = array()) {
			
			$aCoords = explode(',', $mCoords);
			$aCoords = array(
				'nLat' => floatval($aCoords[0]),
				'nLon' => floatval($aCoords[1]),
			);
			
			$sMap = '
				
				<style>
					html, body, #map-canvas {
						height: 100%;
						margin: 0;
						padding: 0;
					}
				</style>
				
				<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true"></script>
				
				<script>
					
					function initialize() {
						
						var oCoords = ' . json_encode($aCoords) . ';
						var oOptions = ' . json_encode($aOptions) . ';
						var aMarkers = ' . json_encode($aMarkers) . ';
						
						oOptions.center = new google.maps.LatLng(oCoords.nLat,oCoords.nLon);
						
						if (typeof oOptions.zoom) oOptions.zoom = 13
						//if (oOptions.type == \'terrain\') {
						//	oOptions.mapTypeId = google.maps.MapTypeId.TERRAIN;
						//}
						
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
					
				</script>
				
				<div id="map-canvas"></div>
				
			';
			
			return $sMap;
			
		}
		
		
		
		
	}
	
	
	
	
?>