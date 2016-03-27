<?php
	
	
	
	
	require_once('../init.php');
	
	Utilitu::vReturnJson(array('aAds' => Ad::aGet("
		SELECT a.* FROM ads AS a
		JOIN ads_htmls AS h ON a.html_id = h.id
		WHERE h.list != 'wg0' AND h.fetched > '" . date('Y-m-d H:i:s', strtotime('now - 3 days')) . "';
	")));
	
	
	
	
?>