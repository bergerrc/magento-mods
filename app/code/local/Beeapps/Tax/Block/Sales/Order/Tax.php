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
 * Tax totals modification block. Can be used just as subblock of Mage_Sales_Block_Order_Totals
 * @author Beeapps Developer <developer@beeapps.com.br>
 */
class Beeapps_Tax_Block_Sales_Order_Tax extends Mage_Tax_Block_Sales_Order_Tax
{
    
    /**
     * Initialize all order totals relates with tax
     *
     * @return Mage_Tax_Block_Sales_Order_Tax
     */
    public function initTotals()
    {
        /** @var $parent Mage_Adminhtml_Block_Sales_Order_Invoice_Totals */
        $parent = $this->getParentBlock();
        $this->_order   = $parent->getOrder();
        $this->_source  = $parent->getSource();

        $store = $this->getStore();
        $allowTax = ($this->_source->getTaxAmount() > 0) || ($this->_config->displaySalesZeroTax($store));
        $grandTotal = (float) $this->_source->getGrandTotal();
        if (!$grandTotal || ($allowTax && !$this->_config->displaySalesTaxWithGrandTotal($store))) {
            parent::_addTax();
        }

        parent::_initSubtotal();
        parent::_initShipping();
        parent::_initDiscount();
        parent::_initGrandTotal();
        return $this;
    }
}
