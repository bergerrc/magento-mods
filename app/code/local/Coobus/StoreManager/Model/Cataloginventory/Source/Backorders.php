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

class Coobus_StoreManager_Model_Cataloginventory_Source_Backorders extends Mage_CatalogInventory_Model_Source_Backorders
{
	
    public function toOptionArray()
    {
    	$result = parent::toOptionArray();
    	unset($result[1]); //Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY
    	return $result;
    }
   
}
