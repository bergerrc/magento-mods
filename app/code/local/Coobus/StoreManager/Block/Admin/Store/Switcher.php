<?php
/**
 * Coobus Consultoria de Processos
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   ACL
 * @package    Coobus_StoreManager
 * @copyright  Copyright (c) 2011 Coobus (http://www.coobus.com.br)
 * @author     Ricardo Custódio <ricardo@coobus.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Store switcher block Extension
 *
 * @package    Coobus_StoreManager
 * @author     Ricardo Custodio <ricardo@coobus.com.br>
 */
class Coobus_StoreManager_Block_Admin_Store_Switcher extends Mage_Adminhtml_Block_Store_Switcher
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('store/switcher.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultStoreName($this->__('All Store Views'));
        
        $this->_storeIds = Mage::helper('coobus_storemanager/data')->getAvailableStoreIds();
		$this->_hasDefaultOption = Mage::helper('coobus_storemanager/data')->getDefaultStoreId()==null; //Equivale a usuario admin
    }
	
    public function isShow()
    {
        return parent::isShow() && sizeof($this->_storeIds) > 1;
    }
    
    protected function _toHtml()
    {
        if ($this->isShow()) {
            return parent::_toHtml();
        }
        return '';
    }
}
