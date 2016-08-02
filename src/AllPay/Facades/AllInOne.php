<?php

namespace AllPay\Facades;

use AllPay\AllInOne\AllInOneManager;

class AllInOne extends Facade
{
	protected static function getFacadeAccessor() {
		return AllInOneManager::class;
	}
}
