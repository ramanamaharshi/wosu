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
			$oCoords->nX = $oAddress->x;
			$oCoords->nY = $oAddress->y;
			
			return $oCoords;
			
		}
		
		
		
		
		public static function vWipeDatabase () {
			
			DirectDB::aQuery("DROP TABLE " . self::$sTable . ";");
			
		}
		
		
		
		
		public static function vInit () {
			
			DirectDB::aQuery("
				CREATE TABLE IF NOT EXISTS `" . self::$sTable . "` (
					address_hash varchar(32) NOT NULL,
					fetched DATETIME,
					x varchar(32),
					y varchar(32),
					response mediumtext,
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
							
							$("#map-canvas").oMakeMap(oOptions, aMarkers);
							
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