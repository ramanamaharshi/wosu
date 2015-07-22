<?php
	
	
	
	
	require_once('../init.php');
	
	Utilitu::vReturnJson(array('aAds' => Ad::aGet()));
	
	
	
	
?>