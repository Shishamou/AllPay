<?php
/**
 * AllInOneController
 *
 * @author Shisha <shisha225@gmail.com>
 */

namespace AllPay\AllInOneController;

use RuntimeException;
use Http\Uri;
use AllPay\AllInOne\AllInOneManager;

class AllInOneController extends AllInOneManager
{
	/**
	 * @var \Http\Uri
	 */
	protected $uri;

	/**
	 * @var array
	 */
	protected $events = array();


	/**
	 * 建立當前請求之 Request 物件
	 */
	public function __construct()
	{
		$this->uri = new Uri($_SERVER);

		$this->onCheckOut('normal', function() {});
		$this->onReturn(function() {});
		$this->onError(function() {});
		$this->onFeedback(function() {});

		parent::{__FUNCTION__}();
	}

	public function onCheckOut($payment, $callable)
	{
		$this->events['checkOut'] = func_get_args();
	}

	public function onReturn($callable)
	{
		$this->events['return'] = func_get_args();
	}

	public function onError($callable)
	{
		$this->events['error'] = func_get_args();
	}

	public function onFeedback($callable)
	{
		$this->events['feedback'] = func_get_args();
	}

	public function url($uri = null, $after = '')
	{
		return $this->uri->url($uri, $after);
	}

	/**
	 * 當腳本結束時處理
	 */
	public function __destruct()
	{
		echo $this->execute();
	}

	protected function execute()
	{
		try {
			return $this->resolveRequest();
		} catch (\Exception $exce) {
			if ($this->isTesting) {
				throw $exce;
			}

			return $this->handleError($exce);
		}
	}

	protected function handleError(\Exception $exce)
	{
		list($callable) = $this->events['error'];
		return call_user_func($callable, $exce);
	}

	/**
	 * 解析請求
	 *
	 * @return mixed
	 */
	protected function resolveRequest()
	{
		$pathInfo = $this->uri->getPathInfo();
		$pathInfo = trim($pathInfo, '/');
		$pathInfo = explode('/', $pathInfo);

		$action = array_shift($pathInfo);
		$orderId = array_shift($pathInfo);
		$tradeNo = array_shift($pathInfo);

		if (preg_match('/^[0-9]+$/', $action)) {
			$orderId = $action;
		}

		switch ($action) {
		    case 'feedback':
		        return $this->callFeedback($orderId, $tradeNo);

		    case 'return':
				return $this->callReturn($orderId, $tradeNo);

		    default:
				return $this->callCheckOut($orderId);
		}
	}

	protected function callCheckOut($orderId = '')
	{
		list($payment, $callable) = $this->events['checkOut'];

		return parent::checkOut($payment, function($order) use ($callable, $orderId) {
			if ( ! empty($orderId)) {
				$order->setOrderId($orderId);
				return call_user_func($callable, $order, $orderId);
			}

			return call_user_func($callable, $order);
		});
	}

	protected function callReturn($orderId, $tradeNo)
	{
		list($callable) = $this->events['return'];

		return parent::result(function($result) use($callable, $orderId, $tradeNo) {
			static::checkFeedbackTradeNoWithException($result, $tradeNo);
			call_user_func($callable, $result, $orderId);
		});
	}

	protected function callFeedback($orderId, $tradeNo)
	{
		list($callable) = $this->events['feedback'];

		return parent::feedback(function($feedback) use($callable, $orderId, $tradeNo) {
			static::checkFeedbackTradeNoWithException($feedback, $tradeNo);
			call_user_func($callable, $feedback, $orderId);
		});
	}

	protected static function checkFeedbackTradeNoWithException($feedback, $tradeNo)
	{
		if (isset($feedback['MerchantTradeNo']) && $feedback['MerchantTradeNo'] === $tradeNo) {
			return;
		}

		throw new RuntimeException("TradeNo 驗證失敗");
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
		$order = parent::{__FUNCTION__}($payment);
		$order = new OrderProvider($order);
		return $order;
	}

	/**
	 * 送出訂單
	 *
	 * @param \AllPay\AllInOne\OrderProvider
	 * @return string
	 */
	protected function postOrder($order)
	{
		$tradeNo = $order->getTradeNo();
		$orderId = $order->getOrderId();

		$order->setFeedbackUrl($this->url(null, "/feedback/{$orderId}/{$tradeNo}"));
		$order->setReturnUrl($this->url(null, "/return/{$orderId}/{$tradeNo}"));

		return parent::{__FUNCTION__}($order);
	}
}
