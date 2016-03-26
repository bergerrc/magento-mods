<?php
class Coobus_Catalog_Model_Convert_Parser_Product_Number extends Mage_Catalog_Model_Convert_Parser_Product{
	
    /**
     * Dataflow batch model
     *
     * @var Mage_Dataflow_Model_Batch
     */
    protected $_batch;

    /**
     * Dataflow batch export model
     *
     * @var Mage_Dataflow_Model_Batch_Export
     */
    protected $_batchExport;
    
    protected $BACKORDER_OPTIONS;

    public function __construct()
    {
    	parent::__construct();
		$this->BACKORDER_OPTIONS = array( 	
	    	Mage_CatalogInventory_Model_Stock::BACKORDERS_NO => Mage::helper('adminhtml')->__('No'),
			Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY => Mage::helper('adminhtml')->__('Yes'),
			Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY => Mage::helper('adminhtml')->__('Specified')
		);
	}
	
    /**
     * Retrieve Batch model singleton
     *
     * @return Mage_Dataflow_Model_Batch
     */
    public function getBatchModel()
    {
        if (is_null($this->_batch)) {
            $this->_batch = Mage::getSingleton('dataflow/batch');
        }
        return $this->_batch;
    }
    
    public function getBatchExportModel()
    {
        if (is_null($this->_batchExport)) {
            $object = Mage::getModel('dataflow/batch_export');
            $this->_batchExport = Varien_Object_Cache::singleton()->save($object);
        }
        return Varien_Object_Cache::singleton()->load($this->_batchExport);
    }
	public function unparse(){
		
        $batchModel  = $this->getBatchModel();
        $batchExport = $this->getBatchExportModel();

        $batchExportIds = $batchExport
            ->setBatchId($this->getBatchModel()->getId())
            ->getIdCollection();
        
        $changed = false;
	    foreach ($batchExportIds as $batchExportId) {
            $batchExport = $this->getBatchExportModel()->load($batchExportId);
            $row = $batchExport->getBatchData();

            foreach ($row as $field=>$value) {
            	$attribute = $this->getAttribute($field);
            	$oldValue = $row[$field];
            	
                if (!$attribute) {
                	if ( !(strpos($field, 'qty') === false) )
            			$row[$field] = $this->getNumber($value, 0);
            		if ( $field == 'backorders' )
	                	$row[$field] = $this->BACKORDER_OPTIONS[$value];      
	                if ( $field == 'category_ids' ){
	                	$row['categories'] = $this->getProductCategories(explode(',',$value));      
	                }	                	
                }else{
	                if ($value && $attribute->getBackendType() == 'decimal') {
	                	$row[$field] = $this->getNumber($value);
	            	}else if ($value && $attribute->getBackendType() == 'int' && is_numeric($value) ) {
	            		$row[$field] = $this->getNumber($value, 0);
	            	}
                }
                $changed = ($oldValue !== $row[$field]) || $changed;
            }

            if ( $changed ){
	            $batchExport->setBatchData($row)
	                ->setStatus(2)
	                ->save();
            }
        }
		
        return $this;
	}
	protected $_categoryCache = array();
	public function getProductCategories( $category_ids = array() ){
		$category_ids = array_combine($category_ids, $category_ids);
     	$result = '';
        if (!count($category_ids)) {
            return $result;
        }
        if ( !count( array_diff($category_ids, $this->_categoryCache) ) ){
        	foreach($category_ids as $cat){
        		$result .= ($result?',':'').$this->_categoryCache[$cat]['fullname'];
        	}
        	return $result;
        }
        	
        $collection = Mage::getModel('catalog/category')->getCollection()->addIdFilter($category_ids);
        $parentIds = array();
        foreach ( $collection as $cat ){
        	foreach ( explode('/', $cat->getPath()) as $id )
        		if ( !in_array($id, $parentIds) ) $parentIds[$id]=$id; 
        }
        $collection = Mage::getModel('catalog/category')->getCollection()
        			->addNameToResult()
        			->addIdFilter( array_merge($category_ids, $parentIds));

	 $all = array();
        foreach( $collection as $cat ){
        	$all[$cat->getId()] = array('level'=> $cat->getLevel(),'parent'=> $cat->getParentId(),'name'=>$cat->getName());
        }

        foreach($category_ids as $cat){
        	$c = $all[$cat];
        	while ( $c['level'] > 1 ){
        		$all[$cat]['fullname'] = $c['name'] . ($all[$cat]['fullname']? '/'.$all[$cat]['fullname']:'');
        		$c = $all[$c['parent']];
        	}
        	$result .= ($result?',':'').$all[$cat]['fullname'];
        }
        array_merge($all, $this->_categoryCache);
        return $result;
	}
	
    public function getNumber($value, $decimals = 2)
    {
		$separator = $this->getVar('decimal_separator')? $this->getVar('decimal_separator'):'.';

        return number_format($value, $decimals, $separator, '');
    }
}