<?php
	
	
	
	
	class Utilitu {
		
		
		
		
		public static $sHtdocs = null;
		
		
		
		
		public static function aListFiles ($sDir, $sFileType = null, $bAbsolute = false) {
			
			$aFiles = array();
			$aScanFiles = scandir($sDir);
			
			foreach ($aScanFiles as $sScanFile) {
				if ($sScanFile == '..' || $sScanFile == '.') continue;
				$sFile = $sDir . '/' . $sScanFile;
				if (is_dir($sFile)) {
					$aDirFiles = self::aListFiles($sFile, $sFileType, true);
					foreach ($aDirFiles as $sDirFile) {
						$aFiles []= $sDirFile;
					}
				} else {
					if (!$sFileType || preg_match('#\.' . $sFileType . '$#', $sFile)) {
						$aFiles []= $sFile;
					}
				}
			}
			
			if (!$bAbsolute) {
				foreach ($aFiles as $iNr => $sFile) {
					$aFiles[$iNr] = str_replace(self::$sHtdocs . '/', '', $sFile);
				}
			}
			
			return $aFiles;
			
		}
		
		
		
		
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
		
		
		
		
		public static function vInit () {
			
			self::$sHtdocs = realpath(dirname(__FILE__) . '/..');
			
		}
		
		
		
		
	}
	
	
	
	
?>