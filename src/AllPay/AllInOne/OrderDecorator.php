<?php
/**
 * OrderDecorator
 *
 * @author Shisha <shisha225@gmail.com>
 */

namespace AllPay\AllInOne;

abstract class OrderDecorator
{
    /**
     * @var \AllPay\AllInOne\OrderProvider
     */
    protected $order;

    /**
     * 針對 OrderProvider 進行包裝
     *
     * @param \AllPay\AllInOne\OrderProvider
     * @return void
     */
    public function __construct(OrderProvider $order)
    {
        $this->order = $order;
    }

    /**
     * 呼叫被包裝的 OrderProvider 方法
     *
     * @param string
     * @param array
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->order, $name), $arguments);
    }
}
