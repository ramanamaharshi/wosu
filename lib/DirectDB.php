<?php
	
	
	
	
	class DirectDB {
		
		
		
		
		private $oMysqli = null;
		
		private $aDoNotEscape = array('NOW()');
		
		public $aTableMap = array();
		
		public $sEscapeTable = '';
		
		public $bDump = false;
		public $sLogFile = '';
		
		public static $oDefault = null;
		
		
		
		
		public static function vSetDefault ($aAccessData) {
			
			self::$oDefault = new self($aAccessData);
			
		}
		
		
		
		
		public function __construct ($aAccessData) {
			
			if (!isset($aAccessData['sHost'])) {
				$aAccessData['sHost'] = 'localhost';
			}
			
			$this->oMysqli = mysqli_connect($aAccessData['sHost'], $aAccessData['sUser'], $aAccessData['sPass'], $aAccessData['sDaba']);
			
		}
		
		
		
		
		public function aGetTables () {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$aTablesResult = $oInstance->aSelectQuery("SHOW TABLES;");
			
			$aTables = array();
			foreach ($aTablesResult as $oTable) {
				foreach ($oTable as $sKey => $sValue) {
					$sTable = $sValue;
					break;
				}
				$aTables []= $sTable;
			}
			
			return $aTables;
			
		}
		
		
		
		
		public function oSelectOne ($sTableName, $mWhere = array(), $sSelectFields = '*') {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$aRows = $oInstance->aSelect($sTableName, $mWhere, $sSelectFields);
			if (count($aRows) == 0) {
				return null;
			} else {
				return $aRows[0];
			}
			
		}
		
		
		
		
		
		public function aSelect ($sTableName, $mWhere = array(), $sSelectFields = '*') {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$sTableName = $oInstance->sConvertTableName($sTableName);
			
			$sWhere = $oInstance->sMakeWhere($mWhere);
			
			$sQuery = "
				SELECT " . $sSelectFields . " FROM " . $sTableName . "
				" . $sWhere . "
			";
			$oInstance->info($sQuery);
			$aResult = $oInstance->aSelectQuery($sQuery);
			$oInstance->info($aResult);
			return $aResult;
			
		}
		
		
		
		
		public function aSelectQuery ($sQuery) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			$oResult = $oInstance->aQuery($sQuery);
			if ($oResult === false) {
				$oInstance->vAutoError();
			}
			$aReturn = array();
			while ($aRawRow = mysqli_fetch_array($oResult)) {
				$oRow = new \stdClass();
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
			
			$sColumns = "";
			$sValues = "";
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
			
			$oInstance->info($sQuery);
			$bSuccess = $oInstance->aQuery($sQuery);
			$oInstance->info($bSuccess);
			if ($bSuccess === false) {
				$oInstance->vAutoError();
			}
			if ($bSuccess === false) {
				return false;
			}
			return mysqli_insert_id($oInstance->getDB());
			
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
			
			$oInstance->info($sQuery);
			$mReturn = $oInstance->aQuery($sQuery);
			$oInstance->info($mReturn);
			if ($mReturn === false) {
				$oInstance->vAutoError();
			}
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
			
			$oInstance->info($sQuery);
			$mReturn = $oInstance->aQuery($sQuery);
			$oInstance->info($mReturn);
			if ($mReturn === false) {
				$oInstance->vAutoError();
			}
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
				#\ODT::ec($sQuery);
			}
		}
		
		
		
		
		public function sMakeWhere ($mWhere) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			if (is_array($mWhere)) {
				$sWhere = "";
				$bFirst = true;
				foreach ($mWhere as $sKey => $mValue) {
					if ($bFirst) {
						$bFirst = false;
						$sWhere .= "WHERE ";
					} else {
						$sWhere .= " AND ";
					}
					if (is_array($mValue)) {
						$aValues = array();
						foreach ($mValue as $sValue) {
							$aValues []= "'" . $oInstance->sEscape($sValue) . "'";
						}
						$sWhere .= self::sProcessKey($sKey) . " IN (" . implode(',', $aValues) . ")";
					} else {
						$sValue = $mValue;
						$sWhere .= self::sProcessKey($sKey) . "='" . $oInstance->sEscape($sValue) . "'";
					}
				}
			} else {
				$sWhere = "WHERE " . $mWhere;
			}
			return $sWhere;
			
		}
		
		
		
		
		private static function sProcessKey ($sKey) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
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
		
		
		
		
		public function sConvertTableName ($sTableName) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			if (isset($oInstance->aTableMap[$sTableName])) {
				$sTableName = $oInstance->aTableMap[$sTableName];
			}
			return $sTableName;
			
		}
		
		
		
		
		public function aQuery ($sQuery) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			if (get_class($oInstance->getDB()) == 'mysqli') {
				return $oInstance->getDB()->query($sQuery);
			} else {
				return $oInstance->getDB()->sql_query($sQuery);
			}
			
		}
		
		
		
		
		public function sEscape ($mValue) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			if (get_class($oInstance->getDB()) == 'mysqli') {
				$mValue = $oInstance->getDB()->real_escape_string($mValue);
			} else {
				$mValue = $oInstance->getDB()->quoteStr($mValue, $oInstance->sEscapeTable);
			}
			
			return $mValue;
			
		}
		
		
		
		
		public function info ($mInfo) {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			if (class_exists('\ODT')) {
				if ($oInstance->bDump) \ODT::dump($mInfo);
				if ($oInstance->sLogFile) \ODT::log($mInfo, $oInstance->sLogFile);
			}
			
		}
		
		
		
		
		public function getDB () {
			
			$oInstance = (isset($this) && get_class($this) == __CLASS__) ? $this : self::$oDefault;
			
			return $oInstance->oMysqli;
			
		}
		
		
		
		
		public function __destruct () {
			
			mysqli_close($this->oMysqli);
			
		}
		
		
		
		
	}
	
	
	
	
?>