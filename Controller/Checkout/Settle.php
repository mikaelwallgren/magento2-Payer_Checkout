<?php
namespace Payer\Checkout\Controller\Checkout;

use Braintree\Exception;

class Settle extends \Magento\Framework\App\Action\Action {

	protected $payerCheckoutModel;
	protected $quoteFactory;
	protected $quoteManagement;
	protected $customerFactory;
	protected $storeManager;
	protected $customerRepository;

	public function __construct(
			\Magento\Framework\App\Action\Context $context,
			\Magento\Quote\Model\QuoteFactory $quoteFactory,
			\Magento\Quote\Model\QuoteManagement $quoteManagement,
			\Magento\Customer\Model\CustomerFactory $customerFactory,
			\Magento\Store\Model\StoreManagerInterface $storeManager,
			\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
			\Payer\Checkout\Model\Payment\All $payerCheckoutAllModel,
			\Payer\Checkout\Model\Payment\Card $payerCheckoutCardModel,
			\Payer\Checkout\Model\Payment\Invoice $payerCheckoutInvoiceModel,
			\Payer\Checkout\Model\Payment\Bank $payerCheckoutBankModel,
			\Payer\Checkout\Model\Payment\Installment $payerCheckoutInstallmentModel,
			\Payer\Checkout\Model\Payment\Swish $payerCheckoutSwishModel
	) {
		if($_REQUEST['payer_payment_type'] == 'all') {
			$this->payerCheckoutModel = $payerCheckoutAllModel;
		} else if($_REQUEST['payer_payment_type'] == 'card') {
			$this->payerCheckoutModel = $payerCheckoutCardModel;
		} else if($_REQUEST['payer_payment_type'] == 'invoice') {
			$this->payerCheckoutModel = $payerCheckoutInvoiceModel;
		} else if($_REQUEST['payer_payment_type'] == 'bank') {
			$this->payerCheckoutModel = $payerCheckoutBankModel;
		} else if($_REQUEST['payer_payment_type'] == 'installment') {
			$this->payerCheckoutModel = $payerCheckoutInstallmentModel;
		} else if($_REQUEST['payer_payment_type'] == 'swish') {
			$this->payerCheckoutModel = $payerCheckoutSwishModel;
		}
		$this->quoteFactory = $quoteFactory;
		$this->quoteManagement = $quoteManagement;
		$this->customerFactory = $customerFactory;
		$this->storeManager = $storeManager;
		$this->customerRepository = $customerRepository;
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
			$store = $this->storeManager->getStore();
			$quote = $this->quoteFactory->create()->load($_REQUEST['quote_id']);
			if(!$quote->getReservedOrderId()) {
				$customer = $this->customerFactory->create();
				$customer->setWebsiteId($store->getWebsiteId());
				$customer->loadByEmail($quote->getBillingAddress()->getEmail());
				if(!$customer->getEntityId()) {
					$customer->setWebsiteId($store->getWebsiteId())
							->setStore($store)
							->setFirstname($quote->getBillingAddress()->getFirstname())
							->setLastname($quote->getBillingAddress()->getLastname())
							->setEmail($quote->getBillingAddress()->getEmail());
					$customer->save();
					$customer->loadByEmail($quote->getBillingAddress()->getEmail());
				}
				$customer = $this->customerRepository->getById($customer->getEntityId());
				$quote->assignCustomer($customer);
				$quote->setPaymentMethod($this->payerCheckoutModel->getCode());
				$quote->setAdditionalInformation('payer_payment_type', $_REQUEST['payer_payment_type']);
				$quote->setAdditionalInformation('payer_payment_id', $_REQUEST['payer_payment_id']);
				if(!$quote->getBillingAddress()->getCustomerId()){
					$quote->getBillingAddress()->setCustomerId($customer->getId());
				}
				if(!$quote->getShippingAddress()->getCustomerId()){
					$quote->getShippingAddress()->setCustomerId($customer->getId());
				}
				$quote->save();
				$quote = $this->quoteFactory->create()->load($_REQUEST['quote_id']);
				$quote->getPayment()->importData(['method' => $this->payerCheckoutModel->getCode()]);
				$quote->collectTotals()->save();
				$order = $this->quoteManagement->submit($quote);
				$order->setEmailSent(0);
			}
			$purchase->acceptCallbackRequest();
		} catch(\Payer\Sdk\Exception\PayerException $e) {
			error_log($e->getMessage());
			var_dump($e);
		}
	}
}
