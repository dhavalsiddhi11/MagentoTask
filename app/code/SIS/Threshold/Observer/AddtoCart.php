<?php
namespace SIS\Threshold\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

use \Magento\Checkout\Model\Session as CheckoutSession;

class AddtoCart implements ObserverInterface {

    /** @var CheckoutSession */
    protected $checkoutSession;

    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(CheckoutSession $checkoutSession) {
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer) {
        /** @var \Magento\Catalog\Model\Product\Interceptor $product */
        $product = $observer->getProduct();

        /** @var \Magento\Quote\Model\Quote  */
        $quote = $this->checkoutSession->getQuote();
    }
}