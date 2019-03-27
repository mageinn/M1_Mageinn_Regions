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
 * Admin region product controller
 *
 * @category   Mageinn
 * @package    Mageinn_Regions
 * @author     Mageinn
 */
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Catalog' . DS . 'ProductController.php';
class Mageinn_Regions_Adminhtml_ProductController
    extends Mage_Adminhtml_Catalog_ProductController
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * Json action
     */
    public function regionsJsonAction()
    {
        $this->_initProduct();
        $parentId = (int) $this->getRequest()->getParam('region');
        if (!$parentId) {
            $parentId = Mageinn_Regions_Model_Region::TREE_ROOT_ID;
        }
        
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('mageinn_regions/adminhtml_product_edit_tab_regions')
                ->getRegionsJson($parentId)
        );
    }
}