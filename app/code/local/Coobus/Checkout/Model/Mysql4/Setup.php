<?php

class Coobus_Checkout_Model_Mysql4_Setup extends Mage_Sales_Model_Mysql4_Setup
{

    public function getDefaultEntities()
    {    	
    	$salesEntityArr = parent::getDefaultEntities();
    	$salesEntityArr['quote']['attributes'] = 
         	array_merge( $salesEntityArr['quote']['attributes'],        
        		array(
                    'is_multi_order' => array('type'=>'static')
                ));
        return $salesEntityArr;
    }
}
