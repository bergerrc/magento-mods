<?php

class Coobus_Catalog_Model_System_Config_Source_Dataflow_Profile
{
    public function toOptionArray()
    {
        $sorts = array();
        $profiles = Mage::getModel('dataflow/profile')->getCollection();
        foreach( $profiles as $p ){
        	$sorts[] = array('value' => $p->getId(),   'label' => $p->getName());
        }
        return $sorts;
    }
}