<?php

require_once('init.php');

#DirectDB::aQuery("SHOW TABLES;");

WgGesuchtReader::vRead();

exit;

#HtmlDomParser::str_get_html('');

$oAdA = new Ad();

$oAdA->oPage->sDomain = 'a';
$oAdA->vSave();

$oAdB = Ad::oGet(33);

ODT::vExit($oAdB);
