<?php
class Mage_Banners_Block_Content extends Mage_Core_Block_Template
{
	const CACHE_TAG              = 'banners';
	
    protected function _construct()
    {
        parent::_construct();

        $this->addData(array(
            'cache_lifetime'    => 86400,
            'cache_tags'        => array(self::CACHE_TAG),
        ));
    }
	
    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
           'BANNERS',
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           'template' => $this->getTemplate()
        );
    }
    
	public function getBanners() {
		return Mage::helper("banners")->getBanners();
	}
}