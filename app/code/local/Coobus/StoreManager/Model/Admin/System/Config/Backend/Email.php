<?php
class Coobus_StoreManager_Model_Admin_System_Config_Backend_Email extends Mage_Adminhtml_Model_System_Config_Backend_Email_Address
{
	protected $_dataSaveAllowed = false;
	const TRANSEMAIL_IDENTSALES_EMAIL= 'trans_email/ident_sales/email';
	const SALESEMAIL_ORDER_COPYTO = 'sales_email/order/copy_to';
	const SALESEMAIL_ORDERCOMMENT_COPYTO = 'sales_email/order_comment/copy_to';
	const SALESEMAIL_INVOICE_COPYTO = 'sales_email/invoice/copy_to';
	const SALESEMAIL_INVOICECOMMENT_COPYTO = 'sales_email/invoice_comment/copy_to';
	const SALESEMAIL_SHIPMENT_COPYTO = 'sales_email/shipment/copy_to';
	const SALESEMAIL_SHIPMENTCOMMENT_COPYTO = 'sales_email/shipment_comment/copy_to';
	const SALESEMAIL_CREDITMEMO_COPYTO = 'sales_email/creditmemo/copy_to';
	const SALESEMAIL_CREDITMEMOCOMMENT_COPYTO = 'sales_email/creditmemo_comment/copy_to';
	
	
    protected function _afterLoad()
    {
    	$this->setValue( Mage::getStoreConfig(self::TRANSEMAIL_IDENTSALES_EMAIL, $this->getStore()) );
        parent::_afterLoad();
    }
	
    public function afterCommitCallback()
    {
        $value = $this->getValue();
        if ( $this->isValueChanged() ){           
        	Mage::getConfig()->saveConfig( self::TRANSEMAIL_IDENTSALES_EMAIL, $value, $this->getScope(), $this->getScopeId());
        	Mage::getConfig()->saveConfig( self::SALESEMAIL_ORDER_COPYTO, $value, $this->getScope(), $this->getScopeId());
        	Mage::getConfig()->saveConfig( self::SALESEMAIL_ORDERCOMMENT_COPYTO, $value, $this->getScope(), $this->getScopeId());
        	Mage::getConfig()->saveConfig( self::SALESEMAIL_INVOICE_COPYTO, $value, $this->getScope(), $this->getScopeId());
        	Mage::getConfig()->saveConfig( self::SALESEMAIL_INVOICECOMMENT_COPYTO, $value, $this->getScope(), $this->getScopeId());
        	Mage::getConfig()->saveConfig( self::SALESEMAIL_SHIPMENT_COPYTO, $value, $this->getScope(), $this->getScopeId());
        	Mage::getConfig()->saveConfig( self::SALESEMAIL_SHIPMENTCOMMENT_COPYTO, $value, $this->getScope(), $this->getScopeId());
        	Mage::getConfig()->saveConfig( self::SALESEMAIL_CREDITMEMO_COPYTO, $value, $this->getScope(), $this->getScopeId());
        	Mage::getConfig()->saveConfig( self::SALESEMAIL_CREDITMEMOCOMMENT_COPYTO, $value, $this->getScope(), $this->getScopeId());
        }
        return parent::afterCommitCallback();
    }

}