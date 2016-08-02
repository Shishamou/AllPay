<?php
/**
 * OrderProvider
 *
 * @author Shisha <shisha225@gmail.com>
 */

namespace AllPay\AllInOne;

abstract class OrderProvider extends AllInOneProvider
{
	/**
	 * 商品
	 *
	 * @var string
	 */
	protected $items = array();

	/**
	 * 單位
	 *
	 * @var string
	 */
	protected $currency = '元';


	/**
	 * 初始化歐付寶金流 SDK
	 *
	 * @return void
	 */
	public function __construct()
	{
		call_user_func_array(array('parent', __FUNCTION__), func_get_args());

		$this->sdk->Send['MerchantTradeNo'] = static::makeTradeNo();
		$this->sdk->Send['MerchantTradeDate'] = date('Y/m/d H:i:s');
		$this->sdk->Send['TradeDesc'] = "沒有說明。";
		$this->sdk->Send['Remark'] = "";
	}

	/**
	 * 生成 TradeNo
	 *
	 * @return string
	 */
	protected static function makeTradeNo()
	{
		return date('ymdHis') . str_pad(rand(0, 99999999), 8, STR_PAD_LEFT);
	}

	/**
	 * 結帳
	 *
	 * @return void
	 */
	public function checkOut($button = null)
	{
		$amount = 0;
		foreach ($this->items as $item) {
			$amount += $item['Price'] * $item['Quantity'];
		}
		$this->sdk->Send['TotalAmount'] = $amount;
		$this->sdk->Send['Items'] = $this->items;

		return $this->sdk->checkOutString($button);
	}

	/**
	 * 設定歐付寶訂單驗證 url
	 *
	 * @param string
	 * @return string
	 */
	public function setFeedbackUrl($url)
	{
		return $this->sdk->Send['ReturnURL'] = $url;
	}

	/**
	 * 設定歐付寶訂單回傳 url
	 *
	 * @param string
	 * @return string
	 */
	public function setReturnUrl($url)
	{
		return $this->sdk->Send['OrderResultURL'] = $url;
	}

	/**
	 * 設定訂單編號
	 *
	 * @param string
	 * @return string
	 */
	public function setTradeNo($tradeNo)
	{
		return $this->sdk->Send['MerchantTradeNo'] = $tradeNo;
	}

	/**
	 * 取得訂單編號
	 *
	 * @return string
	 */
	public function getTradeNo()
	{
		return $this->sdk->Send['MerchantTradeNo'];
	}

	/**
	 * 訂單說明
	 *
	 * @param string
	 * @return string
	 */
	public function desc($text)
	{
		return $this->sdk->Send['TradeDesc'] = $text;
	}

	/**
	 * 訂單備註
	 *
	 * @param string
	 * @return string
	 */
	public function remark($text)
	{
		return $this->sdk->Send['Remark'] = $text;
	}

	/**
	 * 設定單位
	 *
	 * @param string
	 * @return void
	 */
	public function setCurrency($currency)
	{
		$this->currency = $currency;
	}

	/**
	 * 新增商品
	 *
	 * @param string 商品名聲
	 * @param integer 價格
	 * @param integer 數量
	 * @param string 商品網址
	 * @return void
	 */
	public function addItem($name, $price, $quantity = 1, $url = '')
	{
		$this->items[] = array(
			'Name' => $name,
			'Price' => (int)$price,
			'Currency' => $this->currency,
			'Quantity' => (int)$quantity,
			'URL' => $url
		);
	}

	/**
	 * 取得已新增的商品
	 *
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}
}
