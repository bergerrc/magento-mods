<?php

class Coobus_StoreManager_Controller_Action
{
	public static $MANAGER_ID = 'manager_id';
	public static $MANAGER_ACL_ROLE = 'manager_acl_role';
	public static $STORES_ALLOWED_REG_KEY = 'stores_allowed';
	public static $STORE_GROUPS_ALLOWED_REG_KEY = 'store_groups_allowed';
	public static $WEBSITES_ALLOWED_REG_KEY = 'websites_allowed';
	
	public function preDispatchStartFromAdmin()
	{
		//Mage::log('preDispatchStartFromAdmin');
		$this->loadStores();	
	}
	
	public function preDispatch(Varien_Event_Observer $observer){
		$area = $observer->getEvent()->getControllerAction()->getLayout()->getArea();
		if ( $area == Mage_Core_Model_App_Area::AREA_FRONTEND) {
			$cookie = Mage::getSingleton('core/cookie');		
			if ( $cookie->get(self::$MANAGER_ID) ){
				//Mage::log('Cleaning cookies from Admin');
				$cookie->delete(self::$MANAGER_ID)
					   ->delete(self::$MANAGER_ACL_ROLE);
				Mage::app()->reinitStores();
			}
		}
	}
	
	public function loadStores(){
		if ( Mage::registry(self::$STORES_ALLOWED_REG_KEY) )
			return $this;		
		
		$helper = Mage::helper('coobus_storemanager');
		$manager = $helper->getManager();
		if ( $manager ){
			Mage::register(self::$STORES_ALLOWED_REG_KEY, 		implode('_',$manager->getStoreIds()).'_0', true);
			Mage::register(self::$STORE_GROUPS_ALLOWED_REG_KEY, implode('_',$manager->getStoreGroupIds()).'_0', true);
			Mage::register(self::$WEBSITES_ALLOWED_REG_KEY, 	implode('_',$manager->getWebsiteIds()).'_0', true);
			
			Mage::getSingleton('core/cookie')
				->set(self::$MANAGER_ID, 	  Mage::getSingleton('admin/session')->getUser()->getId())
				->set(self::$MANAGER_ACL_ROLE, Mage::getSingleton('admin/session')->getUser()->getAclRole());
		}else{
			Mage::getSingleton('core/cookie')
				->delete(self::$MANAGER_ID)
				->delete(self::$MANAGER_ACL_ROLE);			
		}
			
		return $this;
	}
	
	public function cleanSession(){
		try{
			Mage::getSingleton('adminhtml/session')->clear();
			Mage::getSingleton('admin/session')->clear();
		}catch( Exception $e){
		}
		Mage::getSingleton('core/cookie')
			->delete(self::$MANAGER_ID)
			->delete(self::$MANAGER_ACL_ROLE);
	}
	
	public function logout(){
		$this->cleanSession();
	}
	

	public function massStatusUpdate(Varien_Event_Observer $observer)
      {
	$request = $observer->getEvent()->getControllerAction()->getRequest();
	$session = Mage::getSingleton('adminhtml/session');

	$pedidosIds = $request->getPost('order_ids', array());
	$i=0;
	$j=0;
	$status = $request->getPost('status');
	$statuses = array();
       $collection = Mage::getResourceModel('sales/order_status_collection')->joinStates();
       foreach ($collection as $sts) {
	       $statuses[$sts->getStatus()] = $sts['state'];
       }

	foreach ( $pedidosIds as $pedidoId ) {
		$order = Mage::getModel('sales/order')->load( $pedidoId );
		if ( $status==Mage_Sales_Model_Order::STATE_CLOSED ){
			$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CLOSED, 'Encerrado manualmente', $notified = false);
			$order->save();
			$j++;
		}else if ( isset($statuses[$status]) && $statuses[$status]==$order->getState() ){
			$order->addStatusHistoryComment('Atualizado manualmente',$status)
						     ->setIsCustomerNotified(false)
					            ->setIsVisibleOnFront(false);
			$order->save();
			$j++;
		}
		$i++;
	}
	if ( $i>0 && $j==$i) {
		$session->addSuccess( Mage::helper('adminhtml')->__( '%s pedidos(s) com status atualizado(s)', $i ) );
	} else if ( $i==0 ){
		$session->addError( Mage::helper('adminhtml')->__( 'Nenhum status de pedido foi atualizado' ) );
	} else {
		$session->addSuccess( Mage::helper('adminhtml')->__( '%s pedidos(s) com status atualizado(s)', $j ) );
		$session->addError( Mage::helper('adminhtml')->__( '%s pedidos n&atilde;o foram atualizados', $i-$j ) );
	}
	$this->_redirect($observer,'*/*/');
    }

    /**
     * Set redirect into responce
     *
     * @param   string $path
     * @param   array $arguments
     */
    protected function _redirect(Varien_Event_Observer $observer, $path, $arguments=array())
    {
	$controller = $observer->getEvent()->getControllerAction();
	$response = $controller->getResponse();
	$session = Mage::getSingleton('adminhtml/session');

        $session->setIsUrlNotice($controller->getFlag('', $controller::FLAG_IS_URLS_CHECKED));
        $response->setRedirect(Mage::helper('adminhtml')->getUrl($path, $arguments));
        return $controller;
    }

}