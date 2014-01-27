<?php 
defined('C5_EXECUTE') or die("Access Denied.");

class CamelCaseObjectAttributeKey extends AttributeKey {

	public function getIndexedSearchTable() {
		return 'TABLE_NAMESearchIndexAttributes';
	}

	protected $searchIndexFieldDefinition = 'ATTRIBUTE_ID I(11) UNSIGNED NOTNULL DEFAULT 0 PRIMARY';

	/** 
	 * Returns an attribute value list of attributes and values (duh) which a *** version can store 
	 * against its object.
	 * @return AttributeValueList
	 */
	public function getAttributes($ATTRIBUTE_ID, $method = 'getValue') {
		$db = Loader::db();
		$values = $db->GetAll("select akID, avID from TABLE_NAMEAttributeValues where ATTRIBUTE_ID = ?", array($ATTRIBUTE_ID));
		$avl = new AttributeValueList();
		foreach($values as $val) {
			$ak = CamelCaseObjectAttributeKey::getByID($val['akID']);
			if (is_object($ak)) {
				$value = $ak->getAttributeValue($val['avID'], $method);
				$avl->addAttributeValue($ak, $value);
			}
		}
		return $avl;
	}
	

	public function getAttributeValue($avID, $method = 'getValue') {
		$av = CamelCaseObjectAttributeValue::getByID($avID);
		if (is_object($av)) {
			$av->setAttributeKey($this);
			return $av->{$method}();
		}
	}
	
	public static function getByID($akID) {
		$ak = new CamelCaseObjectAttributeKey();
		$ak->load($akID);
		if ($ak->getAttributeKeyID() > 0) {
			return $ak;	
		}
	}

	public static function getByHandle($akHandle) {
		$ak = CacheLocal::getEntry('lowercase_object_attribute_key_by_handle', $akHandle);
		if (is_object($ak)) {
			return $ak;
		} else if ($ak == -1) {
			return false;
		}
		
		$ak = new CamelCaseObjectAttributeKey();
		$ak->load($akHandle, 'akHandle');
		if ($ak->getAttributeKeyID() < 1) {
			$ak = -1;
		}

		CacheLocal::set('lowercase_object_attribute_key_by_handle', $akHandle, $ak);

		if ($ak === -1) {
			return false;
		}

		return $ak;
	}
	
	public static function getList() {
		return parent::getList('lowercase_object');	
	}
	
	
	public static function getColumnHeaderList() {
		return parent::getList('lowercase_object', array('akIsColumnHeader' => 1));	
	}
	public static function getSearchableIndexedList() {
		return parent::getList('lowercase_object', array('akIsSearchableIndexed' => 1));	
	}

	public static function getSearchableList() {
		return parent::getList('lowercase_object', array('akIsSearchable' => 1));	
	}
	
	/** 
	 * @access private 
	 */
	public function get($akID) {
		return CamelCaseObjectAttributeKey::getByID($akID);
	}
	
	protected function saveAttribute($object, $value = false) {
		// We check a ATTRIBUTE_ID/cvID/akID combo, and if that particular combination has an attribute value ID that
		// is NOT in use anywhere else on the same ATTRIBUTE_ID, cvID, akID combo, we use it (so we reuse IDs)
		// otherwise generate new IDs
		$av = $object->getAttributeValueObject($this, true);
		parent::saveAttribute($av, $value);
		$db = Loader::db();
		$v = array($object->getCamelCaseObjectID(), $this->getAttributeKeyID(), $av->getAttributeValueID());
		$db->Replace('TABLE_NAMEAttributeValues', array(
			'ATTRIBUTE_ID' => $object->getCamelCaseObjectID(), 
			'akID' => $this->getAttributeKeyID(), 
			'avID' => $av->getAttributeValueID()
		), array('ATTRIBUTE_ID', 'akID'));
		unset($av);
	}
	
	public function add($at, $args, $pkg = false) {

		// legacy check
		$fargs = func_get_args();
		if (count($fargs) >= 5) {
			$at = $fargs[4];
			$pkg = false;
			$args = array('akHandle' => $fargs[0], 'akName' => $fargs[1], 'akIsSearchable' => $fargs[2]);
		}

		CacheLocal::delete('lowercase_object_attribute_key_by_handle', $args['akHandle']);

		$ak = parent::add('lowercase_object', $at, $args, $pkg);
		return $ak;
	}
	
	public function delete() {
		parent::delete();
		$db = Loader::db();
		$r = $db->Execute('select avID from TABLE_NAMEAttributeValues where akID = ?', array($this->getAttributeKeyID()));
		while ($row = $r->FetchRow()) {
			$db->Execute('delete from AttributeValues where avID = ?', array($row['avID']));
		}
		$db->Execute('delete from TABLE_NAMEAttributeValues where akID = ?', array($this->getAttributeKeyID()));
	}

}

class CamelCaseObjectAttributeValue extends AttributeValue {

	public function setCamelCaseObject($object) {
		$this->item = $object;
	}
	
	public static function getByID($avID) {
		$cav = new CamelCaseObjectAttributeValue();
		$cav->load($avID);
		if ($cav->getAttributeValueID() == $avID) {
			return $cav;
		}
	}

	public function delete() {
		$db = Loader::db();
		$db->Execute('delete from TABLE_NAMEAttributeValues where ATTRIBUTE_ID = ? and akID = ? and avID = ?', array(
			$this->item->getCamelCaseObjectID(), 
			$this->attributeKey->getAttributeKeyID(),
			$this->getAttributeValueID()
		));
		
		// Before we run delete() on the parent object, we make sure that attribute value isn't being referenced in the table anywhere else
		$num = $db->GetOne('select count(avID) from TABLE_NAMEAttributeValues where avID = ?', array($this->getAttributeValueID()));
		if ($num < 1) {
			parent::delete();
		}
	}
}