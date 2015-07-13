<?php

require_once('init.php');

Html::vInclude('header', 'maps.js');

Html::vAddContent('test');

echo Html::sMakePage();
exit;

#DirectDB::aQuery("SHOW TABLES;");

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

exit;

#HtmlDomParser::str_get_html('');

$oAdA = new Ad();

$oAdA->oPage->sDomain = 'a';
$oAdA->vSave();

$oAdB = Ad::oGet(33);

ODT::vExit($oAdB);
