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
class Coobus_StoreManager_Model_Source_Free_Title
{
	
    public function toOptionArray()
    {
        return array(
            array('value' => Mage::helper('coobus_storemanager')->__('Free Shipping'), 'label'=>Mage::helper('coobus_storemanager')->__('Free Shipping')),
            array('value' => Mage::helper('coobus_storemanager')->__('Take on Store'), 'label'=>Mage::helper('coobus_storemanager')->__('Take on Store')),
        );
    }
   
}
