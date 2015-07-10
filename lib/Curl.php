<?php
	
	
	
	
	class Curl {
		
		
		
		
		private static $sCookieJarFolder = '/tmp/phpcurl/cookiejars';
		
		public static $oDefault;
		
		
		
		
		private $sCookieJarFile = null;
		
		
		
		
		public function __construct () {}
		
		
		
		
		public function sGet ($sUrl, $sCookieJarFile = '[auto]') {
ODT::vDump('sGet ' . $sUrl);
			
			$oInstance = isset($this) ? $this : self::$oDefault;
			
			$oCurl = curl_init();
			
			curl_setopt($oCurl, CURLOPT_URL, $sUrl);
			
			curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
			
			curl_setopt($oCurl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
			
			if ($sCookieJarFile == '[auto]') {
				if (!$oInstance->sCookieJarFile) {
					$oInstance->sCreateCookieJarFile();
				}
				$sCookieJarFile = $oInstance->sCookieJarFile;
			}
ODT::vDump($sCookieJarFile);
			
			if ($sCookieJarFile) {
				curl_setopt($oCurl, CURLOPT_COOKIEJAR, $sCookieJarFile);
				curl_setopt($oCurl, CURLOPT_COOKIEFILE, $sCookieJarFile);
			}
			
			$sResponse = curl_exec($oCurl);
			
			curl_close($oCurl);
			
			return $sResponse;
			
		}
		
		
		
		
		public function sCreateCookieJarFile ($bUse = true) {
			
			$oInstance = isset($this) ? $this : self::$oDefault;
			
			$sFile = self::$sCookieJarFolder . '/' . self::sMicrotime() . '_' . self::sRandomString(8) . '.cookie';
			
			file_put_contents($sFile, '');
			
			if ($bUse) $oInstance->vSetCookieJarFile($sFile);
			
			return $sFile;
			
		}
		
		
		
		
		public function vSetCookieJarFile ($sFile) {
			
			$oInstance = isset($this) ? $this : self::$oDefault;
			
			$oInstance->sCookieJarFile = $sFile;
			
		}
		
		
		
		function vInit () {
			
			self::$oDefault = new self();
			
		}
		
		
		
		
	}
	
	
	
	
?>