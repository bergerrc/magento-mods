<?php

/**
 * Adminhtml base helper
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Coobus_StoreManager_Helper_Admin_Data extends Mage_Adminhtml_Helper_Data
{

    public function setPageHelpUrl($url=null)
    {
        if (is_null($url)) {
            $request = Mage::app()->getRequest();
            $frontModule = $request->getControllerModule();
            if (!$frontModule) {
                $frontName = $request->getModuleName();
                $router = Mage::app()->getFrontController()->getRouterByFrontName($frontName);

                $frontModule = $router->getModuleByFrontName($frontName);
                if (is_array($frontModule)) {
                    $frontModule = $frontModule[0];
                }
            }
            $url = 'http://wiki.coobus.com.br?search=';
            //$url.= Mage::app()->getLocale()->getLocaleCode().'/';
            //$url.= $frontModule.'/';
            $url.= $request->getControllerName().' ';
            $url.= $request->getActionName();

            $this->_pageHelpUrl = $url;
        }
		
        return parent::setPageHelpUrl($url);
    }


/**
     * Return month's totals
     *
     * @return mixed
     */
    public function getRevenue()
    {
        $item = $this->_prepareCollection();
/**        $total = $item->getTotalIncomeAmount() - $item->getTotalRefundedAmount();*/
        $total = $item->getTotalRevenueAmount();
        return (string)Mage::helper('core')->currency($total, true, false);
    }

    public function getProfit()
    {
        $item = $this->_prepareCollection();
        $total = $item->getTotalProfitAmount(); 
        return (string)Mage::helper('core')->currency($total, true, false);
    }

    public function getShippingAmount()
    {
        $item = $this->_prepareCollection();
        $total = $item->getTotalShippingAmount();
        return (string)Mage::helper('core')->currency($total, true, false);
    }
 
    /**
     * Get report month's amount totals
     * @return mixed
     */
    protected function _prepareCollection()
    {
        $aggregatedColumns = array('total_income_amount'=>'sum(total_income_amount)',
            'total_refunded_amount'=>'sum(total_refunded_amount)');
 
        $totalsCollection = Mage::getResourceModel('sales/report_order_collection')
            ->setPeriod('year')
            ->setDateRange($this->getStartMonth(), $this->getCurrentDate())
/*            ->addStoreFilter( array(8,7,20) )*/
/*            ->setAggregatedColumns($aggregatedColumns)*/
            ->addOrderStatusFilter( array('complete','closed','processing','sharing') );
/*            ->isTotals(true);*/
 

        foreach ($totalsCollection as $item) {
/*Mage::log( 'query:' . $totalsCollection->getSelect() );*/
            return $item;
            break;
        }
    }
 
    /**
     * Return current date
     *
     * @return string
     */
     
/**
     * Return current day
     *
     * @return string
     */
public function getCurrentDate()
    {
        $date = date('Y-m-d');
        return (string)$date;
    }
 
    /**
     * Return first day for current date
     *
     * @return string
     */
    public function getStartMonth()
    {
        $time = strtotime($this->getCurrentDate());
        $startCurrentMonth = date("Y-m-d", strtotime("-1 month", $time));
/*
Mage::log((string)$startCurrentMonth);
Mage::log((string)date("Y-m-d", $time));
*/
        return (string)$startCurrentMonth;
    }

}
