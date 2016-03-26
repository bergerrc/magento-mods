<?php

class Coobus_StoreManager_Block_Admin_Catalog_Product_Edit_Tab_Categories extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('storemanager/catalog/product/edit/categories.phtml');
    }

}
