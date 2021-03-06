<?php

/**
 * Class FactoryX_Lookbook_Model_Mysql4_Lookbook
 */
class FactoryX_Lookbook_Model_Mysql4_Lookbook extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('lookbook/lookbook', 'lookbook_id');
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        $condition = $this->_getWriteAdapter()->quoteInto('lookbook_id = ?', $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('lookbook/store'), $condition);

        if (!$object->getData('stores')) {
            $storeArray = array();
            $storeArray['lookbook_id'] = $object->getId();
            $storeArray['store_id'] = '0';
            $this->_getWriteAdapter()->insert($this->getTable('lookbook/store'), $storeArray);
        } else {
            foreach ((array) $object->getData('stores') as $store) {
                $storeArray = array();
                $storeArray['lookbook_id'] = $object->getId();
                $storeArray['store_id'] = $store;
                $this->_getWriteAdapter()->insert($this->getTable('lookbook/store'), $storeArray);
            }
        }
        return parent::_afterSave($object);
    }

    /**
     *
     * @param Mage_Core_Model_Abstract $object
     * @return \Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('lookbook/store'))
                ->where('lookbook_id = ?', $object->getId());

        if ($data = $this->_getReadAdapter()->query($select)) {
            $storesArray = array();
			while ($row = $data->fetch())
			{
				$storesArray[] = $row['store_id'];
			}
            $object->setData('store_id', $storesArray);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object) {

        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $select->join(array('cps' => $this->getTable('lookbook/store')), $this->getMainTable() . '.lookbook_id = `cps`.lookbook_id')
                    ->where('`cps`.store_id in (0, ?) ', $object->getStoreId())
                    ->order('store_id DESC')
                    ->limit(1);
        }
        return $select;
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return \Mage_Core_Model_Resource_Db_Abstract|void
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object) {

        // Cleanup stats on lookbook delete
        $adapter = $this->_getReadAdapter();
		// 1. Delete rewrite rules
		try
		{
			// If multistore
			if (!Mage::app()->isSingleStoreMode())
			{
				$select = $this->_getReadAdapter()->select()
						->from($this->getTable('lookbook/store'))
						->where('lookbook_id = ?', $object->getId());

				if ($data = $this->_getReadAdapter()->query($select)) {
					while ($row = $data->fetch())
					{
						$affectedStoreId = $row['store_id'];
						$idPath = $object->getIdentifier()."_".$affectedStoreId;
						$urlRewrite = Mage::getModel('core/url_rewrite')->loadByIdPath($idPath);
						$urlRewrite->delete();
					}
				}
			}
			// If single store
			else
			{
				$urlRewrite = Mage::getModel('core/url_rewrite')->loadByIdPath($object->getIdentifier());
				$urlRewrite->delete();
			}
		}
		catch (Exception $e)
		{
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		// 2. Delete lookbook/store
        $adapter->delete($this->getTable('lookbook/store'), 'lookbook_id=' . $object->getId());
    }

    /**
     * Save attribute to model
     *
     * @param $model - the model
     * @param $attributes - array of attributes to get and save from model
     * @return $this
     * @throws Exception
     */
    public function saveAttribute($model, $attributes)
    {
        try {
            $adapter = $this->_getWriteAdapter();
            $adapter->beginTransaction();
            $condition = $this->_getWriteAdapter()->quoteInto($this->getIdFieldName() . '=?', $model->getId());
            $data      = array();
            foreach ($attributes as $attribute) {
                $value = $model->getData($attribute);
                if (isset($value)) {
                    $data[$attribute] = $value;
                }
            }
            if (!empty($data)) {
                $this->_getWriteAdapter()->update($this->getMainTable(), $data, $condition);
            }
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }
        return $this;
    }
}