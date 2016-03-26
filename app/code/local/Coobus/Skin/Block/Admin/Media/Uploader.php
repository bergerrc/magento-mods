<?php

class Coobus_Skin_Block_Admin_Media_Uploader extends Mage_Adminhtml_Block_Media_Uploader
{
    public function __construct()
    {
        parent::__construct();
        $manager = Mage::helper('coobus_storemanager')->getManager();
        if ( $manager ){
        	foreach ( $manager->getStoreIds() as $id ){
	        	$type = Mage::getStoreConfig('catalog/management/uploadtype', $id);
				if ( $type && $type !== '0' ){
					$this->setTemplate('coobus/media/uploader/single.phtml');
				}
        	}
        }
    }
}