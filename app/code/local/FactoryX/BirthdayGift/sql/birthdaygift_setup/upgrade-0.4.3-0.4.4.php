<?php

$installer = $this;
$installer->startSetup();

Mage::helper('birthdaygift')->log(sprintf("%s", __FILE__));

$moveConfig = array(
    'bdayconfig/options/prefix'             => 'bdayconfig/coupon/prefix',
    'bdayconfig/options/type'               => 'bdayconfig/coupon/type',
    'bdayconfig/options/value'              => 'bdayconfig/coupon/value',
    'bdayconfig/options/customer_groups'    => 'bdayconfig/coupon/customer_groups',
    'bdayconfig/options/uses_coupon'        => 'bdayconfig/coupon/uses_coupon',
    'bdayconfig/options/uses_customer'      => 'bdayconfig/coupon/uses_customer',
    'bdayconfig/options/valid'              => 'bdayconfig/coupon/valid',
    'bdayconfig/options/campaignmonitor'    => 'bdayconfig/campaignmonitor/enabled',
    'bdayconfig/options/segmentID'          => 'bdayconfig/campaignmonitor/segmentID'
);

$resource = Mage::getSingleton('core/resource');
$dbConn = $resource->getConnection('core_write');

foreach($moveConfig as $oldConfig => $newConfig) {
    Mage::helper('birthdaygift')->log(sprintf("move config '%s' -> '%s'", $oldConfig, $newConfig));
    // check if exists
    $sql = sprintf("select scope, scope_id, value from core_config_data where path = '%s'", $oldConfig);
    Mage::helper('birthdaygift')->log(sprintf("sql: %s", $sql));
    $configToMove = $dbConn->fetchAll($sql);
    if (count($configToMove)) {
        foreach($configToMove as $config) {
            $sql = sprintf("replace into core_config_data (scope, scope_id, path, value) values ('%s',%d,'%s','%s')",
                $config['scope'], $config['scope_id'], $newConfig, $config['value']
            );
            Mage::helper('birthdaygift')->log(sprintf("sql: %s", $sql));
            $dbConn->query($sql);

            $sql = sprintf("delete from core_config_data where scope = '%s' AND path = '%s'", $config['scope'], $oldConfig);
            Mage::helper('birthdaygift')->log(sprintf("sql: %s", $sql));
            $dbConn->query($sql);
        }
    }
}

$installer->endSetup();