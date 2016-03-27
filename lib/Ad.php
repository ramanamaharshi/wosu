<?php
	
	
	
	
	class Ad {
		
		
		
		
		#public static function oGetByUrl ($sUrl) {
		#	
		#	$oReturn = null;
		#	
		#	$sUrlHash = Utilitu::sConditionalHash($sUrl);
		#	
		#	$aAds = DirectDB::aSelectQuery("
		#		SELECT ads.id AS id , ads_htmls.url_hash AS url_hash FROM ads
		#		JOIN ads_htmls ON ads_htmls.id = ads.html_id
		#		" . DirectDB::sMakeWhere(array('url_hash' => $sUrlHash)) . "
		#	");
		#	if ($aAds) $oReturn = self::oGet($aAds[0]->id);
		#	
		#	return $oReturn;
		#	
		#}
		
		
		
		
		public static function oGet ($mWhere = array()) {
			
			$oReturn = null;
			
			if (!is_array($mWhere) && !is_object($mWhere)) {
				$mWhere = array('id' => intval($mWhere));
			}
			
			$aAds = self::aGet($mWhere);
			
			if (isset($aAds[0])) $oReturn = $aAds[0];
			
			return $oReturn;
			
		}
		
		
		
		
		public static function aGet ($sQuery = null) {
			
			if (!$sQuery) $sQuery = "SELECT * FROM ads;";
			
			$aResult = DirectDB::aSelectQuery($sQuery);
			
			$aAds = array();
			foreach ($aResult as $oRow) {
				$aAds []= new Ad($oRow);
			}
			
			return $aAds;
			
		}
		
		
		
		
		public static function vDeleteDuplicateUrlAds () {
			
			$aAllUrlRows = DirectDB::aSelect('ads_htmls', array(), 'url', 'GROUP BY url');
			$aAllUrls = Utilitu::aExtractFieldFromArray($aAllUrlRows, 'url');
			
			foreach ($aAllUrls as $sUrl) {
				$aUrlAdIDs = Ad::aGetAdIDsByUrl($sUrl);
				$iPopped = array_pop($aUrlAdIDs);
				DirectDB::bDelete('ads', array('id' => $aUrlAdIDs));
			}
			
		}
		
		
		
		
		public static function iRemoveAdsByUrl ($sUrl) {
			
			$aIDs = self::aGetAdIDsByUrl($sUrl);
			
			DirectDB::bDelete('ads', array('id' => $aIDs));
			
			return count($aIDs);
			
		}
		
		
		
		
		public static function aGetAdIDsByUrl ($sUrl) {
			
			$aWhere = array('url_hash' => Utilitu::sConditionalHash($sUrl));
			$aHtmls = DirectDB::aSelect('ads_htmls', $aWhere, 'id');
			$aHtmlIDs = Utilitu::aExtractFieldFromArray($aHtmls, 'id', 'intval');
			$aAdRows = DirectDB::aSelect('ads', array('html_id' => $aHtmlIDs));
			$aAdIDs = Utilitu::aExtractFieldFromArray($aAdRows, 'id', 'intval');
			
			return $aAdIDs;
			
		}
		
		
		
		
		public function __construct ($oRow = null) {
			
			if ($oRow) {
				
				$this->iID = intval($oRow->id);
				$this->oData = json_decode($oRow->json_data);
				
			} else {
				
				$aData = $this->oData;
				$this->oData = new StdClass();
				foreach ($aData as $sKey => $aDataField) {
					$oDataField = (object) $aDataField;
					foreach ($oDataField as $sFieldKey => $mFieldValue) {
						if (substr($sFieldKey, 0, 1) == 'o' && is_array($mFieldValue)) {
							$oDataField->{$sFieldKey} = (object) $mFieldValue;
						}
					}
					$this->oData->$sKey = $oDataField;
				}
				
			}
			
		}
		
		
		
		
		public function vSave () {
			
			$aData = array(
				'html_id' => $this->oPage->iHtmlID,
				'created' => $this->oPage->sCreated,
				'changed' => $this->oPage->sChanged,
				'json_data' => json_encode($this->oData),
			);
			
			if ($this->iID) {
				DirectDB::bUpdate('ads', $aData, $this->iID);
			} else {
				$this->iID = DirectDB::iInsert('ads', $aData);
			}
			
		}
		
		
		
		
		public static function iSaveHtml ($sList, $sUrl, $sContent, $sFetched = null) {
			
			if (!$sFetched) $sFetched = date('Y-m-d H:i:s');
			
			$sDomain = Utilitu::sUrlToDomain($sUrl);
			
			$aInsert = array(
				'list' => Utilitu::sConditionalHash($sList),
				'domain_hash' => Utilitu::sConditionalHash($sDomain),
				'url_hash' => Utilitu::sConditionalHash($sUrl),
				'html_hash' => md5($sContent),
				'html' => $sContent,
				'fetched' => $sFetched,
				'parsed' => 0,
				'url' => $sUrl,
			);
			
			$iHtmlID = DirectDB::iInsert('ads_htmls', $aInsert);
			
			return $iHtmlID;
			
		}
		
		
		
		
		public static function oGetHtml ($iHtmlID) {
			
			return DirectDB::oSelectOne('ads_htmls', intval($iHtmlID));
			
		}
		
		
		
		
		public static function oGetLatestHtmlForUrl ($sUrl) {
			
			$sExtra = 'ORDER BY fetched DESC LIMIT 1';
			$sWhere = array('url_hash' => Utilitu::sConditionalHash($sUrl));
			$oReturn = DirectDB::oSelectOne('ads_htmls', $sWhere, '*', $sExtra);
			
			return $oReturn;
			
		}
		
		
		
		
		public static function oGetEarliestHtmlForUrl ($sUrl) {
			
			$sExtra = 'ORDER BY fetched ASC LIMIT 1';
			$sWhere = array('url_hash' => Utilitu::sConditionalHash($sUrl));
			$oReturn = DirectDB::oSelectOne('ads_htmls', $sWhere, '*', $sExtra);
			
			return $oReturn;
			
		}
		
		
		
		
		public static function aGetLatestHtmlIDs ($sTimespan = '1 day') {
			
			$aIDs = array();
			
			$sStart = date('Y-m-d H:i:s', strtotime('now - ' . $sTimespan));
			
			$aRows = DirectDB::aSelectQuery("
				SELECT a.id , a.fetched FROM ads_htmls AS a
					LEFT JOIN ads_htmls As b
						ON a.id != b.id
							AND a.url_hash = b.url_hash
							AND a.fetched < b.fetched
					WHERE a.fetched > '" . $sStart . "'
						AND b.id IS NULL;
			");
			
			foreach ($aRows as $oRow) {
				$aIDs []= $oRow->id;
			}
			
			return $aIDs;
			
		}
		
		
		
		
		public static function vWipeDatabase () {
			if (isset($_REQUEST['restructure'])) {
				DirectDB::mQuery("DROP TABLE ads;");
				DirectDB::mQuery("DROP TABLE ads_htmls;");
			}
		}
		
		
		
		
		public static function vInit () {
			
			DirectDB::mQuery("
				CREATE TABLE IF NOT EXISTS ads_htmls (
					id int(9) NOT NULL AUTO_INCREMENT,
					fetched datetime,
					parsed tinyint(1),
					list varchar(32),
					domain_hash varchar(32),
					url_hash varchar(32),
					html_hash varchar(32),
					html longtext,
					url text,
					INDEX (url_hash),
					PRIMARY KEY (id)
				) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			");
			
			DirectDB::mQuery("
				CREATE TABLE IF NOT EXISTS ads (
					id int(9) NOT NULL AUTO_INCREMENT,
					html_id int(9),
					created datetime,
					changed datetime,
					json_data longtext,
					PRIMARY KEY (id)
				) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			");
			
		}
		
		
		
		
		public function __get ($sFieldName) {
			
			if (isset($this->oData->$sFieldName)) {
				return $this->oData->$sFieldName;
			}
			
		}
		
		
		
		
		public $iID = null;
		
		public $oData = array(
			'oPage' => array(
				'sDomain' => null,
				'sUrl' => null,
				'sListID' => null,
				'sCreated' => null,
				'sChanged' => null,
				'sFetched' => null,
				'iHtmlID' => null,
				'sDescription' => null,
				'aImages' => array(),
			),
			'oAddress' => array(
				'sCity' => null,
				'sZip' => null,
				'sStreet' => null,
				'oCoords' => array(
					'nX' => null,
					'nY' => null,
				),
			),
			'oPrice' => array(
				'iWarm' => null,
				'iCold' => null,
				'iNeben' => null,
				'iHeating' => null,
				'iOther' => null,
				'iBail' => null,
				'iBuy' => null,
			),
			'oContact' => array(
				'sName' => null,
				'sPhone' => null,
				'sEmail' => null,
			),
			'oPhysical' => array(
				'nSquareMeters' => null,
			),
		);
		
		
		
		
	}
	
	
	
	
?>