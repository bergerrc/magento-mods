<?php

class Coobus_Mail_Model_Email_Template extends Mage_Core_Model_Email_Template
{
    /**
     * Retrieve mail object instance
     *
     * @return Zend_Mail
     
    public function getMail()
    {
       if (is_null($this->_mail)) {

           $smtp_host = Mage::getStoreConfig('system/smtp/host');  
           $smtp_port = Mage::getStoreConfig('system/smtp/port');
           $smtp_ssl = Mage::getStoreConfig('system/smtp/ssl');
           $smtp_auth = Mage::getStoreConfig('system/smtp/auth');
           $smtp_username = Mage::getStoreConfig('system/smtp/username');
           $smtp_password = Mage::getStoreConfig('system/smtp/password');  
       
            $config = array('port' => $smtp_port);
           
            if ( $smtp_ssl == '1' ) $config['ssl'] = 'ssl';
            if ( $smtp_auth && $smtp_auth !== 'NONE'){
           		$config['auth'] = $smtp_auth;
           		$config['username'] = $smtp_username;
           		$config['password'] = $smtp_password;
            } 	    
                
            $transport = new Zend_Mail_Transport_Smtp($smtp_host, $config);
            
            Zend_Mail::setDefaultTransport($transport);
              
            $this->_mail = new Zend_Mail('utf-8');
        }
        return $this->_mail;
    }
    */
}
