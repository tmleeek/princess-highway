<?php
/**
 * Atwix
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
 * @category    Atwix
 * @package     Atwix_UniversalAnalytics
 * @author      Atwix Core Team
 * @copyright   Copyright (c) 2012 Atwix (http://www.atwix.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Atwix_UniversalAnalytics_Block_Ua extends Mage_Core_Block_Template
{
    /**
     * Render information about specified orders and their items
     *
     * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/ecommerce
     * @return string
     */
    protected function _getOrdersTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));
        $result = array();

        /* Require eccomerce script */

        $result[] = "__gaTracker('require', 'ecommerce', 'ecommerce.js');";

        foreach ($collection as $order) {
            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }

            /* Add order information */

            $result[] = sprintf(
                "__gaTracker('ecommerce:addTransaction', {
                'id': '%s',
                'affiliation': '%s',
                'revenue': '%s',
                'shipping': '%s',
                'tax': '%s'
            });",
                $order->getIncrementId(),
                $this->jsQuoteEscape(Mage::app()->getStore()->getFrontendName()),
                $order->getBaseGrandTotal(),
                $order->getBaseShippingAmount(),
                $order->getBaseTaxAmount()
            );

            /* Add pusrchased items info */
			
			// Get the product
			$product = Mage::getModel('catalog/product')->load($item->getProductId());

			// Generate list of product categories
			$categoryCollection = $product->getCategoryIds();
			$categoryList = "";
			
			foreach ($categoryCollection as $categoryId)
			{
				$category = Mage::getModel('catalog/category')->load($categoryId);
				$categoryList .= $category->getName() . "|";
			}
			
			$categoryList = rtrim($categoryList,"|");

            foreach ($order->getAllVisibleItems() as $item) {
                $result[] = sprintf(
                    "__gaTracker('ecommerce:addItem', {
                    'id': '%s',
                    'name': '%s',
                    'sku': '%s',
                    'category': '%s',
                    'price': '%s',
                    'quantity': '%s'
                });",
                    $order->getIncrementId(),
                    $this->jsQuoteEscape($item->getName()),
                    $this->jsQuoteEscape($item->getSku()),
                    $categoryList,
                    $item->getBasePrice(),
                    $item->getQtyOrdered()
                );
            }
            $result[] = "__gaTracker('ecommerce:send');";
        }
        return implode("\n", $result);
    }


    /**
     * Render Universal GA tracking scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('atwix_universalanalytics')->isUniversalGoogleAnalyticsAvailable()) {
            return '';
        }

        return parent::_toHtml();
    }
}