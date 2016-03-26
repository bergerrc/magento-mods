<?php

class Coobus_Catalog_Model_Convert_Parser_Xml_Excel extends Mage_Dataflow_Model_Convert_Parser_Xml_Excel
{
    /**
     * Read data collection and write to temporary file
     *
     * @return Mage_Dataflow_Model_Convert_Parser_XML_Excel
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

        $out = '<'.'?xml version="1.0"?'.'><'.'?mso-application progid="Excel.Sheet"?'
            .'><Workbook'
            .' xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            .' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
            .' xmlns:x="urn:schemas-microsoft-com:office:excel"'
            .' xmlns:x2="http://schemas.microsoft.com/office/excel/2003/xml"'
            .' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'
            .' xmlns:o="urn:schemas-microsoft-com:office:office"'
            .' xmlns:html="http://www.w3.org/TR/REC-html40"'
            .' xmlns:c="urn:schemas-microsoft-com:office:component:spreadsheet">'
            .'<OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">'
            .'</OfficeDocumentSettings>'
            .'<ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">'
            .'</ExcelWorkbook>';

        $wsName = htmlspecialchars($this->getVar('single_sheet'));
        $wsName = !empty($wsName) ? $wsName : Mage::helper('dataflow')->__('Sheet 1');
        $out .= '<Worksheet ss:Name="' . $wsName . '"><Table>';

        if ($this->getVar('fieldnames')) {
            $xmlData = $this->_getXmlString($fieldList);
            $out .= $xmlData;
        }

        foreach ($batchExportIds as $batchExportId) {
            $xmlData = array();
            $batchExport->load($batchExportId);
            $row = $batchExport->getBatchData();

            foreach ($fieldList as $field) {
                $xmlData[] = isset($row[$field]) ? $row[$field] : '';
            }
            $xmlData = $this->_getXmlString($xmlData);
            $out .= $xmlData;
        }

        $out .= '</Table></Worksheet></Workbook>';
        if ( mb_detect_encoding($out, "UTF-8") == "UTF-8" ){
        	$out = utf8_decode($out);
        }
        $this->getBatchModel()->setData('output', $out);        

        return $this;
    }
}
