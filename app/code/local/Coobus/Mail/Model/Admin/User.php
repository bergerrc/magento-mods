<?php
/**
 */

/**
 * Admin user model
 *
 */
class Coobus_Mail_Model_Admin_User extends Mage_Admin_Model_User
{
	
    /**
     * Send email with new user password
     *
     * @return Mage_Admin_Model_User
	*/
    public function sendNewPasswordEmail()
    {
		$manager = Mage::getModel('coobus_storemanager/manager')->load($this->getId(), $this->getAclRole());
		if ( $manager ){
			$storeIds = $manager->getStoreIds();
			$this->setStoreId( $storeIds[1] );
		}
		
		return parent::sendNewPasswordEmail();
    }
}
