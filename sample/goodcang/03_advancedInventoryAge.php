<?php

/**
 * GoodCang API 示例 - 库龄列表高级用法
 * 
 * 使用方法：
 * php 03_advancedInventoryAge.php
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
    
    echo "=== GoodCang API 库龄列表高级用法示例 ===\n\n";
    
    // 示例1：查询特定库龄范围
    echo "示例1：查询特定库龄范围\n";
    echo "----------------------------------------\n";
    $params1 = [
        'page' => 1,
        'page_size' => 5,
        'age_from' => 100,
        'age_to' => 200
    ];
    
    echo "参数: " . json_encode($params1, JSON_UNESCAPED_UNICODE) . "\n";
    $result1 = $goodCang->getInventoryAgeList($params1);
    echo "结果: 找到 " . $result1['data']['total'] . " 条记录\n";
    echo "第一页数据: " . count($result1['data']['list']) . " 条\n\n";
    
    // 示例2：查询特定仓库的库龄信息
    echo "示例2：查询特定仓库的库龄信息\n";
    echo "----------------------------------------\n";
    $params2 = [
        'page' => 1,
        'page_size' => 10,
        'warehouse_code' => 'UK'
    ];
    
    echo "参数: " . json_encode($params2, JSON_UNESCAPED_UNICODE) . "\n";
    $result2 = $goodCang->getInventoryAgeList($params2);
    echo "结果: 找到 " . $result2['data']['total'] . " 条记录\n";
    
    if (!empty($result2['data']['list'])) {
        echo "第一个商品: " . $result2['data']['list'][0]['product_title'] . "\n";
        echo "库龄: " . $result2['data']['list'][0]['warehouse_age'] . " 天\n";
        echo "库存: " . $result2['data']['list'][0]['iba_quantity'] . "\n";
    }
    echo "\n";
    
    // 示例3：查询特定时间范围上架的商品
    echo "示例3：查询特定时间范围上架的商品\n";
    echo "----------------------------------------\n";
    $params3 = [
        'page' => 1,
        'page_size' => 5,
        'fifo_time_from' => '2025-03-20 00:00:00',
        'fifo_time_to' => '2025-03-25 23:59:59'
    ];
    
    echo "参数: " . json_encode($params3, JSON_UNESCAPED_UNICODE) . "\n";
    $result3 = $goodCang->getInventoryAgeList($params3);
    echo "结果: 找到 " . $result3['data']['total'] . " 条记录\n\n";
    
    // 示例4：分页查询
    echo "示例4：分页查询\n";
    echo "----------------------------------------\n";
    $params4 = [
        'page' => 2,
        'page_size' => 3
    ];
    
    echo "参数: " . json_encode($params4, JSON_UNESCAPED_UNICODE) . "\n";
    $result4 = $goodCang->getInventoryAgeList($params4);
    echo "结果: 第2页数据，共 " . count($result4['data']['list']) . " 条\n";
    
    if (!empty($result4['data']['list'])) {
        foreach ($result4['data']['list'] as $index => $item) {
            echo "  " . ($index + 1) . ". " . $item['product_title'] . " (库龄: " . $item['warehouse_age'] . "天)\n";
        }
    }
    echo "\n";
    
    echo "所有示例执行完成！\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行数: " . $e->getLine() . "\n";
}
