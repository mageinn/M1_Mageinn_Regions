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
 * Product regions tab
 *
 * @category   Mageinn
 * @package    Mageinn_Regions
 * @author     Mageinn
 */
class Mageinn_Regions_Block_Adminhtml_Product_Edit_Tab_Regions extends Mageinn_Regions_Block_Adminhtml_Tree
{
    /**
     * Specify template to use
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mageinn/regions/product/edit/regions.phtml');
    }
    
    /**
     * Retrieve currently edited product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }
    
    /**
     * Return array with region IDs which the product is assigned to
     *
     * @return array
     */
    protected function getRegionIds()
    {
        return Mage::getResourceModel('mageinn_regions/region')
                ->getRegionIds($this->getProduct());
    }

    /**
     * Forms string out of getRegionIds()
     *
     * @return string
     */
    public function getIdsString()
    {
        return implode(',', $this->getRegionIds());
    }
    
    /**
     * TODO
     *
     * @return int
     */
    public function getRootRegionId()
    {
        return 2;
    }
    
    public function getRegionsJson($parentId) 
    {
        $regionIds = $this->getRegionIds();
        return parent::getJson($parentId, true, $regionIds);
    }
    
}