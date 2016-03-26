<?php

class Coobus_Catalog_Block_Adminhtml_Catalog_Product_Edit_Tabs extends Coobus_StoreManager_Block_Admin_Catalog_Product_Edit_Tabs
{
   
    public function disableGroups($groupnames){
        $product = $this->getProduct();

        if (!($setId = $product->getAttributeSetId())) {
            $setId = $this->getRequest()->getParam('set', null);
        }

        if ($setId) {
            $groupCollection = Mage::getResourceModel('eav/entity_attribute_group_collection')
                ->setAttributeSetFilter($setId)
                ->load();

            foreach ($groupCollection as $group) {
            	if ( $groupnames && is_array($groupnames)){
            		if ( in_array($group->getAttributeGroupName(), $groupnames) )
            			unset($this->_tabs["group_".$group->getId()]);
            	}
            }
        }
    }
}