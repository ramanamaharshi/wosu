<?php
	
	
	
	
	class Html {
		
		
		
		
		public static function sMakeHeader ($aJsFiles = array(), $aCssFiles = array(), $sAdditionalHtml = '', $sTitle = '') {
			
			$aJsIncludes = array();
			foreach ($aJsFiles as $sJsFile) {
				$aJsIncludes []= self::sMakeCssInclude($sJsFile);
			}
			
			$aCssIncludes = array();
			foreach ($aCssFiles as $sCssFile) {
				$aCssIncludes []= self::sMakeCssInclude($sCssFile);
			}
			
			$sHeader = trim('
				<!doctype html>
				<html>
					<head>
						<meta charset="UTF-8">
						<title>' . $sTitle . '</title>
						<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
						<link rel="shortcut icon" href="/favicon.ico" />
						<meta name="robots" content="noindex,follow" />
						' . implode("\n", $aCssIncludes) . '
						' . implode("\n", $aJsIncludes) . '
						' . $sAdditionalHtml . '
					</head>
					<body>
			');
			
			return $sHeader;
			
		}
		
		
		
		
		public static  function sMakeFooter ($aJsFiles = array(), $aCssFiles = array()) {
			
			$aJsIncludes = array();
			foreach ($aJsFiles as $sJsFile) {
				$aJsIncludes []= self::sMakeCssInclude($sJsFile);
			}
			
			$aCssIncludes = array();
			foreach ($aCssFiles as $sCssFile) {
				$aCssIncludes []= self::sMakeCssInclude($sCssFile);
			}
			
			$sFooter = '
						' . implode("\n", $aCssIncludes) . '
						' . implode("\n", $aJsIncludes) . '
					</body>
				</html>
			';
			
			return $sFooter;
			
		}
		
		
		
		
		public static function sMakeCssInclude ($sCssFile) {
			
			$sLink = self::sFileToLink($sCssFile);
			$sCssInclude = '<link rel="stylesheet" type="text/css" media="all" href="' . htmlspecialchars($sLink) . '" />';
			return $sCssInclude;
			
		}
		
		
		
		
		public static function sMakeJsInclude ($sJsFile) {
			
			$sLink = self::sFileToLink($sJsFile);
			$sJsInclude = '<script type="text/javascript" src="' . htmlspecialchars($sLink) . '"></script>';
			return $sJsInclude;
			
		}
		
		
		
		
		public static function sFileToLink ($sFile) {
			
			return $sFile;
			
		}
		
		
		
		
	}
	
	
	
	
?>