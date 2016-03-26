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
 * Adminhtml sales orders grid
 *
 * @package    Coobus_StoreManager
 * @author     Ricardo Custodio <ricardo@coobus.com.br>
 */
class Coobus_StoreManager_Block_Admin_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
/*
    protected function _prepareColumns()
    {
	
    	parent::_prepareColumns();
    	$storeIds = Mage::helper('coobus_storemanager/data')->getAvailableStoreIds();
    	if ( sizeof($storeIds)==1 )
    		unset($this->_columns['store_id']);
	unset($this->_columns['shipping_name']);
    }
   
    public function setCollection($collection)
    {
  		$storeIds = Mage::helper('coobus_storemanager/data')->getAvailableStoreIds();
       	$collection->addFieldToFilter('store_id',$storeIds);

        parent::setCollection($collection);
    }


*/ 

    protected function _prepareColumns()
    {
	parent::_prepareColumns();
	unset($this->_columns['grand_total']);
	unset($this->_columns['shipping_name']);

	$storeIdCol = $this->getColumn('store_id');
	if ($storeIdCol) $storeIdCol['width'] = '100px';

	$this->addExportType('*/*/exportSharingInfo', Mage::helper('sales')->__('Excel with Sharing Info'));
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
            } else {
                $cond = $column->getFilter()->getCondition();
                if ($field && isset($cond)) {
                    $this->getCollection()->addFieldToFilter('main_table.' . $field , $cond);
                }
            }
        }
        return $this;
    }

    protected function _prepareMassaction()
    {
	$statuses = array();
	$states = Mage::getSingleton('sales/order_config')->getStates();

	foreach( array_keys($states) as $state){
		$status_of_state = Mage::getSingleton('sales/order_config')->getStateStatuses( $state, true );
		if ( count($status_of_state) > 1 || $state == Mage_Sales_Model_Order::STATE_CLOSED){
			foreach (array_keys($status_of_state) as $status){
				$statuses[$status] = $status_of_state[$status] . ' - ' . $status;
			}
		}
	}
	array_unshift($statuses, array('value'=>'', 'label'=>''));
	$this->getMassactionBlock()->addItem('change_status', array(
		'label'=> Mage::helper('sales')->__('Change Status'),
		'url' => $this->getUrl('*/*/massStatus'),
		'additional' => array(
					'visibility' => array(
								'name' => 'status',
								'type' => 'select',
								'class' => 'required-entry',
								'label' => Mage::helper('sales')->__('New Status'),
								'values' => $statuses))
		));
	parent::_prepareMassaction();
    }

}
