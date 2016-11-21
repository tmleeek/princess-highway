<?php

/**
 * Class FactoryX_MenuImage_Model_Block
 */
class FactoryX_MenuImage_Model_Block extends Mage_Core_Model_Abstract
{
	/**
	 * Constructor for the menuimage block model
	 */
    protected function _construct()
    {
        $this->_init('menuimage/block', 'block_id');
    }
}