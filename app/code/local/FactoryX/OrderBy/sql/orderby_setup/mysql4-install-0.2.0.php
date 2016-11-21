<?php
/**
add created_by field to sales_flat_order
*/
$installer = $this;
$installer->startSetup();
$setup = Mage::getResourceModel('sales/setup','sales_setup');
$setup->addAttribute('order', 'created_by', array('type'=>'varchar'));
Mage::helper('orderby')->log('sql ran all good');
$installer->endSetup();