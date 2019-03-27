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
 * Admin index controller
 *
 * @category   Mageinn
 * @package    Mageinn_Regions
 * @author     Mageinn
 */
class Mageinn_Regions_Adminhtml_IndexController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize requested region and put it into registry.
     *
     * @return Mageinn_Regions_Model_Region
     */
    protected function _initRegion()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Regions'))
             ->_title($this->__('Manage Regions'));

        $regionId = (int) $this->getRequest()->getParam('id',false);
        $region = Mage::getModel('mageinn_regions/region');

        if ($regionId) {
            $region->load($regionId);
        }

        Mage::register('region', $region);
        return $region;
    }
    
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_initRegion();
        
        $this->loadLayout();
        $this->_setActiveMenu('catalog/regions');
        
        $this->_addBreadcrumb(Mage::helper('mageinn_regions')->__('Manage Catalog Regions'),
             Mage::helper('mageinn_regions')->__('Manage Regions')
        );
        
        $this->renderLayout();
    }
    
    /**
     * Edit region page
     */
    public function editAction()
    {
        $this->_initRegion();
        $this->_sendAjaxResponse();
    }
    
    /**
     * Move action
     */
    public function moveAction()
    {
        $region = $this->_initRegion();
        $parentId = intval($this->getRequest()->getParam('ref'));
        $pos = intval($this->getRequest()->getParam('position'));
        
        $region->move($parentId, $pos);
        
        $eventResponse = new Varien_Object(array(
            'status' => 'success',
            'id' => $region->getId()
        ));
        
        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($eventResponse->getData())
        );
    }
    
    /**
     * Region add
     */
    public function addAction($refreshTree = false)
    {
        $data = $this->getRequest()->getPost();
        $isGroup = isset($data['group'])?filter_var($data['group'], FILTER_VALIDATE_BOOLEAN):false;
        $parent = (int) isset($data['parent'])?$data['parent']:0;
        
        if($isGroup || $parent == 0) {
            $parent = 0;
        }
                
        $this->loadLayout();
        $editBlock = $this->getLayout()->createBlock('mageinn_regions/adminhtml_edit');
        
        $eventResponse = new Varien_Object(array(
            'region' => array(
                'title' => Mage::helper('mageinn_regions')->__('New Region'),
                'parent_id' => $parent,
                'entity_id' => 0,
                'name' => '',
                'url_key' => '',
                'is_active' => 1,
                'rtype' => 1
                ),
            'form_key' => Mage::getSingleton('core/session')->getFormKey(),
            'messages' => $this->getLayout()->getMessagesBlock()->getGroupedHtml(),
            'buttons' => $editBlock->getResetButtonHtml() . $editBlock->getSaveButtonHtml(),
            'refreshTree' => $refreshTree
        ));
        
        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($eventResponse->getData())
        );
    }
 
    /**
     * Region save
     */
    public function saveAction()
    {
        if (!$region = $this->_initRegion()) {
            return;
        }
        
        $refreshTree = false;
        if ($data = $this->getRequest()->getPost()) {
            $region->addData($data);
            if (!$region->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
                if (!$parentId) {
                    $parentId = Mageinn_Regions_Model_Region::TREE_ROOT_ID;
                }
                $region->setParentId($parentId);
                
                $parentRegion = Mage::getModel('mageinn_regions/region')->load($parentId);
                $region->setPath($parentRegion->getPath());
            }

            try {
                $validate = $region->validate();
                if ($validate !== true) {
                    foreach ($validate as $field => $error) {
                        if ($error === true) {
                            Mage::throwException(Mage::helper('mageinn_regions')->__('Attribute "%s" is required.', Mage::helper('mageinn_regions')->__($field)));
                        }
                        else {
                            Mage::throwException($error);
                        }
                    }
                }
                
                $region->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mageinn_regions')->__('The region has been saved.'));
                $refreshTree = 'true';
            }
            catch (Exception $e){
                $this->_getSession()->addError($e->getMessage())
                    ->setRegionData($data);
                $refreshTree = 'false';
            }
        }
        
        $this->_sendAjaxResponse($refreshTree);
    }

    /**
     * Delete region
     */
    public function deleteAction()
    {
        if ($id = (int) $this->getRequest()->getParam('id')) {
            try {
                $region = $this->_initRegion();
                Mage::dispatchEvent('regions_controller_region_delete', array('region'=>$region));
                
                $region->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mageinn_regions')->__('The region has been deleted.'));
            } 
            catch (Mage_Core_Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->editAction();
                return;
            } 
            catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('An error occurred while trying to delete the region.'));
                $this->editAction();
                return;
                
            }
        }
        
        $this->addAction(true);
    }  
    
    /**
     * Get tree node (Ajax version)
     */
    public function regionsJsonAction()
    {
        $parentId = (int) $this->getRequest()->getPost('id');

        if (!$parentId) {
            $parentId = Mageinn_Regions_Model_Region::TREE_ROOT_ID;
        }
        
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('mageinn_regions/adminhtml_tree')
                ->getJson($parentId)
        );
    }
    
    /**
     * Send ajax response
     */
    protected function _sendAjaxResponse($refreshTree = false)
    {
        $this->loadLayout();
        $region = Mage::registry('region');
        $editBlock = $this->getLayout()->createBlock('mageinn_regions/adminhtml_edit');
        
        $title = $region->getName() . " (" . Mage::helper('mageinn_regions')->__('ID: ') . $region->getEntityId() . ")";
        $region->setTitle($title);
                
        $eventResponse = new Varien_Object(array(
            'region' => $region->getData(),
            'form_key' => Mage::getSingleton('core/session')->getFormKey(),
            'messages' => $this->getLayout()->getMessagesBlock()->getGroupedHtml(),
            'buttons' => $editBlock->getResetButtonHtml() . $editBlock->getDeleteButtonHtml() . $editBlock->getSaveButtonHtml(),
            'refreshTree' => $refreshTree
        ));
        
        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode($eventResponse->getData())
        );
    }
}