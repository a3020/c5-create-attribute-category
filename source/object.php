<?php 
defined('C5_EXECUTE') or die("Access Denied.");

class CamelCaseObject extends Object {
	public static $table = 'TABLE_NAME';
	
	function getCamelCaseObjectID() {
		return $this->itemID;
	}
	
	protected function load($ID) {
		$db = Loader::db();
		$row = $db->GetRow("SELECT * FROM ".self::$table." WHERE itemID = ?", array($ID));
		$this->setPropertiesFromArray($row);
	}
	
	public static function getByID($ID) {
		$ed = new self();
		$ed->load($ID);
		return $ed;
	}
	
	function add(){
		$db = Loader::db();
		$r = $db->query("INSERT INTO ".self::$table." VALUES(null)");
		
		if($r){
			$ed = self::getByID($db->Insert_ID());
			return $ed;
		}
	}
	
	
	public function delete() {
		$db = Loader::db();
		$db->Execute("delete from ".self::$table." where itemID = ?", array($this->itemID));
		
		$r = $db->Execute('select avID, akID from CamelCaseObjectAttributeValues where itemID = ?', array($this->itemID));
		Loader::model('attribute/categories/lowercase_object'PACKAGE_HANDLE);
		while ($row = $r->FetchRow()) {
			$uak = CamelCaseObjectAttributeKey::getByID($row['akID']);
			$av = $this->getAttributeValueObject($uak);
			if (is_object($av)) {
				$av->delete();
			}
		}
	}
	
	public function getAttribute($ak, $displayMode = false) {
		Loader::model('attribute/categories/lowercase_object'PACKAGE_HANDLE);
		
		if (!is_object($ak)) {
			$ak = CamelCaseObjectAttributeKey::getByHandle($ak);
		}
		
		if (is_object($ak)) {
			$av = $this->getAttributeValueObject($ak);
			if (is_object($av)) {
				$args = func_get_args();
				if (count($args) > 1) {
					array_shift($args);
					return call_user_func_array(array($av, 'getValue'), $args);						
				} else {
					return $av->getValue($displayMode);
				}
			}
		}
	}
	
	
	public function setAttribute($ak, $value) {
		Loader::model('attribute/categories/lowercase_object'PACKAGE_HANDLE);
		if (!is_object($ak)) {
			$ak = CamelCaseObjectAttributeKey::getByHandle($ak);
		}
		
		$ak->setAttribute($this, $value);
		$this->reindex();
	}
	
	
	public function reindex() {
		Loader::model('attribute/categories/lowercase_object'PACKAGE_HANDLE);
		$attribs = CamelCaseObjectAttributeKey::getAttributes($this->getCamelCaseObjectID(), 'getSearchIndexValue');
		
		$db = Loader::db();

		$db->Execute('delete from CamelCaseObjectSearchIndexAttributes where itemID = ?', array($this->getCamelCaseObjectID()));
		$searchableAttributes = array('itemID' => $this->getCamelCaseObjectID());
		$rs = $db->Execute('select * from CamelCaseObjectSearchIndexAttributes where itemID = -1');
		AttributeKey::reindex('CamelCaseObjectSearchIndexAttributes', $searchableAttributes, $attribs, $rs);
	}
	
	
	public function getAttributeValueObject($ak, $createIfNotFound = false) {
		Loader::model('attribute/categories/lowercase_object'PACKAGE_HANDLE);
		$db = Loader::db();
		$av = false;
		$v = array($this->getCamelCaseObjectID(), $ak->getAttributeKeyID());
		$avID = $db->GetOne("select avID from CamelCaseObjectAttributeValues where itemID = ? and akID = ?", $v);
		if ($avID > 0) {
			$av = CamelCaseObjectAttributeValue::getByID($avID);
			if (is_object($av)) {
				$av->setCamelCaseObject($this);
				$av->setAttributeKey($ak);
			}
		}
		
		if ($createIfNotFound) {
			$cnt = 0;
		
			// Is this avID in use ?
			if (is_object($av)) {
				$cnt = $db->GetOne("select count(avID) from CamelCaseObjectAttributeValues where avID = ?", $av->getAttributeValueID());
			}
			
			if ((!is_object($av)) || ($cnt > 1)) {
				$av = $ak->addAttributeValue();
			}
		}
		
		return $av;
	}
}