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
			\Payer\Checkout\Model\Payment\All $payerCheckoutAllModel,
			\Payer\Checkout\Model\Payment\Card $payerCheckoutCardModel,
			\Payer\Checkout\Model\Payment\Invoice $payerCheckoutInvoiceModel,
			\Payer\Checkout\Model\Payment\Bank $payerCheckoutBankModel,
			\Payer\Checkout\Model\Payment\Installment $payerCheckoutInstallmentModel,
			\Payer\Checkout\Model\Payment\Swish $payerCheckoutSwishModel
	) {
		if($_REQUEST['payer_method'] == 'all') {
			$this->payerCheckoutModel = $payerCheckoutAllModel;
		} else if($_REQUEST['payer_method'] == 'card') {
			$this->payerCheckoutModel = $payerCheckoutCardModel;
		} else if($_REQUEST['payer_method'] == 'invoice') {
			$this->payerCheckoutModel = $payerCheckoutInvoiceModel;
		} else if($_REQUEST['payer_method'] == 'bank') {
			$this->payerCheckoutModel = $payerCheckoutBankModel;
		} else if($_REQUEST['payer_method'] == 'installment') {
			$this->payerCheckoutModel = $payerCheckoutInstallmentModel;
		} else if($_REQUEST['payer_method'] == 'swish') {
			$this->payerCheckoutModel = $payerCheckoutSwishModel;
		}
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
