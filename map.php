<?php
	
	require_once('init.php');
	
#$aGeoCodes = DirectDB::aSelect('geocode_cache');
#foreach ($aGeoCodes as $oGeoCode) {
#	$oGeoCode->response = json_decode($oGeoCode->response);
#}
#ODT::vExit($aGeoCodes);
	
	$sGoogleMaps = 'https://maps.googleapis.com/maps/api/js?v=3&sensor=false';
	
	$aMarkers = array();
	$aAds = Ad::oGet(array(), false);
	foreach ($aAds as $oAd) {
		$oCoords = $oAd->oAddress->oCoords;
		$aMarker = array(
			'sTitle' => 'test',
			'nLat' => $oCoords->iY,
			'nLon' => $oCoords->iX,
		);
		$aMarkers []= $aMarker;
	}
	$aOptions = array(
		'zoom' => 13,
		'type' => 'terrain',
	);
	
	$sMapHtml = Maps::sMakeMapHtml('50.7753222,6.0838673', $aOptions, $aMarkers);
	$sContent = '<h1>Karte</h1>' . $sMapHtml;
	
	$sHeader = Html::sMakeHeader(array(), array());
	$sFooter = Html::sMakeFooter(array());
	
	echo $sHeader . $sContent . $sFooter;
	
?>