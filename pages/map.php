<?php
	
	require_once('../init.php');
	
#$aGeoCodes = DirectDB::aSelect('geocode_cache');
#foreach ($aGeoCodes as $oGeoCode) {
#	$oGeoCode->response = json_decode($oGeoCode->response);
#}
#ODT::vExit($aGeoCodes);
	
	$sGoogleMaps = 'https://maps.googleapis.com/maps/api/js?v=3&sensor=false';
	
	$aMarkerIcon = array(
		'url' => '/public/img/marker_icon_a.svg',
		'scaledSize' => array(20,20),
		'bounds' => array(5,0,10,20),
		'origin' => array(5,20),
	);
	$aMarkers = array();
	$aAds = Ad::oGet(array(), false);
	foreach ($aAds as $oAd) {
		$oCoords = $oAd->oAddress->oCoords;
		$aMarker = array(
			'title' => 'test',
			'icon' => $aMarkerIcon,
			'position' => array(
				'nLat' => $oCoords->iY,
				'nLon' => $oCoords->iX,
			),
		);
		$aMarkers []= $aMarker;
	}
	$aOptions = array(
		'zoom' => 13,
		'type' => 'satellite',
		'center' => '50.7753222,6.0838673',
	);
	
	$sMapHtml = Maps::sMakeMapHtml($aOptions, $aMarkers);
	$sContent = '<!--h1>Karte</h1-->' . $sMapHtml;
	
	$sHeader = Html::sMakeHeader(array(), array());
	$sFooter = Html::sMakeFooter(array());
	
	echo $sHeader . $sContent . $sFooter;
	
?>