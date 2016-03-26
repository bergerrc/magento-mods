<?php
$installer = $this;

$installer->startSetup();

$installer->run("
-- SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `{$this->getTable('catalog_product_store')}`;
CREATE TABLE IF NOT EXISTS `{$this->getTable('catalog_product_store')}` (
  `product_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`store_id`),
  KEY `FK_CATALOG_PRODUCT_STORE_STORE` (`store_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED AUTO_INCREMENT=8 ;

ALTER TABLE `{$this->getTable('catalog_product_store')}`
  ADD CONSTRAINT `FK_CATALOG_PRODUCT_STORE_STORE` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_CATALOG_STORE_PRODUCT_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- SET FOREIGN_KEY_CHECKS = 1;  
");

$installer->endSetup();