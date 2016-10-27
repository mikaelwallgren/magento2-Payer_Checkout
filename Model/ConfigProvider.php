<?php
namespace Payer\Checkout\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface {


	const CODE = 'payer_checkout';
	protected $method;

	/**
	 * Payment ConfigProvider constructor.
	 * @param \Magento\Payment\Helper\Data $paymentHelper
	 */
	public function __construct(
			\Magento\Payment\Helper\Data $paymentHelper
	) {
		$this->method = $paymentHelper->getMethodInstance(self::CODE);
	}

	/**
	 * Retrieve assoc array of checkout configuration
	 *
	 * @return array
	 */

	public function getConfig() {
		return [
				'payment' => [
						self::CODE => [
								'methods' => $this->getPayerMethods(),
						]
				]
		];
	}

	protected function getPayerMethods() {
		$methods = explode(',',$this->method->getPayerMethods());
		$return = array();
		foreach($methods as $method){
			$return[] = array('name' => $this->methodToName($method), 'value' => $method);
		}
		if(count($return) === 0){
			$return[] = array('name' => $this->methodToName('auto'), 'value' => 'auto');
		}
		return $return;
	}

	protected function methodToName($method) {
		if($method == 'auto'){
			return 'Payer Checkout';
		}
		return ucfirst($method);
	}
}