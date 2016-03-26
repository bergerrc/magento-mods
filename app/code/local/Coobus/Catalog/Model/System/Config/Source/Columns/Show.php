<?php
/**
 * WDCA
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
 * @category   WDCA
 * @package    Coobus_Catalog
 * @copyright  Copyright (c) 2008-2010 WDCA (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Grid checkbox column renderer
 *
 * @category   WDCA
 * @package    Coobus_Catalog
 * @author      WDCA <contact@wdca.ca>
 */
class Coobus_Catalog_Model_System_Config_Source_Columns_Show
{
    public function toOptionArray()
    {
    
        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter( Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId() )
            ->addFilter("is_visible", 1);
        $cols = array();
        $cols[] = array('label' => Mage::helper("coobus_catalog")->__('ID'), 'value' => 'id');
        $cols[] = array('label' => Mage::helper("coobus_catalog")->__('Type (simple, bundle, etc)'), 'value' => 'type_id');
        $cols[] = array('label' => Mage::helper("coobus_catalog")->__('Attribute Set'), 'value' => 'attribute_set_id');
        $cols[] = array('label' => Mage::helper("coobus_catalog")->__('Quantity'), 'value' => 'qty');
        $cols[] = array('label' => Mage::helper("coobus_catalog")->__('Websites'), 'value' => 'websites');
        $cols[] = array('label' => Mage::helper("coobus_catalog")->__('Categories'), 'value' => 'categories');
        //@nelkaake Tuesday April 27, 2010 :
        $cols[] = array('label' => Mage::helper("coobus_catalog")->__('Date Created'), 'value' => 'created_at');
        foreach($collection->getItems() as $col) {
            $cols[] = array('label' => $col->getFrontendLabel(), 'value' => $col->getAttributeCode());
        }
        sort( $cols );
        return $cols;
    }
}