<?php
/**
 * AllInOneControllerOrder
 *
 * @author Shisha <shisha225@gmail.com>
 */

namespace AllPay\AllInOneController;

use AllPay\AllInOne\OrderDecorator;

class OrderProvider extends OrderDecorator
{
    protected $orderId;

    public function setOrderId($orderId)
    {
        return $this->orderId = $orderId;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }
}
