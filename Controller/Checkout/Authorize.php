<?php
namespace Payer\Checkout\Controller\Checkout;

class Authorize extends \Magento\Framework\App\Action\Action {

	protected $payerCheckoutModel;

	public function __construct(
			\Magento\Framework\App\Action\Context $context,
			\Payer\Checkout\Model\Payment $payerCheckoutModel
	) {
		$this->payerCheckoutModel = $payerCheckoutModel;
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
			$purchase->createAuthorizeResource();
		} catch(\Payer\Sdk\Exception\PayerException $e) {
			var_dump($e);
		}
	}
}
