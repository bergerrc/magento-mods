<?php

class Coobus_Catalog_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
	protected $isenhanced = true;
	private $columnSettings = array();
	private $columnOptions = array();
	private $isenabled = true;

	public function __construct()
	{
		parent::__construct();
		$this->isenabled = Mage::getStoreConfig('catalog/management/isenabled');
		$this->prepareDefaults();
		$this->prepareColumnSettings();
	}

	protected function prepareDefaults() {
		$this->setDefaultLimit(Mage::getStoreConfig('catalog/management/limit'));
		$this->setDefaultPage(Mage::getStoreConfig('catalog/management/page'));
		$this->setDefaultSort(Mage::getStoreConfig('catalog/management/sort'));
		$this->setDefaultDir(Mage::getStoreConfig('catalog/management/dir'));
	}

	protected function prepareColumnSettings() {
		$storeSettings = Mage::getStoreConfig('catalog/management/showcolumns');

		$tempArr = explode(',', $storeSettings);

		foreach($tempArr as $showCol) {
			$this->columnSettings[trim($showCol)] = true;
		}
	}

	public function colIsVisible($code) {
		return isset($this->columnSettings[$code]);
	}

	protected function _isSpecialCol($col) {
		return ($col == 'qty' || $col == 'websites' || $col=='id' || $col == 'categories');
	}

	public function getQueryStr() {
		return urldecode($this->getParam('q'));
	}

	protected function _prepareCollection()
	{
		/*
		 
		 //@nelkaake -m 13/11/10: Just made it a little nicer
		 $queryString = $this->getQueryStr();
		 if($queryString) {
			$collection = Mage::helper('coobus_catalog')
			->getSearchCollection($queryString, $this->getRequest());
			}
			if(!$collection) {
			//@nelkaake -a 15/12/10: To fix categories column issue this is a tempoary way we are going to load the modified collection class.
			$collection = new Coobus_Catalog_Model_Resource_Eav_Mysql4_Product_Collection();
			}
			*/
		if($this->colIsVisible('categories')) {
			$this->setJoinCategories(true);
		}
		
		$this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('customer')->__('XLS'));

		return  parent::_prepareCollection();
	}

	/**
	 * if the attribute has options an options entry will be
	 * added to $columnOptions
	 */
	protected function loadColumnOptions($attr_code) {
		$attr = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $attr_code);
		if(sizeof($attr->getData()) > 0) {
			if($attr->getFrontendInput() == 'select') {
				//@nelkaake -a 13/11/10:
				if($attr->getSourceModel() != null) {
					$sourcemodel = Mage::getModel($attr->getSourceModel());
					//@nelkaake -a 16/11/10:
					$sourcemodel->setAttribute($attr);
					if(method_exists($sourcemodel, 'getAllOptions')) {
						try {
							//die($attr->getSourceModel());
							$values = $sourcemodel->getAllOptions();

							$options = array();

							foreach($values as $value) {

								$options[$value['value']] = $value['label'];
							}
							//die($attr_code);
							$this->columnOptions[$attr_code] = $options;
							return;
						} catch (Exception $e) {
							Mage::log("Tried to get options for {$attr_code} using getAllOptions on {$attr->getSourceModel()}, but an exception occured: ". (String)$e);
						}
					}
				}
				//@nelkaake -a 13/11/10:
				$values = Mage::getResourceModel('eav/entity_attribute_option_collection')
				->setAttributeFilter($attr->getId())
				->setStoreFilter($this->_getStore()->getId(), false)
				->load();
				$options = array();
				foreach($values as $value) {
					$options[$value->getOptionId()] = $value->getValue();
				}
				//die($attr_code);
				$this->columnOptions[$attr_code] = $options;
				//die(print_r($this->columnOptions, true));
			}
		}

	}

	protected function _prepareColumns()
	{
		$ret = parent::_prepareColumns();
		$this->addColumn('id',
		array(
                    'header'=> Mage::helper('catalog')->__('ID'),
                    'width' => '50px',
                    'type'  => 'number',
                    'index' => 'entity_id',
		));
		$imgWidth = Mage::getStoreConfig('catalog/management/width') + "px";
		$this->addColumn('thumbnail',
		array(
                    'header'=> Mage::helper('catalog')->__('Thumbnail'),
                    'type'  => 'image',
                    'width' => $imgWidth,
                    'index' => 'thumbnail',
		));
		$this->addColumn('small_image',
		array(
                    'header'=> Mage::helper('catalog')->__('Small Img'),
                    'type'  => 'image',
                    'width' => $imgWidth,
                    'index' => 'small_image',
		));
		$this->addColumn('image',
		array(
                    'header'=> Mage::helper('catalog')->__('Image'),
                    'type'  => 'image',
                    'width' => $imgWidth,
                    'index' => 'image',
		));
		$this->addColumn('categories',
		array(
                 'header'=> Mage::helper('catalog')->__('Categories'),
                 'width' => '100px',
                 'sortable'  => true,
                 'index'     => 'categories',
                 'sort_index'     => 'category',
                 'filter_index'     => 'category',
		));

		// EG: Show all (other) needed columns.
		$ignoreCols = array('id'=>true, 'websites'=>true,'status'=>true,'visibility'=>true,'qty'=>true,
                'price'=>true,'sku'=>true,'attribute_set_id'=>true, 'type_id'=>true,'name'=>true, 
                'image'=>true, 'thumbnail' => true, 'small_image'=>true, 'categories'=>true);
		$currency = $this->_getStore()->getBaseCurrency()->getCode();
		$truncate =  Mage::getStoreConfig('catalog/management/truncatelongtextafter');
		$defaults = array(
            'cost'  => array('type'=>'price', 'width'=>'30px', 'header'=> Mage::helper('catalog')->__('Cost'), 'currency_code' => $currency),
            'weight'  => array('type'=>'number', 'width'=>'30px', 'header'=> Mage::helper('catalog')->__('Weight')),
            'url_key'  => array('type'=>'text', 'width'=>'100px', 'header'=> Mage::helper('catalog')->__('Url Key')),
            'tier_price'  => array('type'=>'price', 'width'=>'100px', 'header'=> Mage::helper('catalog')->__('Tier Price'), 'currency_code' => $currency),
            'tax_class_id'  => array('type'=>'text', 'width'=>'100px', 'header'=> Mage::helper('catalog')->__('Tax Class ID')),
            'special_to_date'  => array('type'=>'date', 'width'=>'100px', 'header'=> Mage::helper('catalog')->__('Spshl TO Date')),
		//@nelkaake Tuesday April 27, 2010 :
            'created_at'  => array('type'=>'datetime', 'width'=>'100px', 'header'=> Mage::helper('catalog')->__('Date Created')),
            'special_price'  => array('type'=>'price', 'width'=>'30px', 'header'=> Mage::helper('catalog')->__('Special Price'), 'currency_code' => $currency),
            'special_from_date'  => array('type'=>'date', 'width'=>'100px', 'header'=> Mage::helper('catalog')->__('Spshl FROM Date')),
            'color'  => array('type'=>'text', 'width'=>'70px', 'header'=> Mage::helper('catalog')->__('Color')),
            'size'  => array('type'=>'text', 'width'=>'70px', 'header'=> Mage::helper('catalog')->__('Size')),
            'brand'  => array('type'=>'text', 'width'=>'70px', 'header'=> Mage::helper('catalog')->__('Brand')),
            'custom_design'  => array('type'=>'text', 'width'=>'70px', 'header'=> Mage::helper('catalog')->__('Custom Design')),
            'custom_design_from'  => array('type'=>'date', 'width'=>'70px', 'header'=> Mage::helper('catalog')->__('Custom Design FRM')),
            'custom_design_to'  => array('type'=>'date', 'width'=>'70px', 'header'=> Mage::helper('catalog')->__('Custom Design TO')),
            'default_category_id'  => array('type'=>'text', 'width'=>'70px', 'header'=> Mage::helper('catalog')->__('Default Categry ID')),
            'dimension'  => array('type'=>'text', 'width'=>'75px', 'header'=> Mage::helper('catalog')->__('Dimensions')),
            'manufacturer'  => array('type'=>'text', 'width'=>'75px', 'header'=> Mage::helper('catalog')->__('Manufacturer')),
            'meta_keyword'  => array('type'=>'text', 'width'=>'200px', 'header'=> Mage::helper('catalog')->__('Meta Keywds')),
            'meta_description'  => array('type'=>'text', 'width'=>'200px', 'header'=> Mage::helper('catalog')->__('Meta Descr')),
            'meta_title'  => array('type'=>'text', 'width'=>'100px', 'header'=> Mage::helper('catalog')->__('Meta Title')),
            'short_description'  => array('type'=>'text', 'width'=>'150px', 'header'=> Mage::helper('catalog')->__('Short Description'), 'string_limit'=>$truncate),
            'description'  => array('type'=>'text', 'width'=>'200px', 'header'=> Mage::helper('catalog')->__('Description'), 'string_limit'=>$truncate)
		);

		foreach($this->columnSettings as $col => $true) {
			
			// Loads all the column options for each applicable column.
			$this->loadColumnOptions($col);
			
			if(isset($ignoreCols[$col])) continue;
			if(isset($defaults[$col])) {
				$innerSettings = $defaults[$col];
			} else {
				$innerSettings = array(
                    'header'=> Mage::helper('catalog')->__($col),
                    'width' => '100px',
                    'type'  => 'text',
				);
			}
			$innerSettings['index'] = $col;
			//echo print_r($this->columnOptions, true);
			if(isset($this->columnOptions[$col])) {
				//die($col);
				$innerSettings['type'] = 'options';
				$innerSettings['options'] = $this->columnOptions[$col];
			}
			$this->addColumn($col, $innerSettings);
		}

		foreach ( $this->getColumns() as $colName=>$col){
			if ( $colName == 'custom_name' && $this->colIsVisible('name')) continue;
			if ($this->colIsVisible($colName) || $this->colIsVisible($col['index'])) continue;
			unset($this->_columns[$colName]);
		}

		$orderedcolumns = explode(',',Mage::getStoreConfig('catalog/management/orderedcolumns'));
		$after = '';
		foreach($orderedcolumns as $col) {
			$this->addColumnsOrder($col,$after);
			$after = $col;
		}
		$this->sortColumnsByOrder();
		return $ret;
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('entity_id');
		$this->getMassactionBlock()->setFormFieldName('product');

		$this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('catalog')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('catalog')->__('Are you sure?')
		));

		$statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();

		array_unshift($statuses, array('label'=>'', 'value'=>''));
		$this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('catalog')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('catalog')->__('Status'),
                         'values' => $statuses
		)
		)
		));

		$this->getMassactionBlock()->addItem('attributes', array(
            'label' => Mage::helper('catalog')->__('Update attributes'),
            'url'   => $this->getUrl('adminhtml/catalog_product_action_attribute/edit', array('_current'=>true))
		));


		// Divider
		$this->getMassactionBlock()->addItem('imagesDivider', $this->getMADivider("Images"));

		// Show images...
		$imgWidth = Mage::getStoreConfig('catalog/management/width') ;
		$imgHeight = Mage::getStoreConfig('catalog/management/height');
		$this->getMassactionBlock()->addItem('showImages', array(
            'label' => $this->__('Show Selected Images'),
            'url'   => $this->getUrl('coobus_catalog/*/index', array('_current'=>true)),
            'callback' => 'showSelectedImages(productGrid_massactionJsObject, '
            .'{checkedValues}, \'<img src=\\\'{imgurl}\\\' width='.$imgWidth
            .' height='.$imgHeight.' border=0 />\')'
            
            ));
            // Hide Images
            $this->getMassactionBlock()->addItem('hideImages', array(
            'label' => $this->__('Hide Selected Images'),
            'url'   => $this->getUrl('coobus_catalog/*/index', array('_current'=>true)),
            'callback' => 'hideSelectedImages(productGrid_massactionJsObject, {checkedValues})'
            
            ));

            // Divider 3
            $this->getMassactionBlock()->addItem('otherDivider', $this->getMADivider("Other"));

            // Opens all products

            // Refresh...
            $this->getMassactionBlock()->addItem('refreshProducts', array(
            'label' => $this->__('Refresh Products'),
            'url'   => $this->getUrl('coobus_catalog/*/massRefreshProducts', array('_current'=>true))
            ));

            // $this->getMassactionBlock()->addItem('saveEditables', array(
            //     'label' => $this->__('SAVE EDITABLES'),
            //     'url'   => $this->getUrl('*/*/saveEditables', array('_current'=>true)),
            //     'fields' => array('short_description2', '')
            // ));


            return $this;
	}

	/*
	 public function getRowUrl($row)
	 {
		//@nelkaake -m 16/11/10: Changed to use _getStore function
		return $this->getUrl('adminhtml/catalog_product/edit', array(
		'store'=>$this->_getStore(),
		'id'=>$row->getId())
		);
		}

		public function getGridUrl()
		{
		return $this->getUrl('coobus_catalog/*\/grid', array('_current'=>true));
		}
		*/
	protected function getMADivider($dividerHeading="-------") {
		$dividerTemplate = array(
          'label' => '--------'.$this->__($dividerHeading).'--------',
          'url'   => $this->getUrl('*/*/index', array('_current'=>true)),
          'callback' => "null"
          );
          return $dividerTemplate;
	}

	protected function _prepareJoinCategories( $collection ){
		$collection->getSelect()->reset(Zend_Db_Select::GROUP);

		$collection
		->joinField('category_id',
                   'catalog/category_product',
                   'category_id',
                   'product_id=entity_id',
		null,
                   'left');
		$category_name_attribute_id = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_category', 'name')->getId();
			
		//@nelkaake -m 13/11/10: Added support for tables with prefixes
		$ccev_t = Mage::getConfig()->getTablePrefix(). 'catalog_category_entity_varchar';
		$collection->joinField('categories',
					$ccev_t,
                   'GROUP_CONCAT(_table_categories.value)',
                   'entity_id=category_id',
                   "_table_categories.attribute_id={$category_name_attribute_id}",
                   'left');
		$collection->joinField('category',
					$ccev_t,
                   'value',
                   'entity_id=category_id',
                   "_table_category.attribute_id={$category_name_attribute_id}",
                   'left');
		$collection->groupByAttribute('entity_id');
	}
	
	protected function _preparePage()
	{
		parent::_preparePage();
		// EG: Select all needed columns.
		//id,name,type,attribute_set,sku,price,qty,visibility,status,websites,image
		foreach($this->columnSettings as $col => $true) {
			if($this->_isSpecialCol($col)) continue;
			$this->getCollection()->addAttributeToSelect($col);
		}
		if( $this->getJoinCategories() ) {
			$this->_prepareJoinCategories( $this->getCollection() );
		}
		return $this;			
	}

	public function getExport($mode = 'csv')
	{
		$csv = '';
		$this->_isExport = true;

		$this->_prepareGrid();
		$collection = $this->getCollection();
		$collection->clear();
		$collection->getSelect()->limit(0);
		$collection->setPageSize(0);
		$collection->load();
		$this->_afterLoadCollection();

		foreach ($this->_columns as $column) {
			if (!$column->getIsSystem()) {
				$fields[$column->getExportHeader()] = '"'.$column->getExportHeader().'"';
			}
		}
		$filter_ids = array();
		foreach ($collection as $item) {
			$filter_ids[] = $item->getId();
		}
		if ( count($filter_ids) > 0 )
		Mage::register('catalog_product_filter_ids', $filter_ids);
		else
		return;
		$profile = Mage::getModel('dataflow/profile');
		$profile->load( Mage::getStoreConfig('catalog/management/exportprofile_'.$mode) );
		Mage::register('current_convert_profile', $profile);
		$profile->run();
		Mage::unregister('current_convert_profile');
		$batchModel = Mage::getSingleton('dataflow/batch');
		$batchModel->parseFieldList( $fields );
		$content = $batchModel->getOutput();

		return $content;
	}
}