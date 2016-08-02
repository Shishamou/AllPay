<?php
/**
 * AllInOneManager
 *
 * @author Shisha <shisha225@gmail.com>
 */

namespace AllPay\AllInOne;

use RuntimeException;

class AllInOneManager
{
	/**
	 * 測試模式
	 *
	 * @var boolean
	 */
	protected $isTesting = false;

	/**
	 * 設定值, 依序為 [$hashKey, $hashIv, $merchantId]
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * Service Url
	 *
	 * @var array
	 */
	protected $serviceUrl = array();

	/**
	 * 測試模式之設定值
	 *
	 * @var array
	 */
	protected static $testingConfig = array(
		'5294y06JbISpM5x9', 'v77hoKGq4kWxNNIS', '2000132'
	);

	/**
	 * 測試模式之 Service Url
	 *
	 * @var array
	 */
	protected static $testingServiceUrl = array(
		"AioCheckOut" => "http://payment-stage.allpay.com.tw/Cashier/AioCheckOut",
		"QueryTradeInfo" => "http://payment-stage.allpay.com.tw/Cashier/QueryTradeInfo",
	);

	/**
	 * 正式模式之 Service Url
	 *
	 * @var array
	 */
	protected static $runningServiceUrl = array(
		"AioCheckOut" => "https://payment.allpay.com.tw/Cashier/AioCheckOut",
		"QueryTradeInfo" => "https://payment.allpay.com.tw/Cashier/QueryTradeInfo",
	);


	/**
	 * 初始化設定值
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->config = static::$testingConfig;
		$this->serviceUrl = static::$testingServiceUrl;
	}

	/**
	 * 設定歐付寶金流
	 *
	 * @param string
	 * @param string
	 * @param string
	 * @return $this
	 */
	public function config($hashKey, $hashIv, $merchantId)
	{
		$this->config = func_get_args();

		return $this;
	}

	/**
	 * 設定測試模式
	 *
	 * @param boolean
	 * @return $this
	 */
	public function testing($bool)
	{
		$this->isTesting = settype($bool, 'boolean');

		return $this;
	}

	/**
	 * 取得 ServiceUrl
	 *
	 * @param string
	 * @return string
	 */
	private function getServiceUrl($name)
	{
		if ($this->isTesting) {
			return static::$testingServiceUrl[$name];
		}

		return static::$runningServiceUrl[$name];
	}

	/**
	 * 取得設定
	 *
	 * @return array
	 */
	private function getConfig()
	{
		return ($this->isTesting) ? static::$testingConfig : $this->config;
	}

	/**
	 * 建立訂單並送出
	 *
	 * @param string
	 * @param mixed
	 * @return string
	 * @throws \RuntimeException
	 */
	public function checkOut($payment, $callable)
	{
		$this->checkCallableWithException($callable);

		$order = $this->initialOrder($payment);
		call_user_func($callable, $order);

		if (empty($order->getItems())) {
			throw new RuntimeException("訂單商品不能為空");
		}

		return $this->postOrder($order);
	}

	/**
	 * 送出訂單
	 *
	 * @param \AllPay\AllInOne\OrderProvider
	 * @return string
	 */
	protected function postOrder($order)
	{
		if ($this->isTesting) {
			return $order->checkOut('送出測試表單');
		} else {
			return $order->checkOut();
		}
	}

	/**
	 * 初始化訂單
	 *
	 * @param string
	 * @return \AllPay\AllInOne\OrderProvider
	 * @throws \RuntimeException
	 */
	protected function initialOrder($payment)
	{
		$class = strtoupper(substr($payment, 0, 1)) . substr($payment, 1);
		$class = __NAMESPACE__ . "\\{$class}OrderProvider";

		if ( ! class_exists($class)) {
			throw new RuntimeException("未定義的付費方法：{$payment}");
		}

		return $this->initialProvider($class, 'AioCheckOut');
	}

	/**
	 * 處理訂單 feedback
	 *
	 * @param mixed
	 * @return void
	 */
	public function feedback($callable)
	{
		$this->checkCallableWithException($callable);

		$class = __NAMESPACE__ . "\\FeedbackProvider";
		$provider = $this->initialProvider($class, 'AioCheckOut');

		$feedback = $provider->checkOutFeedback();
		if ( ! empty($feedback)) {
			$this->handleFeedback($callable, $feedback);
		}
	}

	/**
	 * 呼叫使用者自訂函式處理訂單 feedback，並中止程式。
	 *
	 * @param mixed
	 * @param array
	 * @return void
	 */
	protected function handleFeedback($callable, $feedback)
	{
		try {
			ob_start();
			call_user_func($callable, $feedback);
			ob_end_clean();

			echo '1|OK';
		} catch (\Exception $e) {
			echo '0|' . $e->getMessage();
		} finally {
			die;
		}
	}

	/**
	 * 呼叫使用者自訂函式處理訂單回傳
	 *
	 * @param mixed
	 * @return void
	 */
	public function result($callable)
	{
		$this->checkCallableWithException($callable);

		$class = __NAMESPACE__ . "\\FeedbackProvider";
		$provider = $this->initialProvider($class, 'AioCheckOut');

		$feedback = $provider->checkOutFeedback();
		call_user_func($callable, $feedback);
	}

	/**
	 * 初始化 Provider
	 *
	 * @param string
	 * @return \AllPay\AllInOne\OrderProvider
	 */
	private function initialProvider($provider, $serviceName)
	{
		$serviceUrl = $this->getServiceUrl($serviceName);
		list($hashKey, $hashIv, $merchantId) = $this->getConfig();

		return new $provider($serviceUrl, $hashKey, $hashIv, $merchantId);
	}

	/**
	 * 檢查是否為 callable，並拋錯
	 *
	 * @param mixed
	 * @return void
	 * @throws \RuntimeException
	 */
	private function checkCallableWithException($callable)
	{
		if ( ! is_callable($callable)) {
			throw new RuntimeException("參數必須為 callable。");
		}
	}
}
