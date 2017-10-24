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
use Magento\Framework\Exception\StateException;
/**
 * Shopping cart model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated 
 */
class Cart extends \Magento\Framework\DataObject
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
									
			//CHECK THRESHOLD START
			if (null !== $product->getCustomAttribute('quantity_threshold')) {
				   $qt=$product->getCustomAttribute('quantity_threshold')->getValue();				   				 
				   if(!isset($requestInfo['qty'])) { $requestInfo['qty']=1; }				   				   
				   if($qt!="" && ($requestInfo['qty'] > $qt))
				   {				   					   							
						throw new StateException(__('We can\'t add '.$product->getName().' to your shopping cart right now.You can only add maximum '.$qt.' for it.'));			            			
				   }			  
			}
			//CHECK THRESHOLD END
    }
	public function beforeupdateItems( \Magento\Checkout\Model\Cart $productInfo,$requestInfo = null)
    {    	  	
		$product_ids=$this->getQuoteProductQts();
		$storeId = $this->_storeManager->getStore()->getId();		
		foreach($requestInfo as $k=>$data)
		{			
			$product = $this->productRepository->getById($product_ids[$k], false, $storeId, true);
			if (null !== $product->getCustomAttribute('quantity_threshold')) {									
				$qt=$product->getCustomAttribute('quantity_threshold')->getValue();														
				if(!isset($data['qty'])) { $data['qty']=1; }
				if($qt && ($data['qty'] > $qt))
				{
					throw new StateException(__('We can\'t add '.$product->getName().' to your shopping cart right now.You can only add maximum '.$qt.' for it.'));				
				}		
			}
		}

    	
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
	 protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
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
	public function getQuoteProductQts()
    {
        $products = $this->getData('product_ids');
        if ($products === null) {
            $products = [];
            foreach ($this->getQuote()->getAllItems() as $item) {
                $products[$item->getId()] = $item->getProductId();
				
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

}
