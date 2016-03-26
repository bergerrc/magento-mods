<?php
$installer = $this;
/* @var $installer Coobus_Checkout_Model_Mysql4_Setup */

$installer->startSetup();

$installer->installEntities();
$installer->getConnection()->addColumn($installer->getTable('sales/quote'), 'is_multi_order', 'tinyint(1) unsigned default \'0\'');
$installer->addAttribute('quote', 'is_multi_order', array('type'=>'static'));
    
$installer->endSetup();
