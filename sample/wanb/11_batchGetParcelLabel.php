<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Wanb;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Wanb($config);

// 读取之前保存的处理号
$processCode = @file_get_contents(__DIR__ . '/last_process_code.txt');
if (!$processCode) {
    die("请先创建包裹并获取处理号！\n");
}

// 批量获取包裹标签参数
$params = [
    'ProcessCodes' => [
        $processCode
        // 可以添加更多处理号
    ],
    'LabelFormat' => 'PDF',      // 标签格式：PDF/PNG
    'LabelSize' => '100x100',    // 标签尺寸
    'LabelType' => 1             // 标签类型：1-标准标签，2-自定义标签
];

// 批量获取包裹标签
$result = $app->batchGetParcelLabel($params);
print_r($result);

/**
 * 成功返回示例：
 * Array
 * (
 *     [Code] => 0
 *     [Message] => success
 *     [Data] => Array
 *         (
 *             [SuccessCount] => 1
 *             [FailCount] => 0
 *             [Details] => Array
 *                 (
 *                     [0] => Array
 *                         (
 *                             [ProcessCode] => SBXPAA0000038638YQ
 *                             [Success] => true
 *                             [Message] => success
 *                             [LabelUrl] => http://api-sbx.wanbexpress.com/labels/SBXPAA0000038638YQ.pdf
 *                             [LabelFormat] => PDF
 *                         )
 *                 )
 *         )
 * )
 */ 