<?php
	
	
	
	
	class Ad {
		
		
		
		
		public static function oGetByUrl ($sUrl) {
			
			$oReturn = null;
			
			$sUrlHash = Utilitu::sConditionalHash($sUrl);
			
			$aAds = DirectDB::aSelectQuery("
				SELECT ads.id AS id , ads_html.url_hash AS url_hash FROM ads
				JOIN ads_html ON ads_html.id = ads.html_id
				" . DirectDB::sMakeWhere(array('url_hash' => $sUrlHash)) . "
			");
			if ($aAds) $oReturn = self::oGet($aAds[0]->id);
			
			return $oReturn;
			
		}
		
		
		
		
		public static function oGet ($mWhere = array()) {
			
			$oReturn = null;
			
			if (!is_array($mWhere) && !is_object($mWhere)) {
				$mWhere = array('id' => intval($mWhere));
			}
			
			$aAds = self::aGet($mWhere);
			
			if (isset($aAds[0])) $oReturn = $aAds[0];
			
			return $oReturn;
			
		}
		
		
		
		
		public static function aGet ($mWhere = array()) {
			
			$aResult = DirectDB::aSelect('ads', $mWhere);
			
			$aAds = array();
			foreach ($aResult as $oRow) {
				$aAds []= new Ad($oRow);
			}
			
			return $aAds;
			
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
				'fetched' => $this->oPage->sFetched,
				'json_data' => json_encode($this->oData),
			);
			
			if ($this->iID) {
				DirectDB::bUpdate('ads', $aData, $this->iID);
			} else {
				$this->iID = DirectDB::iInsert('ads', $aData);
			}
			
		}
		
		
		
		
		public static function iInsertHtml ($sList, $sUrl, $sContent, $sFetched = null) {
			
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
			);
			
			$iHtmlID = DirectDB::iInsert('ads_html', $aInsert);
			
			return $iHtmlID;
			
		}
		
		
		
		
		public static function oGetHtml ($iHtmlID) {
			
			return DirectDB::oSelectOne('ads_html', intval($iHtmlID));
			
		}
		
		
		
		
		public static function vWipeDatabase () {
			if (isset($_REQUEST['restructure'])) {
				DirectDB::aQuery("DROP TABLE ads;");
				DirectDB::aQuery("DROP TABLE ads_html;");
			}
		}
		
		
		
		
		public static function vInit () {
			
			DirectDB::aQuery("
				CREATE TABLE IF NOT EXISTS ads_html (
					id int(9) NOT NULL AUTO_INCREMENT,
					fetched datetime,
					parsed tinyint(1),
					list varchar(32),
					domain_hash varchar(32),
					url_hash varchar(32),
					html_hash varchar(32),
					html longtext,
					INDEX (url_hash),
					PRIMARY KEY (id)
				) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
			");
			
			DirectDB::aQuery("
				CREATE TABLE IF NOT EXISTS ads (
					id int(9) NOT NULL AUTO_INCREMENT,
					html_id int(9),
					created datetime,
					changed datetime,
					fetched datetime,
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
		
		
		
		
		private $iID = null;
		
		#private $aData = array(
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