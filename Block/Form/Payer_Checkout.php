<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Payer\Checkout\Block\Form;

/**
 * Block for Payer Checkout payment method form
 */
class Payer_Checkout extends \Magento\Payment\Block\Form
{
	/**
	 * Payer Checkout template
	 *
	 * @var string
	 */
	protected $_template = 'form/payer_checkout.phtml';
}
