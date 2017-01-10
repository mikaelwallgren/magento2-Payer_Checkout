<?php
namespace Payer\Checkout\Controller\Checkout;

class Redirect extends \Magento\Framework\App\Action\Action {

	protected $payerCheckoutModel;
	protected $checkoutSession;
	protected $urlBuilder;

	/**
	 * @param \Magento\Framework\App\Action\Context $context
	 */
	public function __construct(
			\Magento\Framework\App\Action\Context $context,
			\Magento\Checkout\Model\Session $checkoutSession,
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
		$this->checkoutSession = $checkoutSession;
		$this->urlBuilder = $context->getUrl();
		parent::__construct($context);
	}

	/**
	 * @return \Magento\Framework\View\Result\Page
	 */
	public function execute() {
		$quote = $this->checkoutSession->getQuote();
		$customer = $quote->getCustomer();
		$billingAddress = $quote->getBillingAddress();

		$credentials = array(
				'agent_id' => $this->payerCheckoutModel->getConfigData('agent_id'),
				'post' => array(
						'key_1' => $this->payerCheckoutModel->getConfigData('key_1'),
						'key_2' => $this->payerCheckoutModel->getConfigData('key_2')
				),
		);

		$yourReference = '';
		if($billingAddress->getCompany() != ''){
			$yourReference = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
		}

		$data = array(
				'payment' => array(
						'language' => 'sv',
						'method' => $_REQUEST['payer_method'],
						'url' => array(
								'authorize' => $this->urlBuilder->getUrl('payer/checkout/authorize') . '?quote_id=' . $quote->getId(),
								'settle' => $this->urlBuilder->getUrl('payer/checkout/settle') . '?quote_id=' . $quote->getId(),
								'redirect' => $this->urlBuilder->getUrl('/checkout'),
								'success' => $this->urlBuilder->getUrl('payer/checkout/success') . '?quote_id=' . $quote->getId().'&payer_method='.$_REQUEST['payer_method']
						),
				),
				'purchase' => array(
						'charset' => 'UTF-8',
						'currency' => $quote->getQuoteCurrencyCode(),
						'description' => 'Quote Id ' . $quote->getId(),
						'reference_id' => $quote->getId(),
						'test_mode' => ($this->payerCheckoutModel->getConfigData('debug')==1)?'true':'false',
						'customer' => array(
								'identity_number' => '',
								'organisation' => $billingAddress->getCompany(),
								'your_reference' => $yourReference,
								'first_name' => $billingAddress->getFirstname(),
								'last_name' => $billingAddress->getLastname(),
								'address' => array(
										'address_1' => $billingAddress->getStreetLine(1),
										'address_2' => $billingAddress->getStreetLine(2),
										'co' => '',
								),
								'zip_code' => $billingAddress->getPostcode(),
								'city' => $billingAddress->getCity(),
								'country_code' => $billingAddress->getCountry(),
								'phone' => array(
										'home' => $billingAddress->getTelephone(),
										'work' => $billingAddress->getTelephone(),
										'mobile' => $billingAddress->getTelephone()
								),
								'email' => $billingAddress->getEmail()
						),
						'items' => array()
				)
		);

		$items = $quote->getAllVisibleItems();
		$lineNumber = 0;
		foreach($items as $item) {
			$lineNumber++;
			$data['purchase']['items'][] = array(
					'type' => 'freeform',
					'line_number' => $lineNumber,
					'article_number' => $item->getSku(),
					'description' => $item->getName(),
					'unit_price' => $item->getPriceInclTax(),
					'unit_vat_percentage' => $item->getTaxPercent(),
					'quantity' => $item->getQty(),
					'unit' => null,
					'account' => null,
					'dist_agent_id' => null
			);
		}

		try {
			$gateway = \Payer\Sdk\Client::create($credentials);
			$purchase = new \Payer\Sdk\Resource\Purchase($gateway);
			$purchase->create($data);
		} catch(\Payer\Sdk\Exception\PayerException $e) {
			var_dump($e);
		}
	}
}
