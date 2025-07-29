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

// 删除来货/揽收袋
$result = $app->deleteBag($bagCode);
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
 *             [Status] => Deleted
 *             [UpdateTime] => 2024-01-29 16:40:00
 *         )
 * )
 *
 * 注意：
 * 1. 只能删除未确认交运的袋子
 * 2. 删除后的袋子不能恢复
 */

// 删除成功后，删除保存的袋号文件
if ($result['Code'] === 0) {
    @unlink(__DIR__ . '/last_bag_code.txt');
} 