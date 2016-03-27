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
	
	$bFetch = (isset($_REQUEST['fetch']) || isset($_REQUEST['fetch_and_parse']));
	$bParse = (isset($_REQUEST['parse']) || isset($_REQUEST['fetch_and_parse']));
	
	if (isset($_REQUEST['search_html'])) {
		if (isset($_REQUEST['url'])) {
			$aWhere = array('url' => array('%like%' => $_REQUEST['url']));
			$oHtml = DirectDB::oSelectOne('ads_htmls', $aWhere, 'id , url', 'ORDER BY fetched DESC LIMIT 1');
			ODT::vDump($oHtml);
		}
	}
	
	if ($bFetch) {
		Ad::vDeleteDuplicateUrlAds();
		WgGesuchtReader::vFetch();
	}
	
	if ($bParse) {
		$iParse = intval($_REQUEST['parse']);
		if ($iParse) {
			$oAd = WgGesuchtReader::oParseHtml($iParse);
			ODT::vDump($oAd);
			$oHtml = Ad::oGetHtml($iParse);
			ODT::vDump($oHtml);
		} else {
			$aHtmlIDs = Ad::aGetLatestHtmlIDs('999 days');
			foreach ($aHtmlIDs as $iID) {
				WgGesuchtReader::oParseHtml($iID);
			}
		}
	}
	
	if (isset($_REQUEST['show'])) {
		$oHtml = Ad::oGetHtml($_REQUEST['show']);
		$sHtml = $oHtml->html;
		echo($sHtml);
	}
	
?>