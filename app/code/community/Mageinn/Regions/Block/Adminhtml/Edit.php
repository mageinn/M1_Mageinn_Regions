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
 * Regions container block
 *
 * @category   Mageinn
 * @package    Mageinn_Regions
 * @author     Mageinn
 */
class Mageinn_Regions_Block_Adminhtml_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
    * Constructor
    */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'mageinn_regions';
        $this->_controller = 'adminhtml';
        $this->_mode = 'edit';
        $this->_headerText = Mage::helper('mageinn_regions')->__('Edit Form');
    }
    
    /**
     * Retrieve current region instance
     *
     * @return Mageinn_Regions_Model_Region
     */
    public function getRegion()
    {
        return Mage::registry('region');
    }
    
    public function getRegionId()
    {
        if ($this->getRegion()) {
            return $this->getRegion()->getId();
        }
        return 0;
    }
    
    public function getRegionName()
    {
        return $this->getRegion()->getName();
    }
    
    protected function _prepareLayout()
    {
        if (!$region = $this->getRegion()) {
            $regionId = 0;
        } else {
            $regionId = (int) $region->getId(); // 0 when we create region, otherwise some value for editing region
        }
        
        // Save button
        $this->setChild('save_region_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('mageinn_regions')->__('Save'),
                    'onclick'   => "regionSubmit()",
                    'class'     => 'save'
                ))
        );

        // Delete button
        $this->setChild('delete_region_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('mageinn_regions')->__('Delete'),
                    'onclick'   => "regionDelete({$regionId})",
                    'class'     => 'delete'
                ))
        );

        // Reset button
        $this->setChild('reset_region_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('mageinn_regions')->__('Reset'),
                    'onclick'   => "regionReset({$regionId})"
                ))
        );
        
        return parent::_prepareLayout();
    }
    
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_region_button');
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_region_button');
    }

    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_region_button');
    }
    
    public function getHeader()
    {
        if ($this->getRegionId()) {
            return $this->getRegionName();
        } else {
            return Mage::helper('mageinn_regions')->__('New Region');
        }
    }
    
    public function getSaveUrl(array $args = array())
    {
        return $this->getUrl('*/*/save', $args);
    }
    
    public function getAddUrl()
    {
        return $this->getUrl('*/*/add');
    }
    
    public function getEditUrl()
    {
        return $this->getUrl('*/*/edit');
    }
}