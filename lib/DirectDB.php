<?php
	
	
	
	
	class DirectDB {
		
		
		
		
		/// version 1.2
		
		
		
		
		public $bLog = false;
		public $sLogFile = '';
		public $aTableMap = array();
		
		private $aDoNotEscape = array('NOW()');
		private $sDatabaseType = '';
		
		public static $oDefault = null;
		
		
		
		
		public static function vSetDefault ($aAccessData) {
			
			self::$oDefault = new self($aAccessData);
			
		}
		
		
		
		
		public function __construct ($aAccessData = null) {
			
			$oInstance = $this;
			
			if ($aAccessData) {
				$oInstance->sDatabaseType = 'mysqli';
				if (!isset($aAccessData['sHost'])) $aAccessData['sHost'] = 'localhost';
				$oInstance->oDB = mysqli_connect(
					$aAccessData['sHost'],
					$aAccessData['sUser'],
					$aAccessData['sPass'],
					$aAccessData['sDaba']
				);
				if (mysqli_connect_errno($oInstance->oDB)) {
					exit('failed to connect to ' . $sDaba . ' on ' . $sHost);
				}
			} else {
				if ($aAccessData == 'wordpress') {
					$oInstance->sDatabaseType = 'wordpress';
					global $wpdb;
					$oInstance->oDB = $wpdb;
				}
			}
			
		}
		
		
		
		
		public function __destruct () {
			
			$oInstance = $this;
			
			if ($oInstance->sDatabaseType == 'mysqli') {
				mysqli_close($oInstance->oDB);
			}
			
		}
		
		
		
		
		public function bCreateTable ($sTable, $sPrimary, $aColums) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$sCI = "\t\t\t";
			
			foreach ($aColums as $sName => $sAttrs) {
				if (is_array($sAttrs)) {
					$sLine = implode(' ', array(
						$aAttrs['sField'],
						$aAttrs['sType'],
						$aAttrs['bNull'] ? 'NULL' : 'NOT NULL',
						($aAttrs['sDefault'] === '') ? '' : 'DEFAULT ' . $aAttrs['sDefault'],
						$aAttrs['sExtra'],
					));
					$aColumnQueries []= $sCI . $sAttrs;
				} else {
					$sLine = $sName . ' ' . $sAttrs;
					$aColumnQueries []= $sCI . $sLine;
				}
			}
			
			$sQuery = '
				CREATE TABLE ' . $sTable . ' (
					' . $sPrimary . ' INT NOT AUTO INCREMENT,
					' . implode("\n", $aColumnsQueries) . ',
					PRIMARY KEY (' . $sPrimary . ')
				);
			';
			
			$bSuccess = $oInstance->mQuery($sQuery);
			
			return $bSuccess;
			
		}
		
		
		
		
		public function aGetTableColumns ($sTable) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$aColumnData = $oInstance->aSelectQuery("SHOW COLUMNS FROM " . $sTable);
			
		}
		
		
		
		
		public function aGetTables () {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$aTablesResult = $oInstance->aSelectQuery("SHOW TABLES;");
			
			$aTables = array();
			foreach ($aTablesResult as $oTable) {
				$sTable = $oTable->{'Tables_in_' . $oInstance->sDaba};
				$aTables []= $sTable;
			}
			
			return $aTables;
			
		}
		
		
		
		
		public function oSelectOne ($sTableName, $mWhere = array(), $sSelectFields = '*', $sSpecial = '') {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$aRows = $oInstance->aSelect($sTableName, $mWhere, $sSelectFields, $sSpecial);
			if (count($aRows) == 0) {
				return null;
			} else {
				return $aRows[0];
			}
			
		}
		
		
		
		
		public function aSelect ($sTableName, $mWhere = array(), $sSelectFields = '*', $sSpecial = '') {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$sTableName = $oInstance->sConvertTableName($sTableName);
			
			$sWhere = $oInstance->sMakeWhere($mWhere);
			
			$sQuery = "
				SELECT " . $sSelectFields . " FROM " . $sTableName . "
				" . $sWhere . "
				" . $sSpecial . "
			";
			
			$oInstance->vLog($sQuery);
			$aResult = $oInstance->aSelectQuery($sQuery);
			$oInstance->vLog($aResult);
			
			return $aResult;
			
		}
		
		
		
		
		public function aSelectQuery ($sQuery) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$oResult = $oInstance->mQuery($sQuery);
			
			if ($oResult === false) $oInstance->vAutoError();
			
			$aReturn = array();
			while ($aRawRow = mysqli_fetch_array($oResult)) {
				$oRow = new stdClass();
				foreach ($aRawRow as $sKey => $sValue) {
					if (is_string($sKey)) {
						$oRow->$sKey = $sValue;
					}
				}
				$aReturn []= $oRow;
			}
			
			return $aReturn;
			
		}
		
		
		
		
		public function iInsert ($sTableName, $mData) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$sTableName = $oInstance->sConvertTableName($sTableName);
			
			$sValues = "";
			$sColumns = "";
			$bFirst = true;
			foreach ($mData as $sKey => $sValue) {
				if ($bFirst) {
					$bFirst = false;
				} else {
					$sColumns .= ",";
					$sValues .= ",";
				}
				if (in_array($sValue, $oInstance->aDoNotEscape)) {
					$sEscapedValue = $sValue;
				} else {
					$sEscapedValue = "'" . $oInstance->sEscape($sValue) . "'";
				}
				$sColumns .= self::sProcessKey($sKey);
				$sValues .= $sEscapedValue;
			}
			$sColumns = "(" . $sColumns . ")";
			$sValues = "(" . $sValues . ")";
			
			$sQuery = "
				INSERT INTO " . $sTableName . "
				" . $sColumns . "
				VALUES " . $sValues . "
			";
			
			$oInstance->vLog($sQuery);
			$bSuccess = $oInstance->mQuery($sQuery);
			$oInstance->vLog($bSuccess);
			
			if ($bSuccess === false) {
				$oInstance->vAutoError();
				return false;
			}
			
			return $oInstance->iGetInsertID();
			
		}
		
		
		
		
		public function bUpdate ($sTableName, $mData, $mWhere = array()) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$sTableName = $oInstance->sConvertTableName($sTableName);
			
			$sSet = "";
			$bFirst = true;
			foreach ($mData as $sKey => $sValue) {
				if ($bFirst) {
					$bFirst = false;
				} else {
					$sSet .= ",";
				}
				if (in_array($sValue, $oInstance->aDoNotEscape)) {
					$sEscapedValue = $sValue;
				} else {
					$sEscapedValue = "'" . $oInstance->sEscape($sValue) . "'";
				}
				$sSet .= self::sProcessKey($sKey) . "=" . $sEscapedValue;
			}
			
			$sWhere = $oInstance->sMakeWhere($mWhere);
			
			$sQuery = "
				UPDATE " . $sTableName . "
				SET " . $sSet . "
				" . $sWhere . "
			";
			
			$oInstance->vLog($sQuery);
			$mReturn = $oInstance->mQuery($sQuery);
			$oInstance->vLog($mReturn);
			
			if ($mReturn === false) $oInstance->vAutoError();
			
			return $mReturn;
			
		}
		
		
		
		
		public function bDelete ($sTableName, $mWhere = array()) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$sTableName = $oInstance->sConvertTableName($sTableName);
			
			$sWhere = $oInstance->sMakeWhere($mWhere);
			
			$sQuery = "
				DELETE FROM `" . $sTableName . "`
				" . $sWhere . "
			";
			
			$oInstance->vLog($sQuery);
			$mReturn = $oInstance->mQuery($sQuery);
			$oInstance->vLog($mReturn);
			
			if ($mReturn === false) $oInstance->vAutoError();
			
			return $mReturn;
			
		}
		
		
		
		
		public function vAutoError () {
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			if (class_exists('\ODT')) {
				if (get_class($oInstance->getDB()) == 'mysqli') {
					$sError = $oInstance->getDB()->error;
				} else {
					$sError = $oInstance->getDB()->sql_error();
				}
				\ODT::ec('ERROR: ' . $sError);
			}
		}
		
		
		
		
		public function sMakeWhere ($mWhere) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			if (is_numeric($mWhere)) {
				$mWhere = array('id' => $mWhere);
			}
			
			$aWhere = array($mWhere);
			if (is_array($mWhere)) {
				$aWhere = array();
				foreach ($mWhere as $sKey => $mValue) {
					if (is_array($mValue)) {
						if (isset($mValue['%like%'])) {
							$sValue = $mValue['%like%'];
							$aWhere []= self::sProcessKey($sKey) . " LIKE '%" . $oInstance->sEscape($sValue) . "%'";
						} else {
							$aValues = array();
							foreach ($mValue as $sValue) {
								$aValues []= "'" . $oInstance->sEscape($sValue) . "'";
							}
							$aWhere []= self::sProcessKey($sKey) . " IN (" . implode(',', $aValues) . ")";
						}
					} else {
						$sValue = $mValue;
						$aWhere []= self::sProcessKey($sKey) . "='" . $oInstance->sEscape($sValue) . "'";
					}
				}
			}
			
			$sWhere = '';
			if (count($aWhere)) {
				$sWhere = "WHERE " . implode(" AND ", $aWhere);
			}
			
			return $sWhere;
			
		}
		
		
		
		
		private static function sProcessKey ($sKey) {
			
			if (strstr($sKey, '.' === false)) {
				$sKey = "`" . $sKey . "`";
			} else {
				$aKeyParts = explode('.', $sKey);
				foreach ($aKeyParts as $i => $sKeyPart) {
					$aKeyParts[$i] = "`" . $sKeyPart . "`";
				}
				$sKey = implode('.', $aKeyParts);
			}
			
			return $sKey;
			
		}
		
		
		
		
		public function iGetInsertID () {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$iReturn = null;
			
			switch ($oInstance->sDatabaseType) {
				case 'mysqli':{
					$sReturn = $oInstance->oGetDB()->insert_id;
				}
				case 'wordpress':{
					$iReturn = intval($oInstance->oGetDB()->insert_id);
				}
			}
			
			return $iReturn;
			
		}
		
		
		
		
		public function sGetLastError () {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$sReturn = null;
			
			switch ($oInstance->sDatabaseType) {
				case 'mysqli':{
					$sReturn = $oInstance->oGetDB()->error;
				}
				case 'wordpress':{
					$sReturn = $oInstance->oGetDB()->last_error;
				}
			}
			
			return $sReturn;
			
		}
		
		
		
		
		public function sEscape ($sInput) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$sReturn = null;
			
			switch ($oInstance->sDatabaseType) {
				case 'mysqli':{
					if (get_class($oInstance->oGetDB()) == 'mysqli') {
						$mValue = $oInstance->oGetDB()->real_escape_string($sInput);
					} else {
						$mValue = $oInstance->oGetDB()->quoteStr($sInput, $oInstance->sEscapeTable);
					}
				}
				case 'wordpress':{
					$sReturn = mysqli_real_escape_string($oInstance->oGetDB(), $sInput);
				}
			}
			
			return $sReturn;
			
		}
		
		
		
		
		public function mQuery ($sQuery) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			return $oInstance->oGetDB()->query($sQuery);
			
		}
		
		
		
		
		public function oGetDB () {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			return $oInstance->oDB;
			
		}
		
		
		
		
		public function vLog ($mInfo) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			if ($oInstance->sLogFile) ODT::log($mInfo, $oInstance->sLogFile);
			if ($oInstance->bLog) ODT::dump($mInfo);
			
		}
		
		
		
		
		public function sConvertTableName ($sTableName) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			if (isset($oInstance->aTableMap[$sTableName])) {
				$sTableName = $oInstance->aTableMap[$sTableName];
			}
			return $sTableName;
			
		}
		
		
		
		
		public function vInitTables ($sTableGroupID, $sUpdateFunctionPattern) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$sStoreTable = 'direct_db_store';
			
			$oInstance->mQuery('
				CREATE TABLE IF NOT EXISTS ' . $sStoreTable . ' (
				`id` int(10) unsigned NOT NULL auto_increment,
				`key` varchar(64) NOT NULL,
				`value` text NOT NULL,
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
			');
			
			$sKey = 'table_group_version_' . $sTableGroupID;
			
			$oVersionRow = $oInstance->oSelectOne($sStoreTable, array('key' => $sKey));
			if ($oVersionRow) {
				$sCurrentVersion = $oVersionRow->value;
			} else {
				$sCurrentVersion = '0';
				$oInstance->iInsert($sStoreTable, array('key' => $sKey, 'version' => $sCurrentVersion));
			}
			
			$aUpdateFunctionPattern = explode('::', sUpdateFunctionPattern);
			if (isset($aUpdateFunctionPattern[1])) {
				$sClass = $aUpdateFunctionPattern[0];
				$sFunctionPart = $aUpdateFunctionPattern[1];
				$aClassFunctions = get_class_methods($sClass);
				$aFunctions = array();
				foreach ($aClassFunctions as $sFunction) {
					$aFunctions []= $sClass . '::' . $aClassFunctions;
				}
			} else {
				$sFunctionPart = $aUpdateFunctionPattern[0];
				$aFunctions = get_defined_functions();
			}
			$aUpdateFunctions = array();
			foreach ($aFunctions as $sFunction) {
				if (preg_match('/^' . $sUpdateFunctionPattern . '_(?<version>\d+(_\d+)*)$/', $sFunction, $aMatchesA)) {
					$sVersion = str_replace('_', '.', $aMatchesA['version']);
					$aUpdateFunctions[$sVersion] = $sFunction;
				}
			}
			uksort($aUpdateFunctions, 'version_compare');
			
			foreach ($aUpdateFunctions as $sVersion => $sFunction) {
				if (version_compare($sCurrentVersion, $sVersion) < 0) {
					$bSuccess = call_user_func_array($sFunction, array($oInstance));
					if ($bSuccess) {
						$sCurrentVersion = $sVersion;
						$oInstance->bUpdate($sStoreTable, array('key' => $sKey, 'version' => $sCurrentVersion));
						self::$aAdminNotices []= 'tp_likes: updated database to version ' . $sVersion;
					} else {
						self::$aAdminNotices []= 'tp_likes: failed to update database to version ' . $sVersion;
						return;
					}
				}
			}
			
		}
		
		
		
		
	}
	
	
	
	
?>