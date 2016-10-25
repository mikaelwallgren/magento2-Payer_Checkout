<?php
namespace Payer\Checkout\Controller\Checkout;

class Success extends \Magento\Framework\App\Action\Action {

	protected $payerCheckoutModel;
	protected $quoteFactory;
	protected $checkoutSession;
	protected $resultFactory;
	protected $order;

	public function __construct(
			\Magento\Framework\App\Action\Context $context,
			\Magento\Quote\Model\QuoteFactory $quoteFactory,
			\Magento\Checkout\Model\Session $checkoutSession,
			\Magento\Sales\Model\Order $order,
			\Payer\Checkout\Model\Payment $payerCheckoutModel
	) {
		$this->payerCheckoutModel = $payerCheckoutModel;
		$this->quoteFactory = $quoteFactory;
		$this->checkoutSession = $checkoutSession;
		$this->order = $order;
		$this->resultFactory = $context->getResultFactory();
		parent::__construct($context);
	}

	/**
	 * @return \Magento\Framework\View\Result\Page
	 */
	public function execute() {
		$quote = $this->quoteFactory->create()->load($_REQUEST['quote_id']);
		$this->order->loadByIncrementId($quote->getReservedOrderId());
		$this->checkoutSession->getCheckout();
		$this->checkoutSession->setLastSuccessQuoteId($quote->getId());
		$this->checkoutSession->setLastQuoteId($quote->getId());
		$this->checkoutSession->setLastOrderId($this->order->getId());
		$this->checkoutSession->setLastRealOrderId($this->order->getRealOrderId());
		$resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl('/checkout/onepage/success');
		return $resultRedirect;
	}
}
