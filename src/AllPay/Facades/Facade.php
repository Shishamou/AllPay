<?php
/**
 * Facade
 *
 * @author Shisha <shisha225@gmail.com>
 */

namespace AllPay\Facades;

use RuntimeException;

abstract class Facade
{
	/**
	 * @var mixed
	 */
	protected static $facadeInstance;

	/**
	 * 解析過的物件實例。
	 *
	 * @var array
	 */
	protected static $resolvedInstance;

	/**
	 * 靜態呼叫物件實例
	 *
	 * @param string
	 * @param array
	 * @return mixed
	 * @throws \RuntimeException
	 */
	public static function __callStatic($method, $arguments)
	{
		if ($instance = self::getFacadeInstance()) {
			switch (count($arguments)) {
				case 0:
					return $instance->$method();

				case 1:
					return $instance->$method($arguments[0]);

				case 2:
					return $instance->$method($arguments[0], $arguments[1]);

				case 3:
					return $instance->$method($arguments[0], $arguments[1], $arguments[2]);

				default:
					return call_user_func_array([$instance, $method], $arguments);
			}
		}

		throw new RuntimeException('Facade 未指定。');
	}

	/**
     * 初始與取得實例
     *
     * @return mixed
     */
    public static function getFacadeInstance()
    {
		if (empty(static::$facadeInstance)) {
			static::$facadeInstance = self::resolveFacadeInstance(static::getFacadeAccessor());
		}

		return static::$facadeInstance;
    }

    /**
     * 解析 facade instance, 若有則回傳, 反之進行建構
     *
     * @param  string|object
     * @return mixed
     */
    protected static function resolveFacadeInstance($name)
    {
        if (is_object($name)) {
            return $name;
        }

        if ( ! isset(self::$resolvedInstance[$name])) {
			self::$resolvedInstance[$name] = new $name;
        }

		return self::$resolvedInstance[$name];
    }

	/**
	 * 註冊 facade
	 */
	abstract protected static function getFacadeAccessor();
}
