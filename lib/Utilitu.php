<?php
	
	
	
	
	class Utilitu {
		
		
		
		
		public static function sConditionalHash ($sInput) {
			
			$sHash = $sInput;
			
			if (strlen($sHash) > 32) $sHash = md5($sHash);
			
			return $sHash;
			
		}
		
		
		
		
		public static function sRandomString ($iLength, $sChars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz') {
			
			$sReturn = '';
			
			for ($i = 0; $i < $iLength; $i++) {
				$sReturn .= $sChars[rand(0, strlen($sChars) - 1)];
			}
			
			return $sReturn;
			
		}
		
		
		
		
		public static function sPregRead ($sString, $sRegExp, $sReadKey = 1) {
			
			preg_match($sRegExp, $sString, $aMatches);
			return $aMatches[$sReadKey];
			
		}
		
		
		
		
		public static function sMicrotime () {
			
			preg_match('/^(\d+)\.(\d+) (\d+)$/', microtime(), $aMatches);
			
			return $aMatches[3] . $aMatches[2];
			
		}
		
		
		
		
		public static function sGetHtdocs () {
			
			return $GLOBALS['htdocs'];
			
		}
		
		
		
		
	}
	
	
	
	
?>