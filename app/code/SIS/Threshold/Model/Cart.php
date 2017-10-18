<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SIS\Threshold\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use SIS\Threshold\Model\Cart\CartInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject;
use Magento\Checkout\Model\Session;

use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Shopping cart model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated 
 */
class Cart extends \Magento\Framework\DataObject implements CartInterface
{
    /**
     * Shopping cart items summary quantity(s)
     *
     * @var int|null
     */
    protected $_summaryQty;

    /**
     * List of product ids in shopping cart
     *
     * @var int[]|null
     */
    protected $_productIds;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Checkout\Model\ResourceModel\Cart
     */
    protected $_resourceCart;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockState;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Checkout\Model\Cart\RequestInfoFilterInterface
     */
    private $requestInfoFilter;
	  /**
     * @var CartManagementInterface
     */
    protected $quoteManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;
	
	protected $quoteFactory;
	
	/**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $currency;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\ResourceModel\Cart $resourceCart
     * @param Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\ResourceModel\Cart $resourceCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        CartManagementInterface $quoteManagement,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Framework\Locale\CurrencyInterface $currency,
        CartRepositoryInterface $cartRepository,
        array $data = []
    ) {
    	
		
		
        $this->_eventManager = $eventManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_resourceCart = $resourceCart;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->stockRegistry = $stockRegistry;
        $this->stockState = $stockState;
        $this->quoteRepository = $quoteRepository;
		$this->quoteFactory = $quoteFactory;
		$this->currency = $currency;
		
        parent::__construct($data);
		
			
        $this->productRepository = $productRepository;
		$this->quoteManagement = $quoteManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
		
		
    }
	
	
	 public function beforeAddProduct(
            \Magento\Checkout\Model\Cart $subject,
            $productInfo,
            $requestInfo = null
        ) {
        	
		    $product = $this->_getProduct($productInfo);				
			$pre_order_type=$this->_scopeConfig->getValue('mars_marketplace/preorder/pre_order_type');		
			$price=$product->getPrice();
			
			
			
			if($pre_order_type==1)
			{										
				$percentage=$this->_scopeConfig->getValue('mars_marketplace/preorder/payment_percentage');			
				$new_price=($price*$percentage)/100;												
				$product->setSpecialPrice($new_price);				
				//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();    				
				/*$stockRegistry = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
				$stockItem=$stockRegistry->getStockItem($product->getId());*/
				
				/*$product->setQuantityAndStockStatus(['qty' => 100, 'is_in_stock' => 1]);											
				$stockItem->setData('manage_stock', 1);					
				$stockItem->setData('is_in_stock', 1);*/
									
				//$stockItem->save();		
				/*$stockItem = $product->getStockItem();
				*/
			}
			/*else {		
				
				$product->setSpecialPrice($new_price);
			}*/
							
            //$requestInfo['qty'] = 10; // increasing quantity to 10
            return array($productInfo, $requestInfo);
    }
	
    /**
     * Get shopping cart resource model
     *
     * @return \Magento\Checkout\Model\ResourceModel\Cart
     * @codeCoverageIgnore
     */
    protected function _getResource()
    {
        return $this->_resourceCart;
    }

    /**
     * Retrieve checkout session model
     *
     * @return Session
     * @codeCoverageIgnore
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Retrieve customer session model
     *
     * @return \Magento\Customer\Model\Session
     * @codeCoverageIgnore
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * List of shopping cart items
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection|array
     */
    public function getItems()
    {
        if (!$this->getQuote()->getId()) {
            return [];
        }
        return $this->getQuote()->getItemsCollection();
    }

    /**
     * Retrieve array of cart product ids
     *
     * @return array
     */
    public function getQuoteProductIds()
    {
        $products = $this->getData('product_ids');
        if ($products === null) {
            $products = [];
            foreach ($this->getQuote()->getAllItems() as $item) {
                $products[$item->getProductId()] = $item->getProductId();
            }
            $this->setData('product_ids', $products);
        }
        return $products;
    }

    /**
     * Get quote object associated with cart. By default it is current customer session quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->hasData('quote')) {
            $this->setData('quote', $this->_checkoutSession->getQuote());
        }
        return $this->_getData('quote');
    }
	/*
	*/
	/*public function getCartQuote($cartData)
	{		
		$cart_id=array_keys($cartData);
		return $this->quoteFactory->create()->load($cart_id[0]);
			
	}*/
	
    /**
     * Set quote object associated with the cart
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     * @codeCoverageIgnore
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {    	
        $this->setData('quote', $quote);
        return $this;
    }

    /**
     * Reinitialize cart quote state
     *
     * @return $this
     */
    protected function reinitializeState()
    {
        $quote = $this->getQuote()->setCheckoutMethod('');
        $this->_checkoutSession->setCartWasUpdated(true);
        // TODO: Move this logic to Multishipping module as plug-in.
        // reset for multiple address checkout
        if ($this->_checkoutSession->getCheckoutState() !== Session::CHECKOUT_STATE_BEGIN
            && $this->_checkoutSession->getCheckoutState() !== null) {
            $quote->removeAllAddresses()->removePayment();
            $this->_checkoutSession->resetCheckout();
        }
        return $this;
    }

    /**
     * Convert order item to quote item
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param true|null $qtyFlag if is null set product qty like in order
     * @return $this
     */
    public function addOrderItem($orderItem, $qtyFlag = null)
    {
        /* @var $orderItem \Magento\Sales\Model\Order\Item */
        if ($orderItem->getParentItem() === null) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                /**
                 * We need to reload product in this place, because products
                 * with the same id may have different sets of order attributes.
                 */
                $product = $this->productRepository->getById($orderItem->getProductId(), false, $storeId, true);
            } catch (NoSuchEntityException $e) {
                return $this;
            }
            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $info = new \Magento\Framework\DataObject($info);
            if ($qtyFlag === null) {
                $info->setQty($orderItem->getQtyOrdered());
            } else {
                $info->setQty(1);
            }

            $this->addProduct($product, $info);
        }
        return $this;
    }

    /**
     * Get product object based on requested product information
     *
     * @param   Product|int|string $productInfo
     * @return  Product
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getProduct($productInfo)
    {
        $product = null;
        if ($productInfo instanceof Product) {
            $product = $productInfo;
            if (!$product->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the product.'));
            }
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productInfo, false, $storeId);
            } catch (NoSuchEntityException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the product.'), $e);
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the product.'));
        }
        $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
        if (!is_array($product->getWebsiteIds()) || !in_array($currentWebsiteId, $product->getWebsiteIds())) {
            throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t find the product.'));
        }
        return $product;
    }

    /**
     * Get request for product add to cart procedure
     *
     * @param   \Magento\Framework\DataObject|int|array $requestInfo
     * @return  \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof \Magento\Framework\DataObject) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new \Magento\Framework\DataObject(['qty' => $requestInfo]);
        } elseif (is_array($requestInfo)) {
            $request = new \Magento\Framework\DataObject($requestInfo);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }
        $this->getRequestInfoFilter()->filter($request);

        return $request;
    }

    /**
     * Add product to shopping cart (quote)
     *
     * @param int|Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */       
    
	  public function addProduct($productInfo, $requestInfo = null)
    {
        $product = $this->_getProduct($productInfo);
        $request = $this->_getProductRequest($requestInfo);
        $productId = $product->getId();

        
		$seller_id=$product->getSellerId();
		//$this->_checkoutSession->setCartIds(array());			
		$check = $this->_checkoutSession->getCartIds();						
		//print_r($check);exit;	
		if($check===null)
		{
			//echo "NO1";exit;
			//$quoteIdMask = $this->quoteIdMaskFactory->create();
	        $cartId = $this->quoteManagement->createEmptyCart();
			$check[$seller_id]=$cartId;	
			$qt_id=$cartId;
			$this->_checkoutSession->setCartIds($check);	
			//$this->setData('cartids',$check); 			
		}		
		elseif(!array_key_exists($seller_id, $check))
		{
			//echo "NO2";exit;	
			$quoteIdMask = $this->quoteIdMaskFactory->create();
	        $cartId = $this->quoteManagement->createEmptyCart();
			$check[$seller_id]=$cartId;	
			$qt_id=$cartId;
			//$this->setData('cartids',$check);
			$this->_checkoutSession->setCartIds($check);	
	        //$quoteIdMask->setQuoteId($cartId)->save();
		}
		else {
			
			//echo "YES";exit;
			$qt_id=$check[$seller_id];
		}
		
		/*echo "<pre>";
		print_r($check);
		echo "</pre>";*/
		
		//$qt_id=93;			
		if ($productId) {
            $stockItem = $this->stockRegistry->getStockItem($productId, $product->getStore()->getWebsiteId());
            $minimumQty = $stockItem->getMinSaleQty();
            //If product was not found in cart and there is set minimal qty for it
            if ($minimumQty && $minimumQty > 0 && !$request->getQty() && !$this->getQuote()->hasProductId($productId)) {
                $request->setQty($minimumQty);
            }			
        }
		
		//echo $this->getQuote()->getId();
		
        if ($productId) {
            try {
                //$result = $this->getQuote()->addProduct($product, $request);										
				//$quoteIdMask->addProduct($product, $request);
				//$check=$this->getData('cartids');
				//$check[]=$cartId;
				//$this->setData('cartids', array($cartId));											
				$quote = $this->quoteFactory->create()->load($qt_id);
				$this->setData('quote', $quote);				
				$result = $quote->addProduct($product, $request);
				
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_checkoutSession->setUseNotice(false);
                $result = $e->getMessage();
            }
            /**
             * String we can get if prepare process has error
             */
            if (is_string($result)) {
                if ($product->hasOptionsValidationFail()) {
                    $redirectUrl = $product->getUrlModel()->getUrl(
                        $product,
                        ['_query' => ['startcustomization' => 1]]
                    );
                } else {
                    $redirectUrl = $product->getProductUrl();
                }
                $this->_checkoutSession->setRedirectUrl($redirectUrl);
                if ($this->_checkoutSession->getUseNotice() === null) {
                    $this->_checkoutSession->setUseNotice(true);
                }
                throw new \Magento\Framework\Exception\LocalizedException(__($result));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
        }

        $this->_eventManager->dispatch(
            'checkout_cart_product_add_after',
            ['quote_item' => $result, 'product' => $product]
        );
        $this->_checkoutSession->setLastAddedProductId($productId);
        return $this;
    }
    public function addProduct_($productInfo, $requestInfo = null)
    {
    	
					
        //$product = $this->_getProduct($productInfo);				
		//$pre_order_type=$this->_scopeConfig->getValue('mars_marketplace/preorder/pre_order_type');		
		//$price=$product->getPrice();
		
		/*if($pre_order_type==1)
		{										
			$percentage=$this->_scopeConfig->getValue('mars_marketplace/preorder/payment_percentage');			
			$new_price=($price*$percentage)/100;		
			$product->setSpecialPrice($new_price);
			
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();    				
			$stockRegistry = $objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
			$stockItem=$stockRegistry->getStockItem($product->getId());
			
			$product->setQuantityAndStockStatus(['qty' => 100, 'is_in_stock' => 1]);
										
			$stockItem->setData('manage_stock', 1);
					
			$stockItem->setData('is_in_stock', 1);
			
			$product->setSpecialPrice($new_price);	
			//$stockItem->save();		
			//$stockItem = $product->getStockItem();
			
		}*/
		
				    	
        $request = $this->_getProductRequest($requestInfo);
        $productId = $product->getId();
		
			
        if ($productId) {
            $stockItem = $this->stockRegistry->getStockItem($productId, $product->getStore()->getWebsiteId());
			$stockItem->setData('is_in_stock',1); 
            $minimumQty = $stockItem->getMinSaleQty();
            //If product was not found in cart and there is set minimal qty for it
            if ($minimumQty
                && $minimumQty > 0
                && !$request->getQty()
                && !$this->getQuote()->hasProductId($productId)
            ) {
                $request->setQty($minimumQty);
            }
			
		
        }
				
        if ($productId) {
        	
			/*$quoteIdMask = $this->quoteIdMaskFactory->create();
	        $cartId = $this->quoteManagement->createEmptyCart();
	        $quoteIdMask->setQuoteId($cartId)->save();*/
			
            try {
                $result = $this->getQuote()->addProduct($product, $request);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_checkoutSession->setUseNotice(false);
                $result = $e->getMessage();
            }							
            /**
             * String we can get if prepare process has error
             */
            if (is_string($result)) {
                if ($product->hasOptionsValidationFail()) {
                    $redirectUrl = $product->getUrlModel()->getUrl(
                        $product,
                        ['_query' => ['startcustomization' => 1]]
                    );
                } else {
                    $redirectUrl = $product->getProductUrl();
                }
                $this->_checkoutSession->setRedirectUrl($redirectUrl);
                if ($this->_checkoutSession->getUseNotice() === null) {
                    $this->_checkoutSession->setUseNotice(true);
                }
                throw new \Magento\Framework\Exception\LocalizedException(__($result));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
        }

        $this->_eventManager->dispatch(
            'checkout_cart_product_add_after',
            ['quote_item' => $result, 'product' => $product]
        );
        $this->_checkoutSession->setLastAddedProductId($productId);
        return $this;
    }

    /**
     * Adding products to cart by ids
     *
     * @param  int[] $productIds
     * @return $this
     */
    public function addProductsByIds($productIds)
    {
        $allAvailable = true;
        $allAdded = true;

        if (!empty($productIds)) {
            foreach ($productIds as $productId) {
                $productId = (int)$productId;
                if (!$productId) {
                    continue;
                }
                $product = $this->_getProduct($productId);
                if ($product->getId() && $product->isVisibleInCatalog()) {
                    try {
                        $this->getQuote()->addProduct($product);
                    } catch (\Exception $e) {
                        $allAdded = false;
                    }
                } else {
                    $allAvailable = false;
                }
            }

            if (!$allAvailable) {
                $this->messageManager->addError(__("We don't have some of the products you want."));
            }
            if (!$allAdded) {
                $this->messageManager->addError(__("We don't have as many of some products as you want."));
            }
        }
        return $this;
    }

    /**
     * Returns suggested quantities for items.
     * Can be used to automatically fix user entered quantities before updating cart
     * so that cart contains valid qty values
     *
     * The $data is an array of ($quoteItemId => (item info array with 'qty' key), ...)
     *
     * @param   array $data
     * @return  array
     */
    public function suggestItemsQty($data)
    {
        foreach ($data as $itemId => $itemInfo) {
            if (!isset($itemInfo['qty'])) {
                continue;
            }
            $qty = (float)$itemInfo['qty'];
            if ($qty <= 0) {
                continue;
            }

            $quoteItem = $this->getQuote()->getItemById($itemId);
            if (!$quoteItem) {
                continue;
            }

            $product = $quoteItem->getProduct();
            if (!$product) {
                continue;
            }

            $data[$itemId]['before_suggest_qty'] = $qty;
            $data[$itemId]['qty'] = $this->stockState->suggestQty(
                $product->getId(),
                $qty,
                $product->getStore()->getWebsiteId()
            );
        }
        return $data;
    }
	/**
     * Update cart items information
     *
     * @param  array $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getCartQuote($cart_id)
    {    	
		return $quote = $this->quoteRepository->get($cart_id);    			   
    }
    public function updateCartItems($cart_id,$data)
    {
    	
        $infoDataObject = new \Magento\Framework\DataObject($data);
        $this->_eventManager->dispatch(
            'checkout_cart_update_items_before',
            ['cart' => $this, 'info' => $infoDataObject]
        );
        
		$this->setData('quote', $this->getCartQuote($cart_id));
		
        $qtyRecalculatedFlag = false;
        foreach ($data as $itemId => $itemInfo) {
        	
			
            $item = $this->getCartQuote($cart_id)->getItemById($itemId);
			
						
            if (!$item) {
                continue;
            }

            if (!empty($itemInfo['remove']) || isset($itemInfo['qty']) && $itemInfo['qty'] == '0') {
                $this->removeItem($itemId);
                continue;
            }

            $qty = isset($itemInfo['qty']) ? (double)$itemInfo['qty'] : false;
            if ($qty > 0) {
            	
				
				
                $item->setQty($qty);

                if ($item->getHasError()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
                }

                if (isset($itemInfo['before_suggest_qty']) && $itemInfo['before_suggest_qty'] != $qty) {
                    $qtyRecalculatedFlag = true;
                    $this->messageManager->addNotice(
                        __('Quantity was recalculated from %1 to %2', $itemInfo['before_suggest_qty'], $qty),
                        'quote_item' . $item->getId()
                    );
                }
            }
        }
			
	
		
        if ($qtyRecalculatedFlag) {
            $this->messageManager->addNotice(
                __('We adjusted product quantities to fit the required increments.')
            );
        }

        $this->_eventManager->dispatch(
            'checkout_cart_update_items_after',
            ['cart' => $this, 'info' => $infoDataObject]
        );
		
		
		
        return $this;
    }
    /**
     * Update cart items information
     *
     * @param  array $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function updateItems($data)
    {
        $infoDataObject = new \Magento\Framework\DataObject($data);
        $this->_eventManager->dispatch(
            'checkout_cart_update_items_before',
            ['cart' => $this, 'info' => $infoDataObject]
        );

        $qtyRecalculatedFlag = false;
        foreach ($data as $itemId => $itemInfo) {
            $item = $this->getQuote()->getItemById($itemId);
            if (!$item) {
                continue;
            }

            if (!empty($itemInfo['remove']) || isset($itemInfo['qty']) && $itemInfo['qty'] == '0') {
                $this->removeItem($itemId);
                continue;
            }

            $qty = isset($itemInfo['qty']) ? (double)$itemInfo['qty'] : false;
            if ($qty > 0) {
                $item->setQty($qty);

                if ($item->getHasError()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
                }

                if (isset($itemInfo['before_suggest_qty']) && $itemInfo['before_suggest_qty'] != $qty) {
                    $qtyRecalculatedFlag = true;
                    $this->messageManager->addNotice(
                        __('Quantity was recalculated from %1 to %2', $itemInfo['before_suggest_qty'], $qty),
                        'quote_item' . $item->getId()
                    );
                }
            }
        }

        if ($qtyRecalculatedFlag) {
            $this->messageManager->addNotice(
                __('We adjusted product quantities to fit the required increments.')
            );
        }

        $this->_eventManager->dispatch(
            'checkout_cart_update_items_after',
            ['cart' => $this, 'info' => $infoDataObject]
        );

        return $this;
    }

    /**
     * Remove item from cart
     *
     * @param  int $itemId
     * @return $this
     * @codeCoverageIgnore
     */
    public function removeItem($itemId)
    {
        $this->getQuote()->removeItem($itemId);
        return $this;
    }
	public function removeCartItem($itemId,$quote_id)
    {
    	$quote = $this->quoteFactory->create()->load($quote_id);
        $quote->removeItem($itemId);
		$quote->save();
        return $this;
    }
	
	public function getCarts($id)
	{		
		$cartIds=$this->_checkoutSession->getCartIds();				
		foreach($cartIds as $cart_id)
		{
			$quote = $this->quoteFactory->create()->load($cart_id);
			$items=$quote->getItemsCollection();
			foreach($items as $item)
			{
				if($id==$item->getId())
				{
					return $quote->getId();														
				}					
			}
				
		}
				
	}
    /**
     * Save cart
     *
     * @return $this
     */
    public function save()
    {
    	
        $this->_eventManager->dispatch('checkout_cart_save_before', ['cart' => $this]);

        $this->getQuote()->getBillingAddress();
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->getQuote()->collectTotals();
        $this->quoteRepository->save($this->getQuote());
        $this->_checkoutSession->setQuoteId($this->getQuote()->getId());
        /**
         * Cart save usually called after changes with cart items.
         */
        $this->_eventManager->dispatch('checkout_cart_save_after', ['cart' => $this]);
        $this->reinitializeState();
        return $this;
    }
	public function saveCart($quote_id='')
    {    
        $this->_eventManager->dispatch('checkout_cart_save_before', ['cart' => $this]);		
        $this->getQuote()->getBillingAddress();
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->getQuote()->collectTotals();
		if($quote_id)
		{			
			$this->quoteRepository->save($this->quoteFactory->create()->load($quote_id));			
			
		}		
		else {
			$this->quoteRepository->save($this->getQuote());
		}
        $this->_checkoutSession->setQuoteId($this->getQuote()->getId());
        /**
         * Cart save usually called after changes with cart items.
         */
        $this->_eventManager->dispatch('checkout_cart_save_after', ['cart' => $this]);
        $this->reinitializeState();
        return $this;
    }
    /**
     * Save cart (implement interface method)
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function saveQuote()
    {
        $this->save();
    }

    /**
     * Mark all quote items as deleted (empty shopping cart)
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function truncate($cart_id)
    {    	
		$quote = $this->quoteFactory->create()->load($cart_id);
		$quote->removeAllItems();
		$quote->save();
				       
        return $this;
    }

    /**
     * @return int[]
     */
    public function getProductIds()
    {
        if (null === $this->_productIds) {
            $this->_productIds = [];
            if ($this->getSummaryQty() > 0) {
                foreach ($this->getQuote()->getAllItems() as $item) {
                    $this->_productIds[] = $item->getProductId();
                }
            }
            $this->_productIds = array_unique($this->_productIds);
        }
        return $this->_productIds;
    }

    /**
     * Get shopping cart items summary (includes config settings)
     *
     * @return int|float
     */
    public function getSummaryQty()
    {
    	
        $quoteId = $this->_checkoutSession->getQuoteId();

        //If there is no quote id in session trying to load quote
        //and get new quote id. This is done for cases when quote was created
        //not by customer (from backend for example).
        if (!$quoteId && $this->_customerSession->isLoggedIn()) {
            $this->_checkoutSession->getQuote();
            $quoteId = $this->_checkoutSession->getQuoteId();
        }

        if ($quoteId && $this->_summaryQty === null) {
            $useQty = $this->_scopeConfig->getValue(
                'checkout/cart_link/use_qty',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $this->_summaryQty = $useQty ? $this->getItemsQty() : $this->getItemsCount();
        }
        return $this->_summaryQty;
    }

    /**
     * Get shopping cart items count
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getItemsCount()
    {
    	$no_of_items=0;
		$cartIds=$this->_checkoutSession->getCartIds();				
		foreach($cartIds as $cart_id)
		{
			$quote = $this->quoteFactory->create()->load($cart_id);
			$items=$quote->getItemsCollection();
			$no_of_items+=count($items);			
				
		}		
        return $no_of_items * 1;
    }

    /**
     * Get shopping cart summary qty
     *
     * @return int|float
     * @codeCoverageIgnore
     */
    public function getItemsQty()
    {
    	
        return $this->getQuote()->getItemsQty() * 1;
    }

    /**
     * Update item in shopping cart (quote)
     * $requestInfo - either qty (int) or buyRequest in form of array or \Magento\Framework\DataObject
     * $updatingParams - information on how to perform update, passed to Quote->updateItem() method
     *
     * @param int $itemId
     * @param int|array|\Magento\Framework\DataObject $requestInfo
     * @param null|array|\Magento\Framework\DataObject $updatingParams
     * @return \Magento\Quote\Model\Quote\Item|string
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @see \Magento\Quote\Model\Quote::updateItem()
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function updateItem($itemId, $requestInfo = null, $updatingParams = null)
    {
        try {
            $item = $this->getQuote()->getItemById($itemId);
            if (!$item) {
                throw new \Magento\Framework\Exception\LocalizedException(__('This quote item does not exist.'));
            }
            $productId = $item->getProduct()->getId();
            $product = $this->_getProduct($productId);
            $request = $this->_getProductRequest($requestInfo);

            if ($productId) {
                $stockItem = $this->stockRegistry->getStockItem($productId, $product->getStore()->getWebsiteId());
                $minimumQty = $stockItem->getMinSaleQty();
                // If product was not found in cart and there is set minimal qty for it
                if ($minimumQty
                    && $minimumQty > 0
                    && !$request->getQty()
                    && !$this->getQuote()->hasProductId($productId)
                ) {
                    $request->setQty($minimumQty);
                }
            }

            $result = $this->getQuote()->updateItem($itemId, $request, $updatingParams);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_checkoutSession->setUseNotice(false);
            $result = $e->getMessage();
        }

        /**
         * We can get string if updating process had some errors
         */
        if (is_string($result)) {
            if ($this->_checkoutSession->getUseNotice() === null) {
                $this->_checkoutSession->setUseNotice(true);
            }
            throw new \Magento\Framework\Exception\LocalizedException(__($result));
        }

        $this->_eventManager->dispatch(
            'checkout_cart_product_update_after',
            ['quote_item' => $result, 'product' => $product]
        );
        $this->_checkoutSession->setLastAddedProductId($productId);
        return $result;
    }

    /**
     * Getter for RequestInfoFilter
     *
     * @deprecated
     * @return \Magento\Checkout\Model\Cart\RequestInfoFilterInterface
     */
    private function getRequestInfoFilter()
    {
        if ($this->requestInfoFilter === null) {
            $this->requestInfoFilter = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Checkout\Model\Cart\RequestInfoFilterInterface::class);
        }
        return $this->requestInfoFilter;
    }
	
    /**
     * Getter for RequestInfoFilter
     *
     * @deprecated
     * @return \Magento\Checkout\Model\Cart\RequestInfoFilterInterface
     */
	public function getCartId($cartData)
	{
		$carts=array_keys($cartData);
		return $carts[0];
	}
}
