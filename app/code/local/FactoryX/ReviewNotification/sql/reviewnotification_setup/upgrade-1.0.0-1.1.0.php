<?php

try
{

        $installer = $this;

        $installer->startSetup();

        $installer->run("

                ALTER TABLE  {$this->getTable('sales_flat_order')}
                ADD  `easyreview_date` DATE NULL;

        ");

        $installer->endSetup();

}
catch (Exception $e)
{
        Mage::log($e->getMessage());
}
