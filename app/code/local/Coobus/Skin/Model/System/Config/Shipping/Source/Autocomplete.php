<?php

class Coobus_Skin_Model_System_Config_Shipping_Source_Autocomplete {
	
    public function toOptionArray() {
        $options= array();
        
        $options[] = array('label' => 'RepublicaVirtual', 'value' => '1');
        $options[] = array('label' => 'KingHost', 'value' => '2');
        $options[] = array('label' => 'Locaweb', 'value' => '3');
            
        return $options;
    }
    
}
