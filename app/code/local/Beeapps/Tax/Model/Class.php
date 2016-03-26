<?php
/**
 * Beeapps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 *
 * @category    Beeapps
 * @package     Beeapps_Tax
 * @copyright   Beeapps Inc. (http://www.beeapps.com.br)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax class model
 *
 * @category   Mage
 * @package    Mage_Tax
 * @author Beeapps Developer <developer@beeapps.com.br>
 */

class Beeapps_Tax_Model_Class extends Mage_Tax_Model_Class
{
    const TAX_CLASS_TYPE_PAYMENTMETHOD   = 'PAYMENT_METHOD';

    public function _construct()
    {
        parent::_init('tax/class');
    }
}
