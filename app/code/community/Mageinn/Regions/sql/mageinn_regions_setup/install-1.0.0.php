<?php
/**
 * Mageinn_Regions extension
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
 * @category    Mageinn
 * @package     Mageinn_Regions
 * @copyright   Copyright (c) 2016 Mageinn. (http://mageinn.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Install script
 *
 * @category   Mageinn
 * @package    Mageinn_Regions
 * @author     Mageinn
 */

$installer = $this;
$installer->startSetup();
$installer->run("DROP TABLE IF EXISTS `{$installer->getTable('mageinn_regions/region')}`;
CREATE TABLE `{$installer->getTable('mageinn_regions/region')}` (
  `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Entity ID',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Parent Region ID',
  `name` varchar(255) NOT NULL COMMENT 'Region Name',
  `url_key` varchar(255) DEFAULT NULL COMMENT 'URL key',
  `path` varchar(255) NOT NULL COMMENT 'Tree Path',
  `rtype` varchar(255) NOT NULL COMMENT 'Region Type',
  `position` int(11) NOT NULL DEFAULT '0' COMMENT 'Position',
  `is_active` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Is Active',
  PRIMARY KEY (`entity_id`),
  KEY `IDX_MAGEINN_REGION_PARENT_ID` (`parent_id`),
  KEY `IDX_MAGEINN_REGION_URL_KEY` (`url_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Region Table';");

$installer->run("DROP TABLE IF EXISTS `{$installer->getTable('mageinn_regions/region_product')}`;
CREATE TABLE `{$installer->getTable('mageinn_regions/region_product')}` (
  `region_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Region ID',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Product ID',
  `position` int(11) NOT NULL DEFAULT '0' COMMENT 'Position',
  PRIMARY KEY (`region_id`,`product_id`),
  KEY `IDX_MAGEINN_REGION_PRODUCT_PRODUCT_ID` (`product_id`),
  CONSTRAINT `FK_FA_PRD_REG_ID_CAT_CTGR_ENTT_ENTT_ID` FOREIGN KEY (`region_id`) REFERENCES `{$installer->getTable('mageinn_regions/region')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_FA_PRD_PRD_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog Product To Region Linkage Table';");

$installer->run("INSERT INTO `{$installer->getTable('mageinn_regions/region')}` (`entity_id`,`parent_id`,`name`,`path`,`rtype`,`position`,`is_active`) VALUES ('1','0','TREE ROOT','1','0','0','1');");

$installer->endSetup();