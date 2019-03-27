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
 * Region collection model
 *
 * @category   Mageinn
 * @package    Mageinn_Regions
 * @author     Mageinn
 */
class Mageinn_Regions_Model_Resource_Region_Collection
    extends Mage_Core_Model_Resource_DB_Collection_Abstract
{
    /**
     * Name of product table
     *
     * @var string
     */
    protected $_productRegionTable;

    
    public function _construct()
    {
        $this->_init('mageinn_regions/region');
        $this->_productRegionTable = $this->getTable('mageinn_regions/region_product');
    }
    
    /**
     * Add Product To Filter
     * NOTUSED
     *
     * @param int $productId
     * @return Mageinn_Regions_Model_Resource_Region_Collection
     */
    public function addProductToFilter($productId)
    {
        $this->getSelect()
                ->join(
                        array('pr' => $this->_productRegionTable),
                        'main_table.entity_id=pr.region_id AND ' . $this->getConnection()->quoteInto('pr.product_id=?',$productId),
                        array()
                );
        return $this;
    }
}