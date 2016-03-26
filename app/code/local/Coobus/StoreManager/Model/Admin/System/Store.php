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
 * Adminhtml System Store Model
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Coobus_StoreManager_Model_Admin_System_Store extends Mage_Adminhtml_Model_System_Store
{

    private $_storeIds = array();


    protected function getStoreIds()
    {
        return $this->_storeIds;
    }
    
    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * @see Mage_Adminhtml_Model_System_Store
     */
    protected function _loadStoreCollection()
    {
    	$_stores = Mage::app()->getStores();
        foreach ($_stores as $store) {
        	if ( !in_array($store->getId(), $this->_storeIds)  )
                continue;
            $this->_storeCollection[] = $store;
        }
    }

    /**
     * @see Mage_Adminhtml_Model_System_Store
     */
    public function reload($type = null)
    {
        $this->_storeIds = Mage::helper('coobus_storemanager/data')->getAvailableStoreIds();
    	return parent::reload($type);
    }
}
