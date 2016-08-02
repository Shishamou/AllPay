<?php
/**
 * AllInOneController 範例
 *
 * @author Shisha <shisha225@gmail.com>
 */

require dirname(__DIR__) . '/autoload.php';

use AllPay\AllInOneController;

// 設置商家資料
// AllInOneController::config('HashKey', 'HashIV', '特店代碼');

// 測試模式開啟
// AllInOneController::testing(1);

// 處理結帳
AllInOneController::onCheckOut('normal', function($order, $orderId = '000001') {
    $order->setOrderId($orderId);
    $order->addItem('測試商品', 1);
});

// 處理交易結果
AllInOneController::onReturn(function($return) {
    throw new Exception("錯誤");
    echo "<pre>", var_export($return, 1);
});

// 處理交易驗證
AllInOneController::onFeedback(function($feedback) {
    file_put_contents("feedback.log", json_encode($feedback) . "\n");
});

// 處理錯誤
AllInOneController::onError(function($exce) {
    echo "Error" . $exce->getMessage();
});
