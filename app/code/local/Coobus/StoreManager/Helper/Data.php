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
 
class Coobus_StoreManager_Helper_Data extends Mage_Core_Helper_Abstract{ 
	
	private $_storeVarName = 'store';
	
	public function getManager(){
		try{
			$session = new Mage_Admin_Model_Session();
			$user = $session->getUser();
			if ( $user )
				return Mage::getModel('coobus_storemanager/manager')->load($user->getId(), $user->getAclRole());
			else
				return false;
		}catch (Exception $e){
			return false;
		}
	}

	public function isUserMasterAdmin(){
		$session = Mage::getSingleton('admin/session');
		if ( !$session ) return false;
		$user = Mage::getSingleton('admin/session')->getUser();
		if ($user){
	        $acl = Mage::getResourceModel('admin/acl')->loadAcl();
			return $acl->isAllowed($user->getAclRole(),"all");
		}
		return false;
	}

}