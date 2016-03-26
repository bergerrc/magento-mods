<?php
/**
 */

/**
 */
class Coobus_StoreManager_Block_Admin_Sales_Shipment_Grid extends Mage_Adminhtml_Block_Sales_Shipment_Grid
{

    protected function _prepareColumns()
    {
  	 unset($this->_columns['total_qty']);
        $this->addColumn('shipping_amount', array(
            'header' => Mage::helper('sales')->__('Shipping Amount'),
            'index' => 'shipping_amount',
            'type'  => 'number',
        ));

        return parent::_prepareColumns();
    }

}
