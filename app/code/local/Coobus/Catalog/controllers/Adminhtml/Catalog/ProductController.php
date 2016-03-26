<?php
/**
 * Magento
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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
include_once "Mage".DS."Adminhtml".DS."controllers".DS."Catalog".DS."ProductController.php";
class Coobus_Catalog_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{
    protected $massactionEventDispatchEnabled = true;
    
    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Coobus_Catalog');
    }
    
    /**
     * Product list page
     
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('catalog/products');
        
        $this->_addContent(
            $this->getLayout()->createBlock('coobus_catalog/adminhtml_catalog_product')
        );
        $this->renderLayout();
    }
*/
    /**
     * Product grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('coobus_catalog/adminhtml_catalog_product_grid')->toHtml()
        );
    }   
 
    /**
     * Export product grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'produtos.csv';
        $content    = $this->getLayout()->createBlock('coobus_catalog/adminhtml_catalog_product_grid')
            ->getExport();

        $this->_sendUploadResponse($fileName, $content);
    }

    /**
     * Export product grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'produtos.xls';
        $content    = $this->getLayout()->createBlock('coobus_catalog/adminhtml_catalog_product_grid')
            ->getExport('xml');

        $this->_sendUploadResponse($fileName, $content);
    }
    
    
    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');

        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);

        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setHeader('charset','ISO-8859-1');
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
    
    ///////////////////////////////////////////////////////////////////////////////////////////////
    // Mass Functions BEGIN -->               /////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////
    
    
    /** 
     * This will relate all products selected to each other.     
     *
     */     
    public function massRefreshProductsAction()
    {
        $productIds = $this->getRequest()->getParam('product');
        if (!is_array($productIds)) {
            $this->_getSession()->addError($this->__('Please select product(s)'));
        }
        else {
            try {
                foreach ($productIds as $productId) {
                    $product = Mage::getModel('catalog/product')->load($productId);
                    if ($this->massactionEventDispatchEnabled)
                      Mage::dispatchEvent('catalog_product_prepare_save', 
                          array('product' => $product, 'request' => $this->getRequest()));
                    $product->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully refreshed.', count($productIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

}