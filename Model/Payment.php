<?php

namespace Payer\Checkout\Model;

class Payment extends \Magento\Payment\Model\Method\Cc
{
	const CODE = 'payer_checkout';

	protected $_code = self::CODE;

	protected $_isGateway                   = true;
	protected $_canCapture                  = true;
	protected $_canCapturePartial           = true;
	protected $_canRefund                   = true;
	protected $_canRefundInvoicePartial     = true;

	protected $_payerApi = false;

	protected $_countryFactory;

	protected $_minAmount = null;
	protected $_maxAmount = null;
	protected $_supportedCurrencyCodes = array('USD');

	protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];

	public function __construct(
			\Magento\Framework\Model\Context $context,
			\Magento\Framework\Registry $registry,
			\Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
			\Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
			\Magento\Payment\Helper\Data $paymentData,
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\Magento\Payment\Model\Method\Logger $logger,
			\Magento\Framework\Module\ModuleListInterface $moduleList,
			\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
			\Magento\Directory\Model\CountryFactory $countryFactory,
			array $data = array()
	) {
		parent::__construct(
				$context,
				$registry,
				$extensionFactory,
				$customAttributeFactory,
				$paymentData,
				$scopeConfig,
				$logger,
				$moduleList,
				$localeDate,
				null,
				null,
				$data
		);

		$this->_countryFactory = $countryFactory;
		$this->_payerApi = $payer;
		$this->_payerApi->setApiKey($this->getConfigData('api_key'));
		$this->_minAmount = $this->getConfigData('min_order_total');
		$this->_maxAmount = $this->getConfigData('max_order_total');
	}

	/**
	 * Payment capturing
	 *
	 * @param \Magento\Payment\Model\InfoInterface $payment
	 * @param float $amount
	 * @return $this
	 * @throws \Magento\Framework\Validator\Exception
	 */
	public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
	{

		/** @var \Magento\Sales\Model\Order $order */
		$order = $payment->getOrder();

		/** @var \Magento\Sales\Model\Order\Address $billing */
		$billing = $order->getBillingAddress();

		return $this;
	}

	/**
	 * Payment refund
	 *
	 * @param \Magento\Payment\Model\InfoInterface $payment
	 * @param float $amount
	 * @return $this
	 * @throws \Magento\Framework\Validator\Exception
	 */
	public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
	{
		$transactionId = $payment->getParentTransactionId();

		return $this;
	}

	/**
	 * Determine method availability based on quote amount and config data
	 *
	 * @param \Magento\Quote\Api\Data\CartInterface|null $quote
	 * @return bool
	 */
	public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
	{
		if ($quote && (
						$quote->getBaseGrandTotal() < $this->_minAmount
						|| ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
		) {
			return false;
		}

		if (!$this->getConfigData('api_key')) {
			return false;
		}

		return parent::isAvailable($quote);
	}

	/**
	 * Availability for currency
	 *
	 * @param string $currencyCode
	 * @return bool
	 */
	public function canUseForCurrency($currencyCode)
	{
		if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
			return false;
		}
		return true;
	}
}