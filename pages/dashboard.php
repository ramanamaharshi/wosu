<?php
	
	require_once('../init.php');
	
	Html::vAddContent('<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true"></script>');
	
	Html::vAddContent('
		<script type="text/javascript">
			$(document).ready(function(){
				oDashboard.vInit();
			});
		</script>
	');
	
	echo Html::sMakePage();
	
?>