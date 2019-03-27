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
 * Region model
 *
 * @category   Mageinn
 * @package    Mageinn_Regions
 * @author     Mageinn
 */
class Mageinn_Regions_Model_Region extends Mage_Core_Model_Abstract
{
    const TREE_ROOT_ID          = 1;
    
    const RTYPE_REGION          = 1;
    const RTYPE_CITY            = 2;
    const RTYPE_TUBE            = 3;
    
    /**
     * constructor function
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('mageinn_regions/region');
    }
    
    /**
     * Get parent region object
     *
     * @return Mageinn_Regions_Model_Region
     */
    public function getParentRegion()
    {
        if (!$this->hasData('parent_region')) {
            $this->setData('parent_region', Mage::getModel('mageinn_regions/region')->load($this->getParentId()));
        }
        return $this->_getData('parent_region');
    }
    
    /**
     * Get children count
     *
     * @return Mageinn_Regions_Model_Region
     */
    public function getChildrenCount()
    {
        return $this->getChildrenCollection()->count();
    }
    
    /**
     * Get children count
     *
     * @return Mageinn_Regions_Model_Region
     */
    public function getChildrenCollection()
    {
        if ($this->getId() && !$this->hasData('children_collection')) {
            $this->setData('children_collection', 
                    Mage::getModel('mageinn_regions/region')
                    ->getCollection()
                    ->addFieldToFilter("parent_id", $this->getId()))
                    ->setOrder('position', 'ASC');
        }
        return $this->_getData('children_collection');
    }
    
    /**
     * Before delete process
     *
     * @return Mageinn_Regions_Model_Region
     */
    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        if ($this->getResource()->isForbiddenToDelete($this->getId())) {
            Mage::throwException("Can't delete root region.");
        }
        return parent::_beforeDelete();
    }
    
    /**
     * Move region
     *
     * @param   int $parentId new parent category id
     * @param   int $position
     * @return  Mageinn_Regions_Model_Region
     */
    public function move($parentId, $position)
    {
        /**
         * Validate new parent region id
         */
        $parent = Mage::getModel('mageinn_regions/region')
            //->setStoreId($this->getStoreId())
            ->load($parentId);

        if (!$parent->getId()) {
            Mage::throwException(
                Mage::helper('catalog')->__('Region move operation is not possible: the new parent region was not found.')
            );
        }

        if (!$this->getId()) {
            Mage::throwException(
                Mage::helper('catalog')->__('Region move operation is not possible: the current region was not found.')
            );
        } elseif ($parent->getId() == $this->getId()) {
            Mage::throwException(
                Mage::helper('catalog')->__('Region move operation is not possible: parent region is equal to child region.')
            );
        }

        $this->_getResource()->beginTransaction();
        try {
            $this->getResource()->changeParent($this, $parent, $position);
            $this->_getResource()->commit();
        } catch (Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }

        return $this;
    } 
    
    /**
     * Validate attribute values
     *
     * @throws Mage_Eav_Model_Entity_Attribute_Exception
     * @return bool|array
     */
    public function validate()
    {
        return $this->_getResource()->validate($this);
    }
}