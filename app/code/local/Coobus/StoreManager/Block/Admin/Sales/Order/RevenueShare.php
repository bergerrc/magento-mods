<?php

/ * Adminhtml revenue share sales orders grid
 *
 */
class Coobus_StoreManager_Block_Admin_Sales_Order_RevenueShare extends Mage_Core_Block_Template
{

    /**
     * Collection object
     *
     * @var Varien_Data_Collection
     */
    protected $_collection = null;

    /**
     * Count totals
     *
     * @var boolean
     */
    protected $_countTotals = false;

    /**
     * Totals
     *
     * @var Varien_Object
     */
    protected $_varTotals;

    public function __construct()
    {
        parent::__construct();
	 $this->setTemplate('coobus_storemanager/revenueshare.phtml');
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
	 $select = $collection->getSelect();
	 $select->where("status = 'sharing'");
	 $select->orderby('created_at');
        $this->setCollection($collection);
        return $this;
    }

    public function setCollection($collection)
    {
        $this->_collection = $collection;
	 return $this;
    }

    /**
     * get collection object
     *
     * @return Varien_Data_Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    protected function _prepareLayout()
    {
        $this->setChild('export_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Export'),
                    'onclick'   => $this->getJsObjectName().'.doExport()',
                    'class'   => 'task'
                ))
        );
        $this->setChild('reset_filter_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Reset Filter'),
                    'onclick'   => $this->getJsObjectName().'.resetFilter()',
                ))
        );
        $this->setChild('search_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Search'),
                    'onclick'   => $this->getJsObjectName().'.doFilter()',
                    'class'   => 'task'
                ))
        );
        return parent::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        $this->_prepareCollection();
        return parent::_beforeToHtml();
    }

    /**
     * Iterate collection and call callback method per item
     * For callback method first argument always is item object
     *
     * @param string $callback
     * @param array $args additional arguments for callback method
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    public function _exportIterateCollection($callback, array $args)
    {
        $originalCollection = $this->getCollection();
        $count = null;
        $page  = 1;
        $lPage = null;
        $break = false;

        while ($break !== true) {
            $collection = clone $originalCollection;
            $collection->setPageSize($this->_exportPageSize);
            $collection->setCurPage($page);
            $collection->load();
            if (is_null($count)) {
                $count = $collection->getSize();
                $lPage = $collection->getLastPageNumber();
            }
            if ($lPage == $page) {
                $break = true;
            }
            $page ++;

            foreach ($collection as $item) {
                call_user_func_array(array($this, $callback), array_merge(array($item), $args));
            }
        }
    }

    /**
     * Write item data to Excel 2003 XML export file
     *
     * @param Varien_Object $item
     * @param Varien_Io_File $adapter
     * @param Varien_Convert_Parser_Xml_Excel $parser
     */
    protected function _exportExcelItem(Varien_Object $item, Varien_Io_File $adapter, $parser = null)
    {
        if (is_null($parser)) {
            $parser = new Varien_Convert_Parser_Xml_Excel();
        }

        $row = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $row[] = $column->getRowFieldExport($item);
            }
        }
        $data = $parser->getRowXml($row);
        $adapter->streamWrite($data);
    }

    /**
     * Retrieve Totals row array for Export
     *
     * @return array
     */
    protected function _getExportTotals()
    {
        $totals = $this->getTotals();
        $row    = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $row[] = ($column->hasTotalsLabel()) ? $column->getTotalsLabel() : $column->getRowFieldExport($totals);
            }
        }
        return $row;
    }

    /**
     * Set count totals
     *
     * @param boolean $visible
     */
    public function setCountTotals($count=true)
    {
        $this->_countTotals = $count;
    }

    /**
     * Return count totals
     *
     * @return boolean
     */
    public function getCountTotals()
    {
        return $this->_countTotals;
    }

    /**
     * Set totals
     *
     * @param boolean $visible
     */
    public function setTotals(Varien_Object $totals)
    {
        $this->_varTotals = $totals;
    }

    /**
     * Retrieve totals
     *
     * @return Varien_Object
     */
    public function getTotals()
    {
        return $this->_varTotals;
    }

    /**
     * Retrieve a file container array by grid data as MS Excel 2003 XML Document
     *
     * Return array with keys type and value
     *
     * @return string
     */
    public function getExcelFile($sheetName = '')
    {
        $this->_isExport = true;
        $this->_prepareCollection();

        $parser = new Varien_Convert_Parser_Xml_Excel();
        $io     = new Varien_Io_File();

        $path = Mage::getBaseDir('var') . DS . 'export' . DS;
        $name = md5(microtime());
        $file = $path . DS . $name . '.xml';

        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);
        $io->streamWrite($parser->getHeaderXml($sheetName));
        $io->streamWrite($parser->getRowXml($this->_getExportHeaders()));

        $this->_exportIterateCollection('_exportExcelItem', array($io, $parser));

        if ($this->getCountTotals()) {
            $io->streamWrite($parser->getRowXml($this->_getExportTotals()));
        }

        $io->streamWrite($parser->getFooterXml());
        $io->streamUnlock();
        $io->streamClose();

        return array(
            'type'  => 'filename',
            'value' => $file,
            'rm'    => true // can delete file after use
        );
    }
}
