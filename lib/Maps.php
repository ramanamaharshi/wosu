<?php
	
	
	
	
	class Maps {
		
		
		
		
		private static $sTable = 'geocode_cache';
		
		
		
		
		public static function oGetCoords ($sAddress) {
			
			$sAddressHash = Utilitu::sConditionalHash($sAddress);
			
			$oAddress = DirectDB::oSelectOne(self::$sTable, array('address_hash' => $sAddressHash));
			if (!$oAddress) {
				$sResponse = Curl::sGet('http://maps.google.com/maps/api/geocode/json?address=52062%20B%C3%BCchel%2011&sensor=false');
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
		
		
		
		
	}
	
	
	
	
?>