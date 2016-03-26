<?php

$installer = $this;
/* @var $installer Mage_Catalog_Model_Entity_Setup */

$installer->run("
    ALTER TABLE {$this->getTable('catalogsearch_query')} CHANGE `display_in_terms` `display_in_terms` TINYINT( 1 ) NOT NULL DEFAULT '0';
");
