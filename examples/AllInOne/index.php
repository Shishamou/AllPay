<?php
/**
 * AllInOneController 範例
 *
 * @author Shisha <shisha225@gmail.com>
 */

require dirname(dirname(__DIR__)) . '/autoload.php';

use AllPay\AllInOne;

// 設置商家資料
// AllInOne::config('HashKey', 'HashIV', '特店代碼');

// 測試模式開啟
AllInOne::testing(1);

echo AllInOne::checkOut('normal', function($order) use($request) {
    $sdk = $order->getSdk();
    $sdk->Send['ReturnURL'] = 'feedback.php';
    $sdk->Send['OrderResultURL'] = 'return.php';

    $order->addItem('測試商品', 1);
});
