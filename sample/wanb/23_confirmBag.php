<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Wanb;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Wanb($config);

// 读取之前保存的袋号
$bagCode = @file_get_contents(__DIR__ . '/last_bag_code.txt');
if (!$bagCode) {
    die("请先创建来货/揽收袋并获取袋号！\n");
}

// 确认交运来货/揽收袋
$result = $app->confirmBag($bagCode);
print_r($result);

/**
 * 成功返回示例：
 * Array
 * (
 *     [Code] => 0
 *     [Message] => success
 *     [Data] => Array
 *         (
 *             [BagCode] => SBXBAA0000038638YQ
 *             [Status] => Confirmed
 *             [UpdateTime] => 2024-01-29 16:35:00
 *         )
 * )
 *
 * 注意：
 * 1. 确认交运后，袋子信息将不能再修改
 * 2. 确认交运前，请确保所有信息都已正确填写
 */ 