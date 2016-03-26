<?php

class Coobus_Catalog_Block_Adminhtml_Catalog_Product_Edit_Tab_Quickentry
extends Mage_Adminhtml_Block_Widget_Grid implements Mage_Adminhtml_Block_Widget_Tab_Interface 
//extends Mage_Catalog_Block_Product_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('coobus/catalog/product/tab/quickentry.phtml');
        $this->setId('quickentry_product_grid');
        $this->setUseAjax(true);
    }

    public function getTabClass()
    {
        return 'ajax';
    }
    
    public function canShowTab()
    {
        return true;
    }
    
    public function isHidden()
    {
        return false;
    }

    public function getTabTitle()
    {
        return Mage::helper('coobus_catalog')->__('Quick');
    }
    
    public function getTabLabel()
    {
        return $this->getTabTitle();
    }
    
    /**
     * Return current product instance
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }
    
    protected function _prepareLayout()
    {
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
        	$headBlock->addJs('varien/product.js');
        	$headBlock->addJs('varien/configurable.js');
        	$headBlock->addItem('js_css','calendar/calendar-win2k-1.css');
        	$headBlock->addItem('js','calendar/calendar.js');
        	$headBlock->addItem('js','calendar/calendar-setup.js');
        }
    	
        $this->setChild('media', 
        	$this->getLayout()->createBlock('catalog/product_view_media')
        					  ->setTemplate('coobus/catalog/product/view/media.phtml')
        );
        $this->setChild('alert_urls', 
        	$this->getLayout()->createBlock('core/text_list')
        );
        
        $infotabs = $this->getLayout()->createBlock('catalog/product_view_tabs')
        					 		  ->setTemplate('coobus/catalog/product/view/tabs.phtml');
		$infotabs->addTab('description','Product Description','catalog/product_view_description','coobus/catalog/product/view/description.phtml');
		$infotabs->addTab('additional','Additional Information','catalog/product_view_attributes','coobus/catalog/product/view/attributes.phtml');
		$infotabs->addTab('upsell_products','We Also Recommend','catalog/product_list_upsell','coobus/catalog/product/view/upsell.phtml');
        $this->setChild('info_tabs', $infotabs);
        
        $this->setChild('product_additional_data', 
        	$this->getLayout()->createBlock('catalog/product_view_additional')
        					  ->setTemplate('coobus/catalog/product/view/additional.phtml')
        );

        $this->setTierPriceTemplate('coobus/catalog/product/view/tierprices.phtml');
    	
        $container1 = $this->getLayout()->createBlock('core/template_facade');
        $container1->setDataByKey('alias_in_layout','container1');
        $container1->setDataByKeyFromRegistry('options_container','product');
        $container1->append('product.info.options.wrapper');
        $container1->append('product.info.options.wrapper.bottom');
        $this->setChild('container1', $container1);
        
        $container2 = $this->getLayout()->createBlock('core/template_facade');
        $container2->setDataByKey('alias_in_layout','container2');
        $container2->setDataByKeyFromRegistry('options_container','product');
        $container2->append('product.info.options.wrapper');
        $container2->append('product.info.options.wrapper.bottom');
        $this->setChild('container2', $container2);

        return parent::_prepareLayout();
    }
    
    public function canEmailToFriend(){
    	return false;
    }
    
    /**
     * Return true if product has options
     *
     * @return bool
     */
    public function hasOptions()
    {
        if ($this->getProduct()->getTypeInstance(true)->hasOptions($this->getProduct())) {
            return true;
        }
        return false;
    }

    /**
     * Check if product has required options
     *
     * @return bool
     */
    public function hasRequiredOptions()
    {
        return $this->getProduct()->getTypeInstance(true)->hasRequiredOptions($this->getProduct());
    }
    
    public function getJsonConfig()
    {
        $config = array();
        if (!$this->hasOptions()) {
            return Mage::helper('core')->jsonEncode($config);
        }

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_regularPrice = $this->getProduct()->getPrice();
        $_finalPrice = $this->getProduct()->getFinalPrice();
        $_priceInclTax = Mage::helper('tax')->getPrice($this->getProduct(), $_finalPrice, true);
        $_priceExclTax = Mage::helper('tax')->getPrice($this->getProduct(), $_finalPrice);

        $config = array(
            'productId'           => $this->getProduct()->getId(),
            'priceFormat'         => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax'          => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax'      => Mage::helper('tax')->displayPriceIncludingTax(),
            'showBothPrices'      => Mage::helper('tax')->displayBothPrices(),
            'productPrice'        => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('core')->currency($_regularPrice, false, false),
            /**
             * @var skipCalculate
             * @deprecated after 1.5.1.0
             */
            'skipCalculate'       => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'idSuffix'            => '_clone',
            'oldPlusDisposition'  => 0,
            'plusDisposition'     => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition'    => 0,
        );

        $responseObject = new Varien_Object();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object'=>$responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option=>$value) {
                $config[$option] = $value;
            }
        }

        return Mage::helper('core')->jsonEncode($config);
    }
    
    public function getChildHtml($s){
    	return parent::getChildHtml($s);
    }
}
