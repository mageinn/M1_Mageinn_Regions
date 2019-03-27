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
 * Search Mini Block
 *
 * @category   Mageinn
 * @package    Mageinn_Regions
 * @author     Mageinn
 */
class Mageinn_Regions_Block_Switch extends Mage_Core_Block_Template
{
    public function getCities()
    {
        return $this->getCollection()
                ->addFieldToFilter("is_active", 1)
                ->addFieldToFilter("rtype", Mageinn_Regions_Model_Region::RTYPE_CITY);
    }
    
    public function getCollection()
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
    
    public function getCurrentRegionName()
    {
        $name = Mage::helper("mageinn_regions")->getRegionData("name");
        if(empty($name))
            $name = $this->__('All Regions');
        
        return $name;
    }
    
    public function getCurrentRegionId()
    {
        return Mage::helper("mageinn_regions")->getRegionData("entity_id");
    }
    
    public function getNewUrl($urlKey)
    {
        $request = Mage::app()->getRequest();
        $port = $request->getServer('SERVER_PORT');
        if ($port) {
            $defaultPorts = array(
                Mage_Core_Controller_Request_Http::DEFAULT_HTTP_PORT,
                Mage_Core_Controller_Request_Http::DEFAULT_HTTPS_PORT
            );
            $port = (in_array($port, $defaultPorts)) ? '' : ':' . $port;
        }
        $uri = $request->getServer('REQUEST_URI');
        
        $currentUrlKey = Mage::helper("mageinn_regions")->getRegionKey();
        if(empty($currentUrlKey)) {
            $uri = "/" . $urlKey . $uri;
        } else {
            $uri = preg_replace("!^/" . $currentUrlKey . "!i", "/" . $urlKey, $uri);
        }
        
        return $request->getScheme() . '://' . $request->getHttpHost() . $port . $uri;
    }
}
