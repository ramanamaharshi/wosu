<?php


$sOdtDir = '/aaa/tools/odt';
if (file_exists($sOdtDir . '/init.php') && !class_exists('OlliInit')) {
	require_once($sOdtDir . '/init.php');
	if (class_exists('OlliInit')) {
		OlliInit::init(dirname(__FILE__), $sOdtDir . '/log');
	}
}


function aListFiles ($sDir) {
	$aFiles = array();
	$aScanFiles = scandir($sDir);
	foreach ($aScanFiles as $sScanFile) {
		if ($sScanFile == '..' || $sScanFile == '.') continue;
		$sFile = $sDir . '/' . $sScanFile;
		if (is_dir($sFile)) {
			$aDirFiles = aListFiles($sFile);
			foreach ($aDirFiles as $sDirFile) {
				$aFiles []= $sDirFile;
			}
		} else {
			$aFiles []= $sFile;
		}
	}
	return $aFiles;
}


require('lib/foreign/include_all.php');


spl_autoload_register(function ($sClass) {
	require_once('lib/' . $sClass . '.php');
	if (method_exists($sClass, 'vInit')) {
		$sClass::vInit();
	}
});


DirectDB::vSetDefault(array(
	'sUser' => 'wohnungssuche',
	'sPass' => 'c54effb3740c91a40760eda1e5c15319',
	'sDaba' => 'wohnungssuche',
));


#$aInitClasses = array();
#$aLibFiles = aListFiles('lib');
#foreach ($aLibFiles as $sLibFile) {
#	if (preg_match('#\.php$#', $sLibFile)) {
#		$sClass = preg_replace('#^.+/#', '', preg_replace('#\.php$#', '', $sLibFile));
#		require_once('lib/' . $sClass . '.php');
#		if (method_exists($sClass, 'vInit')) {
#			$aInitClasses []= $sClass;
#		}
#	}
#}
#foreach ($aInitClasses as $sInitClass) {
#	$sInitClass::vInit();
#}



