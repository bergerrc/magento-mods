<?php
$installer = $this;

$installer->startSetup();

$installer->installEntities();
$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'revenue_share_amount', 'decimal(12,4) unsigned');
    
$installer->endSetup();
