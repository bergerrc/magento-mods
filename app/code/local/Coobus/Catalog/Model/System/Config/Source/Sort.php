<?php

class Coobus_Catalog_Model_System_Config_Source_Sort
{

	public function toOptionArray()
    	{
        	return array(
            	0 => Mage::helper('adminhtml')->__('Random'),
            	1 => Mage::helper('adminhtml')->__('Last Added')
        	);
    	}
}
