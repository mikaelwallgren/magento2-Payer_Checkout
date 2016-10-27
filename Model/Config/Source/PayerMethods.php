<?php
namespace Payer\Checkout\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PayerMethods implements ArrayInterface {
	/*
	 * Option getter
	 * @return array
	 */
	public function toOptionArray() {
		return array(
				array('label' => 'All methods', 'value' => 'auto'),
				array('label' => 'Card', 'value' => 'card'),
				array('label' => 'Invoice', 'value' => 'invoice'),
				array('label' => 'Bank', 'value' => 'bank'),
				array('label' => 'Installment', 'value' => 'installment'),
				array('label' => 'Swish', 'value' => 'Swish'),
		);
	}


}