<?php

$installer = $this;

$installer->startSetup();
$installer->getConnection()->addColumn($installer->getTable('banners'), 'sort', 'tinyint(10) default \'0\'');
$installer->endSetup(); 

?>