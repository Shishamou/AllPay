<?php
/**
 * AllInOneProvider
 *
 * @author Shisha <shisha225@gmail.com>
 */

namespace AllPay\AllInOne;

use AllInOne;

abstract class AllInOneProvider
{
	/**
	 * 訂單
	 *
	 * @var \AllInOne
	 */
	protected $sdk;

	/**
	 * 初始化歐付寶金流 SDK
	 *
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return void
	 */
	public function __construct($serviceUrl, $hashKey, $hashIv, $merchantId)
	{
		$this->sdk = new AllInOne();

		$this->sdk->ServiceMethod = 1;
		$this->sdk->ServiceURL = $serviceUrl;

		$this->sdk->HashKey = $hashKey;
		$this->sdk->HashIV = $hashIv;
		$this->sdk->MerchantID = $merchantId;
	}

	/**
	 * 取得 SDK 物件
	 *
	 * @return \AllInOne
	 */
	public function getSdk()
	{
		return $this->sdk;
	}
}
