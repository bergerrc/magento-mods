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
 
class Coobus_StoreManager_Helper_Observer extends Mage_Core_Helper_Abstract{ 
	
	protected $_storeIds;
	protected $_storeGroupIds;
	protected $_websiteIds;
	
    public function setStoreGroupCollection(Varien_Event_Observer $observer){
    	if ( $this->_loadRegistry() ){
			$collection = $observer->getEvent()->getCollection();
			$collection->addFieldToFilter($collection->getResource()->getIdFieldName(), array('in' => $this->_storeGroupIds));
    	}
	}
	
    public function setWebsiteCollection(Varien_Event_Observer $observer){
    	if ( $this->_loadRegistry() ){    	
			$websiteCollection = $observer->getEvent()->getWebsiteCollection();
			$websiteCollection->addIdFilter($this->_websiteIds);
    	}
	}
	
	public function afterWebCollection(Varien_Event_Observer $observer){
	    if ( $this->_loadRegistry() ){    	
			$websiteCollection = $observer->getEvent()->getWebsiteCollection();
			$i = 0;
			foreach ( $websiteCollection as $website){
				if ($i > 0) break;
				if ($website->getId() == Mage_Core_Model_App::ADMIN_STORE_ID) continue;
				
				$website->setIsDefault(true);
				if ( $website->getGroupsCount()>0 ){
					$j=0;
					$groups = $website->getGroups();
					foreach ( $groups as $group){
						if ( $j>0 ) continue;
						$website->setDefaultGroupId( $group->getId() );
						$j++;
					}					
				}
				$i = $i + 1;
			}
    	}
	}
	
	public function filterByStore(Varien_Event_Observer $observer){
	    if ( $this->_loadRegistry() ){    	
			$collection = $observer->getEvent()->getCollection();
			if ( $collection ){
				if ( $collection instanceof Mage_Sales_Model_Mysql4_Collection_Abstract ){
					$collection->addAttributeToFilter('main_table.store_id', array('in' => $this->_storeIds));					
				}elseif ($collection instanceof Coobus_StoreManager_Model_Catalog_Resource_Eav_Mysql4_Product_Collection){					 
					$collection->addStoreFilter( $this->_storeIds );
				}elseif ($collection instanceof Mage_Sales_Model_Mysql4_Order_Grid_Collection){
					$collection->addFieldToFilter('main_table.store_id', array('in' => $this->_storeIds));
					Mage::log('main_table.store_id');
				}else{
					$collection->addFieldToFilter('store_id', array('in' => $this->_storeIds));
				}
				//if ( Mage::registry(Coobus_StoreManager_Helper_Data::$STORES_ALLOWED_REG_KEY) )
					//Mage::log('RegisterOK ' . Mage::registry(Coobus_StoreManager_Helper_Data::$STORES_ALLOWED_REG_KEY));
			}else{ 
				Mage::log('Not found collection on ' . $observer->getEvent()->getName());
			}
    	}
	}
	
	public function filterByStoreGroup(Varien_Event_Observer $observer){
	    if ( $this->_loadRegistry() ){    	
			$collection = $observer->getEvent()->getCollection();
			if ( $collection ){
				if ($collection instanceof Coobus_StoreManager_Model_Catalog_Resource_Eav_Mysql4_Category_Collection){
					$collection->addStoreGroupFilter( $this->_storeGroupIds );
				}else{
					$collection->addFieldToFilter('group_id', array('in' => $this->addStoreGroupFilter));
				}
			}else{ 
				Mage::log('Not found collection on ' . $observer->getEvent()->getName());
			}
    	}
	}
	
	private function _loadRegistry(){
		$cookie = Mage::getSingleton('core/cookie');				
		$userId = $cookie->get(Coobus_StoreManager_Controller_Action::$MANAGER_ID);
		$userAclRole = $cookie->get(Coobus_StoreManager_Controller_Action::$MANAGER_ACL_ROLE);

		$this->_storeIds 		= Mage::registry(Coobus_StoreManager_Controller_Action::$STORES_ALLOWED_REG_KEY)? 		explode('_',Mage::registry(Coobus_StoreManager_Controller_Action::$STORES_ALLOWED_REG_KEY)): $this->_storeIds;
		$this->_storeGroupIds 	= Mage::registry(Coobus_StoreManager_Controller_Action::$STORE_GROUPS_ALLOWED_REG_KEY)? explode('_',Mage::registry(Coobus_StoreManager_Controller_Action::$STORE_GROUPS_ALLOWED_REG_KEY)): $this->_storeGroupIds;
		$this->_websiteIds 		= Mage::registry(Coobus_StoreManager_Controller_Action::$WEBSITES_ALLOWED_REG_KEY)? 	explode('_',Mage::registry(Coobus_StoreManager_Controller_Action::$WEBSITES_ALLOWED_REG_KEY)): $this->_websiteIds;
		
		
		$res =  !is_null( $this->_websiteIds ) && $this->_websiteIds && 
				!is_null( $this->_storeGroupIds ) && $this->_storeGroupIds &&
				!is_null( $this->_storeIds ) && $this->_storeIds;
				
		if ( !$res && $userId && $userAclRole ){
			try{
				$manager = Mage::getModel('coobus_storemanager/manager')->load($userId, $userAclRole);
				if ( $manager ){
					$this->_storeIds 		= $manager->getStoreIds();
					$this->_storeGroupIds 	= $manager->getStoreGroupIds();
					$this->_websiteIds 		= $manager->getWebsiteIds();

					$res =  !is_null( $this->_websiteIds ) && $this->_websiteIds && 
							!is_null( $this->_storeGroupIds ) && $this->_storeGroupIds &&
							!is_null( $this->_storeIds ) && $this->_storeIds;
				}
			}catch( Exception $e){
				return true;
			}
		}
				
		return $res;
	}
	

	
	public function redirectSystemConfigMenu(Varien_Event_Observer $observer){
		$action = $observer->getEvent()->getControllerAction();
		$request = $action->getRequest();
		
		//$section = $request->getParam('section');
		$website_code = $request->getParam('website');
        $store_code   = $request->getParam('store');
		
        if ( empty($website_code) && empty($store_code) ){
        	if ( !Mage::helper("coobus_storemanager/data")->isUserMasterAdmin() ){
        		$store = Mage::app()->getDefaultStoreView();
        		$store_code = $store->getCode();
        		$website_code = Mage::app()->getWebsite( $store->getWebsiteId() )->getCode();
        		
				$session = Mage::getSingleton('adminhtml/session');
				$session->setIsUrlNotice($action->getFlag('', Mage_Adminhtml_Controller_Action::FLAG_IS_URLS_CHECKED));
		        //$request->setRedirect($action->getUrl('*/*/', array('store'=>$store_code,'section'=>$section)));
		        
				$request->initForward();
				$request->setParams( array('website'=>$website_code, 'store'=>$store_code) );
				$request->setActionName($request->getRequestedActionName())
            		->setDispatched(false);
        	}
        } 
	}
	/**
	 * Adiciona uma visão automaticamente assim que um novo grupo é adicionado
	 * Serve para não manter grupos orfãos na base ocasionando problema na interface de criação de visão
	 */
	public function storeGroupSave(Varien_Event_Observer $observer){
		$group = $observer->getEvent()->getGroup();
		
		//Verifica se é um novo grupo
		if ( $group->getDefaultStoreId() == 0 ){
			$storeModel = Mage::getModel('core/store');
			$storeModel->setGroupId( $group->getId() );
			$storeModel->setWebsiteId( $group->getWebsiteId() );
			$storeModel->setName( $group->getName() );
			$storeModel->setIsActive( false );			
			$code = str_replace(' ', '',utf8_encode(trim(strtolower($group->getName()))));
			$storeModel->setCode( $code );
			$storeModel->save();

			Mage::app()->reinitStores();
			Mage::dispatchEvent('store_add', array('store'=>$storeModel));
		}
	}

    	public function joinShippingAmount(Varien_Event_Observer $observer){
		$collection = $observer->getEvent()->getCollection();
		if ( $collection ){
			$select = $collection->getSelect();
			$select->joinLeft(array('sales_order'=>$collection->getTable('sales/order')), 'sales_order.entity_id=main_table.entity_id',array('shipping_amount'=>'shipping_amount'));
		}
	}

    	public function joinInvoiceDate(Varien_Event_Observer $observer){
		$collection = $observer->getEvent()->getCollection();
		if ( $collection ){
			$select = $collection->getSelect();
			$select->joinLeft(array('invoice'=>$collection->getTable('sales/invoice')), 'invoice.order_id=main_table.entity_id',array('payment_date'=>'created_at'));
		}
	}

    	public function joinSharingInfo(Varien_Event_Observer $observer){

		$collection = $observer->getEvent()->getCollection();
		if ( $collection ){
			$select = $collection->getSelect();

			$tax = 20;//Mage::getStoreConfig('store_payment/revenueshare/tax');
//			Mage::log('select:' . $select);

			if ( $tax == null || $tax =='' || $tax <= 0 ){
				$select->columns(array(
				    'revenue_share_amount' => new Zend_Db_Expr('0'),
				    'total_to_receive' => new Zend_Db_Expr('main_table.base_grand_total')
				));
			}else{
			$select->columns(array(
			    'revenue_share_amount' => new Zend_Db_Expr('round((main_table.base_grand_total - sales_order.shipping_amount) * ' . $tax . '/100,2)'),
			    'total_to_receive' => new Zend_Db_Expr('(main_table.base_grand_total - round((main_table.base_grand_total - sales_order.shipping_amount) * ' . $tax . '/100,2))')
			));
			}
		}

	}
}