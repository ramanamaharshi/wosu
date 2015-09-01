<?php
	
	require_once('init.php');
	
	if (isset($_REQUEST['restructure'])) {
		Maps::vWipeDatabase();
		Ad::vWipeDatabase();
	}
	
	if (isset($_REQUEST['ad_html'])) {
		$oAds = DirectDB::oSelectOne('ads', array('id' => intval($_REQUEST['ad_html'])));
		$oHtml = DirectDB::oSelectOne('ads_htmls', array('id' => intval($oAds->html_id)));
		$sHtml = $oHtml->html;
		ODT::vDump(WgGesuchtReader::oParseHtml(null, $sHtml));
		exit($sHtml);
	}
	
	if (isset($_REQUEST['fetch'])) {
		WgGesuchtReader::vFetch();
	}
	
	if (isset($_REQUEST['search_html'])) {
		if (isset($_REQUEST['url'])) {
			$aWhere = array('url' => array('%like%' => $_REQUEST['url']));
			$oHtml = DirectDB::oSelectOne('ads_htmls', $aWhere, 'id , url', 'ORDER BY fetched DESC LIMIT 1');
			ODT::vDump($oHtml);
		}
	}
	
	if (isset($_REQUEST['parse'])) {
		$iParse = intval($_REQUEST['parse']);
		if ($iParse) {
			$oAd = WgGesuchtReader::oParseHtml($iParse);
			ODT::vDump($oAd);
			$oHtml = Ad::oGetHtml($iParse);
			ODT::vDump($oHtml);
		} else {
			$aHtmlIDs = DirectDB::aSelect('ads_htmls', array(), 'id');
			foreach ($aHtmlIDs as $oHtmlID) {
				WgGesuchtReader::oParseHtml($oHtmlID->id);
			}
		}
		
	}
	
	if (isset($_REQUEST['show'])) {
		$oHtml = Ad::oGetHtml($_REQUEST['show']);
		$sHtml = $oHtml->html;
		echo($sHtml);
	}
	
?>