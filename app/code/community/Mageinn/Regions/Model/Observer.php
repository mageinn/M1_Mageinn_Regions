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
 * Observer
 *
 * @category   Mageinn
 * @package    Mageinn_Regions
 * @author     Mageinn
 */
class Mageinn_Regions_Model_Observer
{
    /**
     * This method will add "Regions" tab to product edit
     *
     * @param Varien_Event_Observer $observer
     */
    public function addExtraTab(Varien_Event_Observer $observer) {
        if(Mage::helper('mageinn_regions')->isEnabled()) {
            $request = Mage::app()->getRequest();
            $block = $observer->getEvent()->getBlock();

            if ( $block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs ) {
                $setId  = (int) Mage::app()->getFrontController()->getRequest()->getParam('set');
                
                if( $request->getActionName() != "new" || $setId > 0) {
                    $block->addTabAfter('regions', array(
                        'label'     => Mage::helper('mageinn_regions')->__('Regions'),
                        'url'       => $block->getUrl('regions/adminhtml_product/index', array('_current' => true)),
                        'class'     => 'ajax',
                    ),'categories');
                }
            }
        }
    }
    
    /**
     * This method will save regions
     *
     * @param Varien_Event_Observer $observer
     */
    public function saveRegions($observer)
    {
        $product = $observer->getProduct(); 
        Mage::getResourceModel('mageinn_regions/region')->assignToProduct($product);
    }
    
    /**
     * This method will add Region Router
     *
     * @param Varien_Event_Observer $observer
     */
    public function addRouter($observer) 
    {
        $region = new Mageinn_Regions_Controller_Router();
        $observer->getEvent()->getFront()->addRouter('region', $region);
        Mage::app()->getRequest()->isStraight(true);
    }
    
    /**
     * This method will filter product collection be Region if specified
     *
     * @param Varien_Event_Observer $observer
     */
    public function filterByRegion($observer)
    {
        $event = $observer->getEvent();
        $collection = $event->getCollection();
        $region = Mage::registry('region_filter');
        
        if(is_object($region) && $region->getId()) {
            $collection->getSelect()->join( 
                    array('pr' => $collection->getTable('mageinn_regions/region_product')),
                    'e.entity_id = pr.product_id AND ' . $collection->getConnection()->quoteInto('pr.region_id=?',$region->getId()), 
                    array());
        }
    }
}