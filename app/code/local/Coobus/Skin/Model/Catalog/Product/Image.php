<?php

class Coobus_Skin_Model_Catalog_Product_Image extends Mage_Catalog_Model_Product_Image
{
    /**
     * @return Varien_Image
     */
    public function getImageProcessor()
    {
        if( !$this->_processor ) {
            $this->_processor = new Coobus_Skin_Helper_ImageProcessor($this->getBaseFile());
        }
        return parent::getImageProcessor();
    }
}
