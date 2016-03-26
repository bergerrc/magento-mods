<?php
/**
 * Beeapps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 *
 * @category    Beeapps
 * @package     Beeapps_Tax
 * @copyright   Beeapps Inc. (http://www.beeapps.com.br)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author Beeapps Developer <developer@beeapps.com.br>
 */


$installer = $this;
/* $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('tax_class')} CHANGE `class_type` `class_type` enum('CUSTOMER','PRODUCT','PAYMENT_METHOD') NOT NULL default 'CUSTOMER';
");

/*
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('tax_class')};
CREATE TABLE {$this->getTable('tax_class')} (
  `class_id` smallint(6) NOT NULL auto_increment,
  `class_name` varchar(255) NOT NULL default '',
  `class_type` enum('CUSTOMER','PRODUCT') NOT NULL default 'CUSTOMER',
  PRIMARY KEY  (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


insert  into {$this->getTable('tax_class')}(`class_id`,`class_name`,`class_type`) values (2,'Taxable Goods','PRODUCT'),(3,'Retail Customer','CUSTOMER');


-- DROP TABLE IF EXISTS {$this->getTable('tax_rate')};

CREATE TABLE {$this->getTable('tax_rate')} (
  `tax_rate_id` tinyint(4) NOT NULL auto_increment,
  `tax_county_id` smallint(6) default NULL,
  `tax_region_id` mediumint(9) unsigned default NULL,
  `tax_postcode` varchar(12) default NULL,
  PRIMARY KEY  (`tax_rate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Base tax rates';

insert  into {$this->getTable('tax_rate')}(`tax_rate_id`,`tax_county_id`,`tax_region_id`,`tax_postcode`) values (1,0,12,NULL),(2,0,43,NULL);

-- DROP TABLE IF EXISTS {$this->getTable('tax_rate_data')};

CREATE TABLE {$this->getTable('tax_rate_data')} (
  `tax_rate_data_id` tinyint(4) NOT NULL auto_increment,
  `tax_rate_id` tinyint(4) NOT NULL default '0',
  `rate_value` decimal(12,4) NOT NULL default '0.0000',
  `rate_type_id` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`tax_rate_data_id`),
  KEY `rate_id` (`tax_rate_id`),
  KEY `rate_type_id` (`rate_type_id`),
  UNIQUE idx_rate_rate_type (tax_rate_id, rate_type_id),
  CONSTRAINT `FK_TAX_RATE_DATA_TAX_RATE` FOREIGN KEY (`tax_rate_id`) REFERENCES {$this->getTable('tax_rate')} (`tax_rate_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_TAX_RATE_DATE_TAX_RATE_TYPE` FOREIGN KEY (`rate_type_id`) REFERENCES {$this->getTable('tax_rate_type')} (`type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


insert  into {$this->getTable('tax_rate_data')}(`tax_rate_data_id`,`tax_rate_id`,`rate_value`,`rate_type_id`) values (6,2,8.3750,1),(7,2,0.0000,2),(8,2,0.0000,3),(9,2,0.0000,4),(10,2,0.0000,5),(31,1,8.2500,1),(32,1,0.0000,2),(33,1,0.0000,3),(34,1,0.0000,4),(35,1,0.0000,5);

-- DROP TABLE IF EXISTS {$this->getTable('tax_rate_type')};
CREATE TABLE {$this->getTable('tax_rate_type')} (
  `type_id` tinyint(4) NOT NULL auto_increment,
  `type_name` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert  into {$this->getTable('tax_rate_type')}(`type_id`,`type_name`) values (1,'Rate 1'),(2,'Rate 2'),(3,'Rate 3'),(4,'Rate 4'),(5,'Rate 5');

-- DROP TABLE IF EXISTS {$this->getTable('tax_rule')};
CREATE TABLE {$this->getTable('tax_rule')} (
  `tax_rule_id` tinyint(4) NOT NULL auto_increment,
  `tax_customer_class_id` smallint(6) NOT NULL default '0',
  `tax_product_class_id` smallint(6) NOT NULL default '0',
  `tax_rate_type_id` tinyint(4) NOT NULL default '0',
  `tax_shipping` tinyint (1)  NOT NULL default '0',
  PRIMARY KEY  (`tax_rule_id`),
  KEY `tax_customer_class_id` (`tax_customer_class_id`,`tax_product_class_id`),
  KEY `tax_customer_class_id_2` (`tax_customer_class_id`),
  KEY `tax_product_class_id` (`tax_product_class_id`),
  KEY `tax_rate_id` (`tax_rate_type_id`),
  CONSTRAINT `FK_TAX_RULE_TAX_CLASS_CUSTOMER` FOREIGN KEY (`tax_customer_class_id`) REFERENCES {$this->getTable('tax_class')} (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_TAX_RULE_TAX_CLASS_PRODUCT` FOREIGN KEY (`tax_product_class_id`) REFERENCES {$this->getTable('tax_class')} (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert  into {$this->getTable('tax_rule')}(`tax_rule_id`,`tax_customer_class_id`,`tax_product_class_id`,`tax_rate_type_id`) values (1,3,2,1);

CREATE TABLE `{$installer->getTable('tax/sales_order_tax')}` (
    `tax_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `order_id` int(10) unsigned NOT NULL,
    `code` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `percent` decimal(12,4) NOT NULL,
    `amount` decimal(12,4) NOT NULL,
    `priority` int(11) NOT NULL,
    `position` int(11) NOT NULL,
    `base_amount` decimal(12,4) NOT NULL,
    `process` smallint(6) NOT NULL,
    `base_real_amount` decimal(12,4) NOT NULL,
    `hidden` smallint(5) unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`tax_id`),
    KEY `IDX_ORDER_TAX` (`order_id`,`priority`,`position`)
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8;

insert  into `{$installer->getTable('tax_class')}`(`class_id`,`class_name`,`class_type`) values (2,'Taxable Goods','PRODUCT');
insert  into `{$installer->getTable('tax_class')}`(`class_id`,`class_name`,`class_type`) values (3,'Retail Customer','CUSTOMER');
insert  into `{$installer->getTable('tax_class')}`(`class_id`,`class_name`,`class_type`) values (4,'Shipping','PRODUCT');
insert  into `{$installer->getTable('tax_calculation')}`(`tax_calculation_rate_id`,`tax_calculation_rule_id`,`customer_tax_class_id`,`product_tax_class_id`) values (1,1,3,2);
insert  into `{$installer->getTable('tax_calculation')}`(`tax_calculation_rate_id`,`tax_calculation_rule_id`,`customer_tax_class_id`,`product_tax_class_id`) values (2,1,3,2);
insert  into `{$installer->getTable('tax_calculation_rate')}`(`tax_calculation_rate_id`,`tax_country_id`,`tax_region_id`,`tax_postcode`,`code`,`rate`,`zip_is_range`,`zip_from`,`zip_to`) values (1,'US',12,'*','US-CA-*-Rate 1','8.2500',NULL,NULL,NULL);
insert  into `{$installer->getTable('tax_calculation_rate')}`(`tax_calculation_rate_id`,`tax_country_id`,`tax_region_id`,`tax_postcode`,`code`,`rate`,`zip_is_range`,`zip_from`,`zip_to`) values (2,'US',43,'*','US-NY-*-Rate 1','8.3750',NULL,NULL,NULL);
insert  into `{$installer->getTable('tax_calculation_rule')}`(`tax_calculation_rule_id`,`code`,`priority`,`position`) values (1,'Retail Customer-Taxable Goods-Rate 1',1,1);

    ");
*/

/*
$installer->addAttribute('invoice', 'shipping_tax_amount', array('type'=>'decimal'));
$installer->addAttribute('invoice', 'base_shipping_tax_amount', array('type'=>'decimal'));

$installer->addAttribute('creditmemo', 'shipping_tax_amount', array('type'=>'decimal'));
$installer->addAttribute('creditmemo', 'base_shipping_tax_amount', array('type'=>'decimal'));

$installer->addAttribute('quote_item', 'price_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('quote_item', 'base_price_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('quote_item', 'row_total_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('quote_item', 'base_row_total_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('quote_address_item', 'price_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('quote_address_item', 'base_price_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('quote_address_item', 'row_total_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('quote_address_item', 'base_row_total_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('quote_address', 'subtotal_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('quote_address', 'base_subtotal_total_incl_tax', array('type'=>'decimal'));

$installer->addAttribute('order_item', 'price_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('order_item', 'base_price_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('order_item', 'row_total_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('order_item', 'base_row_total_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('order', 'subtotal_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('order', 'base_subtotal_incl_tax', array('type'=>'decimal'));

$installer->addAttribute('invoice_item', 'price_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('invoice_item', 'base_price_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('invoice_item', 'row_total_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('invoice_item', 'base_row_total_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('invoice', 'subtotal_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('invoice', 'base_subtotal_incl_tax', array('type'=>'decimal'));

$installer->addAttribute('creditmemo_item', 'price_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('creditmemo_item', 'base_price_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('creditmemo_item', 'row_total_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('creditmemo_item', 'base_row_total_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('creditmemo', 'subtotal_incl_tax', array('type'=>'decimal'));
$installer->addAttribute('creditmemo', 'base_subtotal_incl_tax', array('type'=>'decimal'));
*/
$installer->endSetup();
