<?php

namespace SIS\Threshold\Observer;
 
use Magento\Framework\Event\ObserverInterface; 
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart\CartInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject;
use Magento\Checkout\Model\Session;
 
class BindCustomprice implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
 
 
  	protected $_scopeConfig;
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_objectManager = $objectManager;
		$this->_scopeConfig = $scopeConfig;
    }
 
    /**
     * customer register event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	echo "Asdsad";exit;
    	//return true;    	    			  
        $item=$observer->getEvent()->getData('quote_item');
					
        $product=$observer->getEvent()->getData('product');
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$stockRegistry = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
		$stockItem=$stockRegistry->getStockItem($product->getId());
		 		 
		$qty = $stockItem->getData('qty');
		$IsInStock = $stockItem->getData('is_in_stock'); 
		//&& $qty<=0
		if(!$IsInStock )
		{
			/*$pre_order_type=$this->_scopeConfig->getValue('mars_marketplace/preorder/pre_order_type');		
			$price=$product->getPrice();
			
			if($pre_order_type==1)
			{										
				$percentage=$this->_scopeConfig->getValue('mars_marketplace/preorder/payment_percentage');			
				$new_price=($price*$percentage)/100;												
				$product->setSpecialPrice($new_price);
			}
				
	        $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
	        // Load the custom price
	        //$price = $product->getPrice()+11; // 10 is custom price. It will increase in product price.
	        // Set the custom price
	        $item->setCustomPrice($new_price);
	        $item->setOriginalCustomPrice($new_price);
	        // Enable super mode on the product.
	        $item->getProduct()->setIsSuperMode(true);*/
		}
		else 
		{
			/*$new_price=11;
			$item->setCustomPrice($new_price);
	        $item->setOriginalCustomPrice($new_price);*/
		}
		
		
		
		
    }
}