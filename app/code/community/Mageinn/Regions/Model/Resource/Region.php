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
 * Region resource model
 *
 * @category   Mageinn
 * @package    Mageinn_Regions
 * @author     Mageinn
 */
class Mageinn_Regions_Model_Resource_Region
    extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Name of product table
     *
     * @var string
     */
    protected $_productRegionTable;
    
    
    /**
     * constructor function
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageinn_regions/region', 'entity_id');
        $this->_productRegionTable = $this->getTable('mageinn_regions/region_product');
    }
    
    /**
     * Process region data before saving
     *
     * @param Varien_Object $object
     * @return Mageinn_Regions_Model_Resource_Region
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeSave($object);

        if (!$object->getId()) {
            $object->setPosition($this->_getMaxPosition($object->getParentId()) + 1);
            $object->setPath($object->getPath() . '/');
        }
        
        return $this;
    }
    
    /**
     * Process region data after save region object
     * update path value
     *
     * @param Varien_Object $object
     * @return Mageinn_Regions_Model_Resource_Region
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /**
         * Add identifier for new category
         */
        if (substr($object->getPath(), -1) == '/') {
            $object->setPath($object->getPath() . $object->getId());
            $this->_savePath($object);
        }

        return parent::_afterSave($object);
    }

    /**
     * Update path field
     *
     * @param Mageinn_Regions_Model_Region $object
     * @return Mageinn_Regions_Model_Resource_Region
     */
    protected function _savePath($object)
    {
        if ($object->getId()) {
            $this->_getWriteAdapter()->update(
                $this->getMainTable(),
                array('path' => $object->getPath()),
                array('entity_id = ?' => $object->getId())
            );
        }
        return $this;
    }
    
    
    /**
     * Get maximum position of child regions by $parentId
     *
     * @param int $parentId
     * @return int
     */
    protected function _getMaxPosition($parentId)
    {
        $adapter = $this->getReadConnection();
        $positionField = $adapter->quoteIdentifier('position');
        $bind = array(
            'r_parent_id' => intval($parentId),
        );
        $select  = $adapter->select()
            ->from($this->getTable('mageinn_regions/region'), 'MAX(' . $positionField . ')')
            ->where($adapter->quoteIdentifier('parent_id') . ' = :r_parent_id');

        $position = $adapter->fetchOne($select, $bind);
        if (!$position) {
            $position = 0;
        }
        return $position;
    }

    /**
     * Check region is forbidden to delete.
     * If region is root and assigned to store group return false
     *
     * @param int $regionId
     * @return boolean
     */
    public function isForbiddenToDelete($regionId)
    {
        /*$select = $this->_getReadAdapter()->select()
            ->from($this->getTable('core/store_group'), array('group_id'))
            ->where('root_region_id = :root_region_id');
        $result = $this->_getReadAdapter()->fetchOne($select,  array('root_region_id' => $regionId));

        if ($result) {
            return true;
        }*/
        return false;
    }
    
    
    /**
     * Process region data before delete
     * delete child regions
     *
     * @param Varien_Object $object
     * @return Mageinn_Regions_Model_Resource_Region
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeDelete($object);

        $this->deleteChildren($object);
        return $this;
    }
    
    /**
     * Delete children regions of specific region
     *
     * @param Varien_Object $object
     * @return Mageinn_Regions_Model_Resource_Region
     */
    public function deleteChildren(Varien_Object $object)
    {
        $adapter = $this->_getWriteAdapter();
        $pathField = $adapter->quoteIdentifier('path');

        $select = $adapter->select()
            ->from($this->getMainTable(), array('entity_id'))
            ->where($pathField . ' LIKE :c_path');

        $childrenIds = $adapter->fetchCol($select, array('c_path' => $object->getPath() . '/%'));

        if (!empty($childrenIds)) {
            $adapter->delete(
                $this->getMainTable(),
                array('entity_id IN (?)' => $childrenIds)
            );
        }

        /**
         * Add deleted children ids to object
         * This data can be used in after delete event
         */
        $object->setDeletedChildrenIds($childrenIds);
        return $this;
    }

    
    /**
     * Move category to another parent node
     *
     * @param Mageinn_Regions_Model_Region $category
     * @param Mageinn_Regions_Model_Region $newParent
     * @param int $newPosition
     * @return Mageinn_Regions_Model_Resource_Region
     */
    public function changeParent($region, $newParent, $newPosition)
    {
        $table          = $this->getMainTable();
        $adapter        = $this->_getWriteAdapter();
        $pathField      = $adapter->quoteIdentifier('path');

        $this->_processPositions($region, $newParent, $newPosition);

        $newPath        = sprintf('%s/%s', $newParent->getPath(), $region->getId());

        /**
         * Update children nodes path
         */
        $adapter->update(
            $table,
            array(
                'path' => new Zend_Db_Expr('REPLACE(' . $pathField . ','.
                    $adapter->quote($region->getPath() . '/'). ', '.$adapter->quote($newPath . '/').')'
                ),
            ),
            array($pathField . ' LIKE ?' => $region->getPath() . '/%')
        );
        /**
         * Update moved category data
         */
        $data = array(
            'path'      => $newPath,
            'position'  => $newPosition,
            'parent_id' => $newParent->getId()
        );
        $adapter->update($table, $data, array('entity_id = ?' => $region->getId()));

        // Update region object to new data
        $region->addData($data);

        return $this;
    }
    
    
    /**
     * TODO
     * Process positions of old parent region children and new parent region children.
     * Get position for moved region
     *
     * @param Mageinn_Regions_Model_Region $region
     * @param Mageinn_Regions_Model_Region $newParent
     * @param int $newPosition
     * @return void
     */
    protected function _processPositions($region, $newParent, $newPosition)
    {
        $table          = $this->getMainTable();
        $adapter        = $this->_getWriteAdapter();
        $positionField  = $adapter->quoteIdentifier('position');

        // Rearrange positions under old parent
        $bind = array(
            'position' => new Zend_Db_Expr($positionField . ' - 1')
        );
        $where = array(
            'parent_id = ?'         => $region->getParentId(),
            $positionField . ' > ?' => $region->getPosition()
        );
        $adapter->update($table, $bind, $where);
        
        // Rearrange positions under new parent
        $bind = array(
            'position' => new Zend_Db_Expr($positionField . ' + 1')
        );
        $where = array(
            'parent_id = ?'         => $newParent->getId(),
            $positionField . ' > ?' => $newPosition
        );
        $adapter->update($table,$bind,$where);
    }
    
    /**
     * Retrieve product region identifiers
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getRegionIds($product)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->_productRegionTable, 'region_id')
            ->where('product_id = ?', (int)$product->getId());

        return $adapter->fetchCol($select);
    }
    
    /**
     * Save product category relations
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $regionIds
     * @return Mageinn_Regions_Model_Resource_Region
     */
    public function assignToProduct($product)
    {
        /**
         * If region ids data is not declared we haven't do manipulations
         */
        if (!$product->hasRegionIds()) {
            return $this;
        }
        $regionIds = explode(",",$product->getRegionIds());
        $oldRegionIds = $this->getRegionIds($product);

        $product->setIsChangedRegions(false);

        $insert = array_diff($regionIds, $oldRegionIds);
        $delete = array_diff($oldRegionIds, $regionIds);

        $write = $this->_getWriteAdapter();
        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $regionId) {
                if (empty($regionId)) {
                    continue;
                }
                $data[] = array(
                    'region_id' => (int)$regionId,
                    'product_id'  => (int)$product->getId(),
                    'position'    => 1
                );
            }
            if ($data) {
                $write->insertMultiple($this->_productRegionTable, $data);
            }
        }

        if (!empty($delete)) {
            foreach ($delete as $regionId) {
                $where = array(
                    'product_id = ?'  => (int)$product->getId(),
                    'region_id = ?' => (int)$regionId,
                );

                $write->delete($this->_productRegionTable, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $product->setAffectedRegionIds(array_merge($insert, $delete));
            $product->setIsChangedRegions(true);
        }

        return $this;
    }
    
    
    /**
     * Validate region
     *
     * @param Mageinn_Regions_Model_Region $region
     * @return bool|array
     */
    public function validate($region)
    {
        $errors = array();
        $name = $region->getName();
        $urlKey = $region->getUrlKey();
        
        if(empty($name))
            $errors['name'] = true;
        
        // Check URL
        if(empty($urlKey)) {
            if($region->getId()) {
                $urlKey = $region->getOrigData("url_key");
            } else {
                $urlKey = $this->_formatUrlKey($name);
            }
        }
        
        // Validate
        if(!$this->_validateUrlKey($urlKey, $region->getId())){
            $errors['url_key'] = "URL Key already exists.";
        }
        
        $region->setUrlKey($urlKey);
                
        if (!$errors) {
            return true;
        }

        return $errors;
    }
    
    /**
     * Format Key for URL
     *
     * @param string $str
     * @return string
     */
    protected function _formatUrlKey($str)
    {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }
    
    /**
     * Validate URL Key
     *
     * @param string $str
     * @return boolean
     */
    protected function _validateUrlKey($urlKey, $regionId)
    {
        $urlKey = $this->_formatUrlKey($urlKey);
        
        if(empty($urlKey))
            return false;
        
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'entity_id')
            ->where('url_key = ?', $urlKey);

        $entityId = (int) $adapter->fetchOne($select);
        if($entityId > 0) {
            if($entityId != $regionId)
                return false;
        }
        
        return true;
    }
}