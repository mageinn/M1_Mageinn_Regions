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
 * Default helper
 *
 * @category   Mageinn
 * @package    Mageinn_Regions
 * @author     Mageinn
 */
class Mageinn_Regions_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_MAGEINN_REGIONS_ENABLED = 'mageinn_regions/general/enabled';

    /**
     * Checks if the module is enabled
     * 
     * @return bool 
     */
    public function isEnabled() 
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_MAGEINN_REGIONS_ENABLED);
    }
    
    
    /**
     * Get Current Region Url Key
     * 
     * @return string 
     */
    public function getRegionKey() 
    {
        return $this->getRegionData('url_key');
    }
    
    /**
     * Get Current Region Data
     * 
     * @param string $field
     * @return string 
     */
    public function getRegionData($field) 
    {
        $region = Mage::registry('region_filter');
        $data = "";
        if(is_object($region) && $region->getId()) {
            $data = $region->getData($field);
        }
        
        return $data;
    }
}