<?php
	
	require_once('init.php');
	
	if (isset($_REQUEST['restructure'])) {
		Maps::vWipeDatabase();
		Ad::vWipeDatabase();
	}
	
	if (isset($_REQUEST['ad_html'])) {
		$oAds = DirectDB::oSelectOne('ads', array('id' => intval($_REQUEST['ad_html'])));
		$oHtml = DirectDB::oSelectOne('ads_html', array('id' => intval($oAds->html_id)));
		$sHtml = $oHtml->html;
		ODT::vDump(WgGesuchtReader::oParseAdHtml(null, $sHtml));
		exit($sHtml);
	}
	
	if (isset($_REQUEST['fetch'])) {
		WgGesuchtReader::vRead();
	}
	
?>