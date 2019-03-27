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
 * Regions edit Form
 *
 * @category   Mageinn
 * @package    Mageinn_Regions
 * @author     Mageinn
 */
class Mageinn_Regions_Block_Adminhtml_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form 
{

    public function getRegion() 
    {
        if (!$this->_region) {
            $this->_region = Mage::registry('region');
        }
        return $this->_region;
    }

    /**
     * Preparing form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() 
    {
        $form = new Varien_Data_Form(
                        array(
                            'id' => 'region_edit_form',
                            'action' => $this->getUrl('*/*/save'),
                            'method' => 'post',
                            'target' => 'iframeSave'
                        )
        );
        
        $form->setDataObject($this->getRegion());
        $form->setUseContainer(false);

        $helper = Mage::helper('mageinn_regions');
        $fieldset = $form->addFieldset('display', array(
            'legend' => $helper->__('General Information'),
            'class' => 'fieldset-wide'
                ));

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => $helper->__('Name'),
            'required' => true
        ));
        
        $fieldset->addField('is_active', 'select', array(
            'name' => 'is_active',
            'label' => $helper->__('Is Active'),
            'required' => false,
            'value' => '1',
            'values' => array('1' => 'Yes', '0' => 'No'),
        ));
        
        $fieldset->addField('url_key', 'text', array(
            'name' => 'url_key',
            'label' => $helper->__('URL Key'),
            'required' => false
        ));
        
        $fieldset->addField('rtype', 'select', array(
            'name' => 'rtype',
            'label' => $helper->__('Type'),
            'required' => false,
            'value' => '1',
            'values' => array('1' => 'Region', '2' => 'City', '3' => 'Tube Station'),
        ));
        

        if ($this->getRegion()->getId()) {
            $form->setValues($this->getRegion()->getData());
        }
        
        $fieldset->addField('id', 'hidden', array(
            'name' => 'id',
            'value' => intval($this->getRegion()->getId())
        ));
        $fieldset->addField('parent', 'hidden', array(
            'name' => 'parent',
            'value' => intval($this->getRegion()->getParentId())
        ));
        
        $this->setForm($form);

        return parent::_prepareForm();
    }
}