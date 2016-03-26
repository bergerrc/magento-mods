<?php
require_once 'Mage/Adminhtml/controllers/Sales/OrderController.php';

class Coobus_StoreManager_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController 
{
    /**
     *  Export order grid to Excel XML format
     */
    public function exportSharingInfoAction()
    {
        $fileName   = 'orders.xml';
        $grid       = $this->getLayout()->createBlock('adminhtml/sales_order_grid');


 	 $grid->addColumnAfter('shipping_amount', 
				  array( 'header'  => Mage::helper('sales')->__('Shipping Amount'),
					'width'     => '100',
					'index'     => 'shipping_amount'),
				 'base_grand_total');
 	 $grid->addColumnAfter('payment_date', 
				  array( 'header'  => Mage::helper('sales')->__('Payment Date'),
					'width'     => '100',
					'index'     => 'payment_date',
					'type'	     => 'datetime'),
				 'created_at');
 	 $grid->addColumnAfter('revenue_share_amount', 
				  array( 'header'  => Mage::helper('sales')->__('Revenue Share'),
					'width'     => '100',
					'index'     => 'revenue_share_amount'),
				 'shipping_amount');
 	 $grid->addColumnAfter('total_to_receive', 
				  array( 'header'  => Mage::helper('sales')->__('Total to Receive'),
					'width'     => '100',
					'index'     => 'total_to_receive'),
				 'revenue_share_amount');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function revenueAction()
    {
        //$fileName   = 'orders.xml';
        //$grid       = $this->getLayout()->createBlock('coobus_storemanager/admin_sales_order_revenueshare');
        //$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	 //$this->getResponse()->setBody('test');
	//Mage::log('fullActionName:' . $this->getFullActionName());

	$this->loadLayout();

        $this->renderLayout();
	Mage::log('Updates:' . $this->getLayout()->getXmlString());
	//echo $this->getLayout()->createBlock('core/profiler')->toHtml();
	//$this->getResponse()->setBody('test');
    }

    public function testAction()
    {
        echo $this->getLayout()->createBlock('core/profiler')->toHtml();
    }
}