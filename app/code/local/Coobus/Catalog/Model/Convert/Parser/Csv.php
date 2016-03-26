<?php

class Coobus_Catalog_Model_Convert_Parser_Csv extends Mage_Dataflow_Model_Convert_Parser_Csv
{
	
	// Returns true if $string is valid UTF-8 and false otherwise.
	function is_utf8($string) {
	    
	    // From http://w3.org/International/questions/qa-forms-utf-8.html
        return preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs', $string);
	    
	}
	
	public function parse()
    {
        $batchIoAdapter = $this->getBatchModel()->getIoAdapter();
        $file = $batchIoAdapter->getIoAdapter();
        $content = $file->read( $batchIoAdapter->getFile(true) );
    	//If content is not UTF-8 encoding, convert to UTF-8
		if ( !$this->is_utf8($content) ){
	        $content = utf8_encode($content);
	        if ( !$file->write( $batchIoAdapter->getFile(true), $content ) )
	        	Mage::throwException("File '".$batchIoAdapter->getFile(true)."' must be in UTF-8 format.");
		}


        // fixed for multibyte characters
        setlocale(LC_ALL, Mage::app()->getLocale()->getLocaleCode().'.UTF-8');

        $fDel = $this->getVar('delimiter', ',');
        $fEnc = $this->getVar('enclose', '"');
        if ($fDel == '\t') {
            $fDel = "\t";
        }

        $adapterName   = $this->getVar('adapter', null);
        $adapterMethod = $this->getVar('method', 'saveRow');

        if (!$adapterName || !$adapterMethod) {
            $message = Mage::helper('dataflow')->__('Please declare "adapter" and "method" nodes first.');
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        try {
            $adapter = Mage::getModel($adapterName);
        }
        catch (Exception $e) {
            $message = Mage::helper('dataflow')->__('Declared adapter %s was not found.', $adapterName);
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        if (!is_callable(array($adapter, $adapterMethod))) {
            $message = Mage::helper('dataflow')->__('Method "%s" not defined in adapter %s.', $adapterMethod, $adapterName);
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        $batchModel = $this->getBatchModel();
        $batchIoAdapter = $this->getBatchModel()->getIoAdapter();

        if (Mage::app()->getRequest()->getParam('files')) {
            $file = Mage::app()->getConfig()->getTempVarDir().'/import/'
                . urldecode(Mage::app()->getRequest()->getParam('files'));
            $this->_copy($file);
        }

        $batchIoAdapter->open(false);

        $isFieldNames = $this->getVar('fieldnames', '') == 'true' ? true : false;
        $map = is_array($this->getVar('map'))? $this->getVar('map'): false;
        $defaults = is_array($this->getVar('default'))? $this->getVar('default'): false;
        if (!$isFieldNames && $map) {
            $fieldNames = $this->getVar('map');
        }
        else {
            $fieldNames = array();
            foreach ($batchIoAdapter->read(true, $fDel, $fEnc) as $v) {
                $fieldNames[$v] = $v;
            }
            
            if ( $map ){
                foreach ($fieldNames as $field) {
                	if ( array_key_exists($field, $map) )
                		$fieldNames[$field]=$map[$field];
            	}
            }
            if ( $defaults ){
                foreach ($defaults as $field=>$value) {
                	if ( !array_key_exists($field, $fieldNames) )
                		$fieldNames[$field]=$field;
            	}
            }
        }

        $countRows = 0;
        while (($csvData = $batchIoAdapter->read(true, $fDel, $fEnc)) !== false) {
            if (count($csvData) == 1 && $csvData[0] === null) {
                continue;
            }

            $itemData = array();
            $countRows ++; $i = 0;         
            foreach ($fieldNames as $field) {
            	if ( isset($csvData[$i]) ){
                	$itemData[$field] = $csvData[$i];
            	}else if ( isset($defaults[$field]) ){
                	$itemData[$field] = Mage::helper("coobus_catalog")->filter( $defaults[$field] );
            	}else{
                	$itemData[$field] = null;
            	}
                $i ++;
            }

            $batchImportModel = $this->getBatchImportModel()
                ->setId(null)
                ->setBatchId($this->getBatchModel()->getId())
                ->setBatchData($itemData)
                ->setStatus(1)
                ->save();
        }

        $this->addException(Mage::helper('dataflow')->__('Found %d rows.', $countRows));
        $this->addException(Mage::helper('dataflow')->__('Starting %s :: %s', $adapterName, $adapterMethod));

        $batchModel->setParams($this->getVars())
            ->setAdapter($adapterName)
            ->save();

        return $this;
    }
    /**
     * Read data collection and write to temporary file
     *
     * @return Mage_Dataflow_Model_Convert_Parser_Csv
     */
    public function unparse()
    {
        $batchExport = $this->getBatchExportModel()
            ->setBatchId($this->getBatchModel()->getId());
        $fieldList = $this->getBatchModel()->getFieldList();
        $batchExportIds = $batchExport->getIdCollection();

        if (!$batchExportIds) {
            return $this;
        }

        $out = '';

        if ($this->getVar('fieldnames')) {
            $csvData = $this->getCsvString($fieldList);
            $out .= $csvData;
        }

        foreach ($batchExportIds as $batchExportId) {
            $csvData = array();
            $batchExport->load($batchExportId);
            $row = $batchExport->getBatchData();

            foreach ($fieldList as $field) {
                $csvData[] = isset($row[$field]) ? $row[$field] : '';
            }
            $csvData = $this->getCsvString($csvData);
            $out .= $csvData;
        }

        if ( mb_detect_encoding($out, "UTF-8") == "UTF-8" ){
        	$out = utf8_decode($out);
        }
        $this->getBatchModel()->setData('output', $out);        

        return $this;
    }
}
