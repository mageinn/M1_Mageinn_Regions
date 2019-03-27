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
 * Regions tree block
 *
 * @category   Mageinn
 * @package    Mageinn_Regions
 * @author     Mageinn
 */
class Mageinn_Regions_Block_Adminhtml_Tree extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mageinn/regions/tree.phtml');
    } 
    
    protected function _prepareLayout()
    {
        $this->setChild('add_sub_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Add Subregion'),
                    'onclick'   => "addNew(false)",
                    'class'     => 'add',
                    'id'        => 'add_subregion_button'
                ))
        );
        
        $this->setChild('add_group_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Add Root Region'),
                    'onclick'   => "addNew(true)",
                    'class'     => 'add',
                    'id'        => 'add_group_region_button'
                ))
        );
        
        return parent::_prepareLayout();
    }

    public function getAddGroupButtonHtml()
    {
        return $this->getChildHtml('add_group_button');
    }
    
    public function getAddSubButtonHtml()
    {
        return $this->getChildHtml('add_sub_button');
    }
    
    public function getRegionCollection()
    {
        $collection = $this->getData('region_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('mageinn_regions/region')
                    ->getCollection()
                    ->setOrder('position', 'ASC');
            $this->setData('region_collection', $collection);
        }
        return $collection;
    }
    
    public function getEditUrl()
    {
        return $this->getUrl('*/*/edit');
    }
    
    public function getLoadTreeUrl()
    {
        return $this->getUrl('*/*/regionsJson');
    }
    
    public function getMoveUrl()
    {
        return $this->getUrl('*/*/move');
    }
    
    public function getJson($parentId, $activeOnly = false, $regionIds = array())
    {
        $rootArray = array();
        $childRegions = $this->getRegionCollection()->addFieldToFilter("parent_id", $parentId);
        if($activeOnly) {
            $childRegions->addFieldToFilter("is_active", 1);
        }
        
        foreach($childRegions as $region) {
            $regArray = array(  
                'data' => $region->getName(),
                'attr' => array(
                    'id' => $region->getId(), 
                    'is_active' => $region->getIsActive()
                        )
                );
            
            if($region->getChildrenCount() > 0)
            {
                $regArray['children'] = array();
                $regArray['state'] = "closed";
            }
            
            // Checkbox
            if(is_array($regionIds) && in_array($region->getId(), $regionIds)) {
                $regArray['attr']['class'] = 'jstree-checked';
            }
            
            $rootArray[] = $regArray;
        }
        
        return Mage::helper('core')->jsonEncode($rootArray);
    }
}