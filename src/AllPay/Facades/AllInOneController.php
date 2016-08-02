<?php

namespace AllPay\Facades;

use AllPay\AllInOneController\AllInOneController as AllInOneControllerReal;

class AllInOneController extends Facade
{
	protected static function getFacadeAccessor() {
		return AllInOneControllerReal::class;
	}
}
