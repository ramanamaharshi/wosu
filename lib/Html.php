<?php
	
	
	
	
	class Html {
		
		
		
		
		private static $sTitle = '';
		private static $sContent = '';
		private static $aIncludes = array('header' => array(), 'footer' => array());
		private static $aAdditionalHtml = array('header' => array(), 'footer' => array());
		
		
		
		
		public static function vSetTitle ($sTitle) {
			
			self::$sTitle = $sTitle;
			
		}
		
		
		
		
		public static function vAddContent ($sContent) {
			
			self::$sContent .= $sContent;
			
		}
		
		
		
		
		public static function vInclude ($sLocation, $sFile, $bOnce = true) {
			
			self::$aIncludes[$sLocation] []= $sFile;
			
		}
		
		
		
		
		public static function vAddHtml ($sLocation, $sHtml) {
			
			self::$aAdditionalHtml[$sLocation] []= $sHtml;
			
		}
		
		
		
		
		public static function sMakeAdditionalHtml ($sLocation) {
			
			$sAdditionalHtml = implode("\n", self::$aAdditionalHtml[$sLocation]);
			
			foreach (self::$aIncludes[$sLocation] as $sInclude) {
				$sAdditionalHtml .= self::sMakeIncludeHtml($sInclude);
			}
			
			return $sAdditionalHtml;
			
		}
		
		
		
		
		public static function vIncludeAllPublic () {
			
			foreach (array('js', 'css') as $sType) {
				
				$aFiles = Utilitu::aListFiles(Utilitu::$sHtdocs . '/public/' . $sType, $sType);
				foreach ($aFiles as $sFile) {
					self::vInclude('header', '/' . $sFile);
				}
				
			}
			
		}
		
		
		
		
		public static function sMakeIncludeHtml ($sLink, $sFileType = null) {
			
			$sReturn = null;
			
			$sLink = self::sFileToLink($sLink);
			
			if (!$sFileType) $sFileType = Utilitu::sPregRead($sLink, '#\.([^\.]+)$#');
			
			if ($sFileType == 'js') {
				$sReturn = '<script type="text/javascript" src="' . htmlspecialchars($sLink) . '"></script>';
			}
			if ($sFileType == 'css') {
				$sReturn = '<link rel="stylesheet" type="text/css" media="all" href="' . htmlspecialchars($sLink) . '" />';
			}
			
			return $sReturn;
			
		}
		
		
		
		
		public static function sFileToLink ($sFile) {
			
			if (!preg_match('#/#', $sFile)) {
				if (!preg_match('#\.[^\.]+$#', $sFile)) {
					$sFile = $sFile . '.js';
				}
				if (preg_match('#\.js$#', $sFile)) {
					if (file_exists(Utilitu::$sHtdocs . '/lib/js/' . $sFile)) {
						$sFile = '/lib/js/' . $sFile;
					}
				}
				if (preg_match('#\.css$#', $sFile)) {
					if (file_exists(Utilitu::$sHtdocs . '/assets/css/' . $sFile)) {
						$sFile = '/assets/css/' . $sFile;
					}
				}
			}
			
			return $sFile;
			
		}
		
		
		
		
		public static function sMakeHeader () {
			
			$sHeader = trim('
				<!doctype html>
				<html>
					<head>
						<meta charset="UTF-8">
						<title>' . self::$sTitle . '</title>
						<link rel="shortcut icon" href="/favicon.ico" />
						<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
						<meta name="robots" content="noindex,follow" />
						' . self::sMakeAdditionalHtml('header') . '
					</head>
					<body>
			');
			
			return $sHeader;
			
		}
		
		
		
		
		public static function sMakeFooter () {
			
			$sFooter = '
						' . self::sMakeAdditionalHtml('footer') . '
					</body>
				</html>
			';
			
			return $sFooter;
			
		}
		
		
		
		
		public static function sMakePage () {
			
			return self::sMakeHeader() . self::$sContent . self::sMakeFooter();
			
		}
		
		
		
		
		public static function vInit () {
			
			self::vIncludeAllPublic();
			
		}
		
		
		
		
	}
	
	
	
	
?>