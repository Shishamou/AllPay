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

AllInOne::feedback(function($feedback) {
    file_put_contents("feedback.log", json_encode($feedback) . "\n\n");
});
