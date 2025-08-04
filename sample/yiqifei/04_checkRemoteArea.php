<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Yiqifei;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Yiqifei($config);

// 检查偏远地区参数
$params = [
    'productCode' => 'USE13-MPKY',    // 产品代码（美国空运免泡-普货）
    'country'     => 'US',            // 国家二字码
    'postCode'    => '10001',         // 邮编
    'weight'      => 2.0,             // 重量(KG)
];

// 检查是否是偏远地区
$result = $app->checkRemoteArea($params);


print_r($result);

echo "=== 偏远地区检查结果 ===\n";
echo "状态: " . ($result['flag'] ? '成功' : '失败') . "\n";
if (isset($result['code'])) {
    switch ($result['code']) {
        case 'POST01':
            echo "结果: 能正常派送，无偏远\n";
            break;
        case 'POST02':
            echo "结果: 偏远地区，无法派送\n";
            break;
        case 'POST03':
            echo "结果: 偏远地区，可以派送，需收取偏远费\n";
            echo "偏远费: " . ($result['obj'] ?? 0) . " USD\n";
            break;
        default:
            echo "结果: " . ($result['msg'] ?? '未知状态') . "\n";
    }
} else {
    echo "错误信息: " . ($result['msg'] ?? $result['obj'] ?? '未知错误') . "\n";
}


/**
 * Array
 * (
 * [code] => POST03
 * [flag] => 1
 * [msg] => 偏远能派送，需收偏远费
 * [obj] => 60
 * [rows] => Array
 * (
 * )
 *
 * [total] => 0
 * )
 * === 偏远地区检查结果 ===
 * 状态: 成功
 * 结果: 偏远地区，可以派送，需收取偏远费
 * 偏远费: 60 USD
 *
 */