<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Baotongda;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Baotongda($config);

// 读取之前保存的订单号
$referenceNo = @file_get_contents(__DIR__ . '/last_reference_no.txt');
if (!$referenceNo) {
    die("请先创建订单并获取订单号！\n");
}

// 清理订单号（去除空白字符和特殊字符）
$referenceNo = trim($referenceNo, " \t\n\r\0\x0B%");

// 获取订单费用明细参数
$params = [
    'reference_no' => $referenceNo    // 客户参考号
];

echo "正在获取订单 {$referenceNo} 的费用明细信息...\n\n";
echo "提示：如果订单刚创建，请确保已经：\n";
echo "1. 创建订单（使用 01_createOrder.php）\n";
echo "2. 提交预报（使用 02_submitForecast.php）\n";
echo "3. 等待系统处理订单（可能需要几分钟时间）\n\n";

// 获取订单费用明细
$result = $app->getOrderFeeDetail($params);
print_r($result);

/**
 * 成功返回示例：
 * Array
 * (
 *     [success] => 1
 *     [cnmessage] => 获取费用明细成功
 *     [enmessage] => Get fee detail successfully
 *     [data] => Array
 *         (
 *             [0] => Array
 *                 (
 *                     [fee_kind_code] => E1
 *                     [fee_kind_name] => 速递运费
 *                     [currency_code] => RMB
 *                     [currency_name] => 人民币
 *                     [currency_rate] => 1.0000
 *                     [currency_amount] => 40.00
 *                     [amount] => 40.00
 *                     [create_time] => 2024-01-29 15:30:00
 *                     [remark] => 系统计费
 *                     [operator] => SYSTEM
 *                 )
 *         )
 * )
 */
