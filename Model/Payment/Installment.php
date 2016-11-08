<?php

namespace Payer\Checkout\Model\Payment;

class Installment extends \Magento\Payment\Model\Method\AbstractMethod
{
	const CODE = 'payer_checkout_installment';

	protected $_code = self::CODE;

	/**
	 * Payer Checkout payment block paths
	 *
	 * @var string
	 */
	protected $_formBlockType = 'Payer\Checkout\Block\Form\Payer_Checkout_Installment';

	/**
	 * Determine method availability based on  config data
	 *
	 * @param \Magento\Quote\Api\Data\CartInterface|null $quote
	 * @return bool
	 */
	public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
	{

		if (!$this->getConfigData('agent_id') || !$this->getConfigData('key_1') || !$this->getConfigData('key_2')) {
			return false;
		}

		return parent::isAvailable($quote);
	}

}