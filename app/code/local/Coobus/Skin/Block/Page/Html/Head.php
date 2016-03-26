<?php

/**
 * Html page block
 *
 */
class Coobus_Skin_Block_Page_Html_Head extends Mage_Page_Block_Html_Head
{

    protected function _construct()
    {
        $this->setTemplate('coobus/page/html/head.phtml');
    }
	
    /**
     * Retrieve content for keyvords tag
     *
     * @return string
     */
    public function getGoogleSiteVerification()
    {
        if (empty($this->_data['google_site_verification'])) {
            $this->_data['google_site_verification'] = Mage::getStoreConfig('design/head/google_site_verification');
        }
        return $this->_data['google_site_verification'];
    }

}
