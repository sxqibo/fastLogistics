<?php

/**
 * GoodCang API 示例 - 获取库龄列表
 * 
 * 使用方法：
 * php 02_getInventoryAgeList.php
 */

// 引入配置文件
$config = require_once __DIR__.'/config.php';

// 引入GoodCang类
require_once __DIR__.'/../../vendor/autoload.php';

use Sxqibo\Logistics\GoodCang;

$appToken = $config['goodcang']['app_token'];
$appKey  = $config['goodcang']['app_key'];

try {
    // 创建GoodCang实例
    $goodCang = new GoodCang($appToken, $appKey);
    
    echo "=== GoodCang API 获取库龄列表测试 ===\n";
    echo "App Token: " . $appToken . "\n";
    echo "App Key: " . $appKey . "\n\n";
    
    // 设置查询参数
    $params = [
        'page' => 1,
        'page_size' => 10
    ];
    
    echo "查询参数:\n";
    echo json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // 调用API获取库龄列表
    echo "正在调用API...\n";
    $result = $goodCang->getInventoryAgeList($params);
    
    echo "API调用成功！\n";
    echo "响应数据:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行数: " . $e->getLine() . "\n";
}


// 响应数据:
// {
//     "code": 0,
//     "message": "success",
//     "errors": [],
//     "data": {
//         "total": 280,
//         "list": [
//             {
//                 "iba_id": 1,
//                 "warehouse_code": "UK",
//                 "product_sku": "XXX",
//                 "product_barcode": "G9407-XXX",
//                 "iba_quantity": 8,
//                 "iba_fifo_time": "2025-03-11",
//                 "iba_warning_age": 0,
//                 "product_title": "XXX",
//                 "product_title_en": "XX",
//                 "warehouse_desc": "英国区",
//                 "warehouse_age": 171,
//                 "expiration_date": ""
//             }
//         ]
//     },
//     "http_code": 200,
//     "error_id": "P4ZJ8R",
//     "cached_time": null
// }