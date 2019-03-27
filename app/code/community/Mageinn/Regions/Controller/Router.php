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
 * Router
 *
 * @category   Mageinn
 * @package    Mageinn_Regions
 * @author     Mageinn
 */
class Mageinn_Regions_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard {
    
    /**
    * Match the request
    *
    * @param Zend_Controller_Request_Http $request
    * @return boolean
    */
    public function match(Zend_Controller_Request_Http $request)
    {
        $request->isStraight(false);
        $path = trim($request->getPathInfo(), '/');

        if ($path) {
            $p = explode('/', $path);
            
            // try to match the region
            $region = Mage::getModel('mageinn_regions/region')->load($p[0],"url_key");
            if($region->getId()) {
                // Set Region here
                Mage::register('region_filter', $region);
                //Mage::getSingleton('core/session')->setRegionFilter($region);
                
                $pathInfo = preg_replace("!^/" . $p[0] . "!", "/", $request->getPathInfo());
                $pathInfo = str_replace("//", "/", $pathInfo);
                $request->setPathInfo($pathInfo);
            }
        }
        
        return Mage::getModel('core/url_rewrite')->rewrite();
    }
}
