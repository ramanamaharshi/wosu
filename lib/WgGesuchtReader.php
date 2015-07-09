<?php
	
	
	
	
	class WgGesuchtReader {
		
		
		
		
		public static $sDomain = null;
		public static $sImagesFolder = 'store/images/';
		
		
		
		
		public static function vRead () {
			
			$aDetailUrls = array();
			$aListUrls = array(
				#'http://' . self::$sDomain . '/wohnungen-in-Aachen.1.0.0.0.html?filter=bb469e0d555c453d123cbe951feef700938d503a025e2e0a3a',
				#'http://' . self::$sDomain . '/wohnungen-in-Aachen.1.1.0.0.html',
				'http://' . self::$sDomain . '/wohnungen-in-Aachen.1.2.0.0.html',
			);
			foreach ($aListUrls as $sListUrl) {
				$sListHtml = str_replace('[[CODE]]', 'w' . 'g-g' . 'esucht', file_get_contents('examples/list_2.html'));
				$aDetailUrlsInList = self::aReadList($sListHtml);
				$aDetailUrls = array_merge($aDetailUrls, $aDetailUrlsInList);
			}
			
			$aAds = array();
			foreach ($aDetailUrls as $sDetailUrl) {
				$sDetailHtml = str_replace('[[CODE]]', 'w' . 'g-g' . 'esucht', file_get_contents('examples/detail_a.html'));
				$sTime = date('Y-m-d H:i:s');
				$iHtmlID = Ad::iInsertHtml($sDetailUrl, $sDetailHtml, $sTime);
				$oAd = Ad::oGetByUrl($sDetailUrl);
				if (!$oAd) $oAd = new Ad();
				$oAd->oPage->sDomain = self::$sDomain;
				$oAd->oPage->sUrl = $sDetailUrl;
				$oAd->oPage->sFetched = $sTime;
				$oAd->oPage->iHtmlID = $iHtmlID;
				self::oParseAdHtml($oAd, $sDetailHtml);
				$oAd->vSave();
				$aAds []= $oAd;
ODT::vExit($oAd->oData);
			}
			
		}
		
		
		
		
		public static function aReadList ($sListHtml) {
			
			$oReturn = array();
			
			$oListDom = HtmlDomParser::str_get_html($sListHtml);
			$oTableBody = $oListDom->find('#table-compact-list tbody', 0);
			$aHearts = $oTableBody->find('.list-details-to-fav');
			
			foreach ($aHearts as $oHeart) {
				$oRow = $oHeart->parent()->parent();
				$oAnchor = $oRow->find('td.ang_spalte_datum a', 0);
				if (!$oAnchor) continue;
				$sHref = $oAnchor->attr['href'];
				$sUrl = 'http://' . self::$sDomain . '/' . $sHref;
				$aReturn []= $sUrl;
			}
			
			return $aReturn;
			
		}
		
		
		
		
		public static function oParseAdHtml ($oAd, $sDetailHtml) {
			
			if (!$oAd) $oAd = new Ad();
			
			$oDom = HtmlDomParser::str_get_html($sDetailHtml);
			$oMainInfo = $oDom->find('.panel-body > .row', 0);
			
			$sOrange = $oDom->find('.headline-key-facts', 0)->innertext;
			$sSquareMeters = Utilitu::sPregRead($sOrange, '#Größe: ([,\d]+)m#');
			$oAd->oPhysical->nSquareMeters = floatval($sSquareMeters);
			
			$aKostenRows = $oMainInfo->find('.col-sm-5 tbody tr');
			$aKostenRowsByLabel = array();
			foreach ($aKostenRows as $oKostenRow) {
				$aCells = $oKostenRow->find('td');
				$sLabel = trim($aCells[0]->plaintext, "\t\n :");
				$sValue = trim($aCells[1]->plaintext, "\t\n ");
				$iPrice = 100 * intval(str_replace(array(',', '&euro;'), array('.', ''), $sValue));
				$aKostenRowsByLabel[$sLabel] = array(
					'oDom' => $oKostenRow,
					'sLabel' => $sLabel,
					'sValue' => $sValue,
					'iPrice' => $iPrice,
				);
			}
			$aKostenMap = array(
				'iCold' => 'Miete',
				'iNeben' => 'Nebenkosten',
				'iOther' => 'Sonstige Kosten',
				'iBail' => 'Kaution',
				'iBuy' => 'Abschlagszahlung',
			);
			foreach ($aKostenMap as $sTarget => $sSource) {
				if (isset($aKostenRowsByLabel[$sSource])) {
					$oAd->oPrice->{$sTarget} = $aKostenRowsByLabel[$sSource]['iPrice'];
				}
			}
			$oAd->oPrice->iWarm  = $oAd->oPrice->iCold + $oAd->oPrice->iNeben;
			
			$sAddressHtml = $oMainInfo->find('.col-sm-4 > p', 0)->innertext;
			$sAddress = trim($sAddressHtml);
			$sAddress = str_replace("\n", '', $sAddress);
			$sAddress = preg_replace('#<br />\s+#', "\n", $sAddress);
			$aAddress = explode("\n", $sAddress);
			$oAd->oAddress->sCity = 'Aachen';
			$oAd->oAddress->sZip = Utilitu::sPregRead($aAddress[0], '#\s*(\d+)#');
			$oAd->oAddress->sStreet = $aAddress[1];
			
			$oCoords = Maps::oGetCoords($oAd->oAddress->sZip . ' ' . $oAd->oAddress->sStreet);
			$oAd->oAddress->oCoords = $oCoords;
			
			$aImageDoms = $oDom->find('.Gallery img');
			foreach ($aImageDoms as $oImageDom) {
				$oImage = new StdClass();
				$oImage->sUrl = str_replace('/./', '/', $oImageDom->attr['data-original']);
				$sFileType = Utilitu::sPregRead($oImage->sUrl, '#\.([^\.]+)$#');
				$oImage->sFile = self::$sImagesFolder . md5($oImage->sUrl) . '.' . $sFileType;
				if (!file_exists($oImage->sFile)) {
					file_put_contents($oImage->sFile, Curl::sGet($oImage->sUrl));
				}
				$oAd->oPage->aImages []= $oImage;
			}
			
			$aDescription = array();
			$aDescriptionBlocks = $oDom->find('#infobox_nachrichtsenden', 0)->parent->find('.freitext');
			foreach ($aDescriptionBlocks as $oDescriptionBlock) {
				$aDescription []= $oDescriptionBlock->plaintext;
			}
			$oAd->oPage->sDescription = implode("\n\n", $aDescription);
			$oAd->oPage->sDescription = preg_replace('#\n\s+#', "\n", $oAd->oPage->sDescription);
			$oAd->oPage->sDescription = str_replace('&nbsp;', '', $oAd->oPage->sDescription);
			
			$aPotentialDates = $oDom->find('.col-sm-4 .col-sm-12');
			foreach ($aPotentialDates as $oPotentialDate) {
				if (preg_match('#^\s*Angebot vom:\s*(.+)\s*$#', $oPotentialDate->plaintext, $aMatch)) {
					$sDate = date('Y-m-d H:i:s', strtotime($aMatch[1]));
					$oAd->oPage->sCreated = $sDate;
					$oAd->oPage->sChanged = $sDate;
				}
			}
			
			/// TODO: oContact
			
			return $oAd;
			
		}
		
		
		
		
		public static function vInit () {
			
			self::$sDomain = 'ww' . 'w.w' . 'g-g' . 'esuch' . 't.de';
			
		}
		
		
		
		
	}
	
	
	
	
?>