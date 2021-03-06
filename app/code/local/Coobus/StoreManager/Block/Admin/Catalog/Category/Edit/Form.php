<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Categories tree block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Coobus_StoreManager_Block_Admin_Catalog_Category_Edit_Form extends Mage_Adminhtml_Block_Catalog_Category_Edit_Form
{
	
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('storemanager/catalog/category/edit/form.phtml');
    }

    protected function isEnabled(){
    	if ( !$this->getCategory() || !$this->getCategory()->getId() ){ 

			$parentId = (int) $this->getRequest()->getParam('parent');
			if ( ($parentId && ($parentId != Mage_Catalog_Model_Category::TREE_ROOT_ID)) ||
				Mage::helper('coobus_storemanager/data')->isUserMasterAdmin()) {
				return true;
			}
			return false;
    	}
		return true;
    }

}
