<?php
namespace Payer\Checkout\Controller\Checkout;

class Settle extends \Magento\Framework\App\Action\Action {

	protected $payerCheckoutModel;
	protected $quoteFactory;
	protected $quoteManagement;

	public function __construct(
			\Magento\Framework\App\Action\Context $context,
			\Magento\Quote\Model\QuoteFactory $quoteFactory,
			\Magento\Quote\Model\QuoteManagement $quoteManagement,
			\Payer\Checkout\Model\Payment $payerCheckoutModel
	) {
		$this->payerCheckoutModel = $payerCheckoutModel;
		$this->quoteFactory = $quoteFactory;
		$this->quoteManagement = $quoteManagement;
		parent::__construct($context);
	}

	/**
	 * @return \Magento\Framework\View\Result\Page
	 */
	public function execute() {
		$credentials = array(
				'agent_id' => $this->payerCheckoutModel->getConfigData('agent_id'),
				'post' => array(
						'key_1' => $this->payerCheckoutModel->getConfigData('key_1'),
						'key_2' => $this->payerCheckoutModel->getConfigData('key_2')
				),
		);
		try {
			$gateway = \Payer\Sdk\Client::create($credentials);
			$purchase = new \Payer\Sdk\Resource\Purchase($gateway);
			$purchase->validateCallbackRequest();
			$quote = $this->quoteFactory->create()->load($_REQUEST['quote_id']);
			$quote->setPaymentMethod('payer_checkout');
			$quote->setAdditionalInformation('payer_payment_type', $_REQUEST['payer_payment_type']);
			$quote->setAdditionalInformation('payer_payment_id', $_REQUEST['payer_payment_id']);
			$quote->save();
			$quote->getPayment()->importData(['method' => 'payer_checkout']);
			$quote->collectTotals()->save();
			$order = $this->quoteManagement->submit($quote);
			$order->setEmailSent(0);
			$purchase->acceptCallbackRequest();
		} catch(\Payer\Sdk\Exception\PayerException $e) {
			var_dump($e);
		}
	}
}
