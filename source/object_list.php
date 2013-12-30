<?php 
defined('C5_EXECUTE') or die("Access Denied."); 

class CamelCaseObjectList extends DatabaseItemList { 

	protected $attributeFilters = array();
	protected $itemsPerPage = 10;
	protected $attributeClass = 'CamelCaseObjectAttributeKey';
	
	
	public function get($itemsToGet = 100, $offset = 0) {
		$list = array(); 
		$this->createQuery();
		$r = parent::get( $itemsToGet, intval($offset));
		foreach($r as $row) {
			$ui = CamelCaseObject::getByID($row['ATTRIBUTE_ID']);			
			$list[] = $ui;
		}
		return $list;
	}
	
	public function getTotal(){ 
		$this->createQuery();
		return parent::getTotal();
	}	
	
	//this was added because calling both getTotal() and get() was duplicating some of the query components
	protected function createQuery(){
		if(!$this->queryCreated){
			$this->setBaseQuery();
			$this->queryCreated=1;
		}
	}
	
	protected function setBaseQuery() {
		$this->setQuery('SELECT tbl.ATTRIBUTE_ID FROM TABLE_NAME tbl ');
		
		$this->setupAttributeFilters("left join CamelCaseObjectSearchIndexAttributes on (CamelCaseObjectSearchIndexAttributes.ATTRIBUTE_ID = tbl.ATTRIBUTE_ID)");
	}

	/* magic method for filtering by page attributes. */
	public function __call($nm, $a) {
		if (substr($nm, 0, 8) == 'filterBy') {
			$txt = Loader::helper('text');
			$attrib = $txt->uncamelcase(substr($nm, 8));
			if (count($a) == 2) {
				$this->filterByAttribute($attrib, $a[0], $a[1]);
			} else {
				$this->filterByAttribute($attrib, $a[0]);
			}
		}			
	}
	
	
	public function sortByATTRIBUTE_IDDescending() {
		parent::sortBy('tbl.ATTRIBUTE_ID', 'desc');
	}
}