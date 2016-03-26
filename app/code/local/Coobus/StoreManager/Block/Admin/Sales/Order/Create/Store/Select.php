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
 * Adminhtml sales order create select store block
 *
 * @package    Coobus_StoreManager
 * @author     Ricardo Custodio <ricardo@coobus.com.br>
 */

class Coobus_StoreManager_Block_Admin_Sales_Order_Create_Store_Select extends Mage_Adminhtml_Block_Sales_Order_Create_Store_Select
{
    public function __construct()
    {
        parent::__construct();
        $this->_storeIds = Mage::helper('coobus_storemanager/data')->getAvailableStoreIds();
    }
	
    public function getStoreCount(){
    	return sizeof($this->_storeIds);
    }
}
