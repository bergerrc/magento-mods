<?php

class Coobus_Catalog_Block_Adminhtml_Catalog_Product extends Mage_Adminhtml_Block_Catalog_Product
{
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setChild('grid', $this->getLayout()->createBlock('coobus_catalog/adminhtml_catalog_product_grid', 'product.grid'));
    }
}

