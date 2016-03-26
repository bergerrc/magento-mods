<?php

class Coobus_Skin_Helper_ImageProcessor extends Varien_Image
{
    public function cropArea($x, $y, $w, $h, $offSetX, $offSetY)
	{
	     $this->_getAdapter()->cropArea($x, $y, $w, $h, $offSetX, $offSetY);
	}
/*	
    protected function _getAdapter($adapter=null)
    {
    	if ( $adapter == Varien_Image_Adapter::ADAPTER_GD2 ) 
    		$this->_adapter = new Coobus_Skin_Helper_ImageAdapterGd2();
    	else
    		$this->_adapter = parent::_getAdapter($adapter);
    	return $this->_adapter;
    }
*/
}
