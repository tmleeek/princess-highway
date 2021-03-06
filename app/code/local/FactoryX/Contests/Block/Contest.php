<?php

/**
 * Class FactoryX_Contests_Block_Contest
 */
class FactoryX_Contests_Block_Contest extends Mage_Core_Block_Template
{
	public $_contestId;
	
	/**
	 *	Retrieve the current contest for the frontend
	 */
	public function getCurrentContest()     
    {
		try
		{
			if(!$this->_contestId) 
			{
				$this->_contestId = $this->getRequest()->getParam('id');
			}
			
			if(!$this->_contestId) 
			{
				$this->_contestId = $this->getID();
			}		
			
			// Load contest based on the given id
			$currentContest = Mage::getModel('contests/contest')->load($this->_contestId);
			
			// Ensure the contest is viewable in the store
			if (!Mage::app()->isSingleStoreMode()) 
			{
				if ($currentContest->isStoreViewable()) 
					return $currentContest;
				else 
					throw new Exception ('This contest is not available with this store');
			}
			else
			{
				return $currentContest;
			}
		}
		catch (Exception $e)
		{
			Mage::helper('contests')->log($this->__('Exception caught in %s under % with message: %s', __FILE__, __FUNCTION__, $e->getMessage()));
			Mage::getSingleton('customer/session')->addError($this->__('There was a problem loading the contest'));
			$this->_redirectReferer();
			return;
		}
    }

    protected function _construct()
    {
        $this->addData(array(
            'cache_lifetime' => 86400,
            'cache_tags'     => array(FactoryX_Contests_Model_Contest::CACHE_TAG),
            'cache_key'      => $this->makeCacheKey(get_class($this))
        ));
    }

	/**
	 * @return mixed
     */
	public function getCacheKey()
    {
        if (!$this->hasData('cache_key')) {
            $cacheKey = $this->makeCacheKey(get_class($this));
            $this->setCacheKey($cacheKey);
        }
        return $this->getData('cache_key');
    }

	/**
	 * @param $className
	 * @return string
     */
	protected function makeCacheKey($className)
    {
        $contestId = $this->getCurrentContest()->getContestId();
        $cacheKey  = sprintf("%s_%d_%s_%s", $className, Mage::app()->getStore()->getId(), Mage::getSingleton('core/design_package')->getPackageName(), $contestId);
        return $cacheKey;
    }

}