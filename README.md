# Regions for Magento® 1

## Description

This Magento extension allows you to create and manage regions, cities as well as tube stations and assign products to any region, city and/or tube station. Customers can then filter out your products by selecting a specific region/city/tube station from a dropdown on the frontend.

## Installation Instructions

1. Enable Module: “System -> Configuration -> Mageinn -> Regions -> Enable = Yes“

2. Enable JQuery for Admin:
   “System -> Configuration -> Mageinn -> General -> Load jQuery in admin = Yes“

3. Add 
```
<?php echo $this->getChildHtml(‘region_switch’); ?> 
```
to “app\design\frontend\package\theme\template\page\html\header.phtml“

## Compatibility
This extension is compatible with Magento Community Edition 1.4 to 1.9 & Enterprise Edition 1.9 to 1.14.