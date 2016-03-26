<?php

class Coobus_Catalog_Adminhtml_FeaturedController extends Mage_Adminhtml_Controller_Action
{
	
	protected function _initProduct()
    {
        
        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->getRequest()->getParam('store', 0));

            if ($setId = (int) $this->getRequest()->getParam('set')) {
                $product->setAttributeSetId($setId);
            }

            if ($typeId = $this->getRequest()->getParam('type')) {
                $product->setTypeId($typeId);
            }
                    
        $product->setData('_edit_mode', true);
        
        Mage::register('product', $product);
       
        return $product;
    }

	public function indexAction()
	{
        $this->_initProduct();
        
		$this->loadLayout()->_setActiveMenu('catalog/featuredproducts');
			
		$this->_addContent($this->getLayout()->createBlock('coobus_catalog/adminhtml_edit'));
        
        $this->renderLayout();
	
	}
	
	public function gridAction()
	{
		 
	$this->getResponse()->setBody(
            $this->getLayout()->createBlock('coobus_catalog/adminhtml_edit_grid')->toHtml()
        );
	
	}
	
	public function saveAction()
	{
		$data = $this->getRequest()->getPost(); 
        $collection = Mage::getModel('catalog/product')->getCollection();
		$storeId        = $this->getRequest()->getParam('store', 0);
		
		         
        parse_str($data['featured_products'], $featured_products);
		
		
        $collection->addIdFilter(array_keys($featured_products));
        
		 try {
		 	
			foreach($collection->getItems() as $product)
			{
				
				$product->setData('is_featured',$featured_products[$product->getEntityId()]);
				$product->setStoreId($storeId);		
		  		$product->save();	
			} 	
		 	
			
		 	$this->_getSession()->addSuccess($this->__('Featured product was successfully saved.'));
			$this->_redirect('*/*/index', array('store'=> $this->getRequest()->getParam('store')));	
		 	
		 }catch (Exception $e){
			$this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/index', array('store'=> $this->getRequest()->getParam('store'))); 
		 }
	
	}
	
	    protected function _validateSecretKey()
    {
    	return true;
    }
	
	
}