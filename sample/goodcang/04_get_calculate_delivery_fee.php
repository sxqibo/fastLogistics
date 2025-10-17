<?php

/**
 * GoodCang API 示例 - 运费试算
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
    
    echo "=== GoodCang API 运费试算测试 ===\n";
    echo "App Token: " . $appToken . "\n";
    echo "App Key: " . $appKey . "\n\n";
    
    // 设置运费试算参数
    $params = [
        'city' => 'Fossano',
        'is_insurance_service' => '1',
        'is_sign_server' => '1',
        'length' => 102,
        'postcode' => '12045',
        'weight' => 45,
        'country_code' => 'IT',
        'width' => 86,
        'state' => 'Cuneo',
        'height' => 86,
        // 可选参数
        'warehouse_code' => 'DE', // 仓库代码，如果不填则使用默认仓库
        'sm_code' => '', // 物流产品代码，如果不填则返回所有适用产品
        'property_label' => '', // 平台模式
        'sku' => [], // 商品编码数组
        'insurance_amount' => 0, // 保险金额
        'is_residential' => '1' // 是否住宅地址
    ];
    
    echo "运费试算参数:\n";
    echo json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // 调用API进行运费试算
    echo "正在调用API进行运费试算...\n";
    $result = $goodCang->getCalculateDeliveryFee($params);
    
    echo "API调用成功！\n";
    echo "响应数据:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    // 解析并显示费用信息
    if (isset($result['ask']) && $result['ask'] === 'Success' && isset($result['data'])) {
        echo "\n=== 费用试算结果解析 ===\n";
        echo "币种: " . ($result['currency'] ?? 'N/A') . "\n";
        echo "物流产品数量: " . count($result['data']) . "\n\n";
        
        foreach ($result['data'] as $index => $item) {
            echo "物流产品 " . ($index + 1) . ":\n";
            echo "  产品代码: " . ($item['sm_code'] ?? 'N/A') . "\n";
            echo "  产品名称(中文): " . ($item['sm_name_cn'] ?? 'N/A') . "\n";
            echo "  产品名称(英文): " . ($item['sm_name'] ?? 'N/A') . "\n";
            echo "  最快时效: " . ($item['sm_delivery_time_min'] ?? 'N/A') . " 天\n";
            echo "  最慢时效: " . ($item['sm_delivery_time_max'] ?? 'N/A') . " 天\n";
            echo "  总费用: " . ($item['total'] ?? 'N/A') . " " . ($result['currency'] ?? 'USD') . "\n";
            
            if (isset($item['income']) && is_array($item['income'])) {
                echo "  费用明细:\n";
                foreach ($item['income'] as $fee) {
                    echo "    - " . ($fee['name'] ?? 'N/A') . ": " . ($fee['amount'] ?? 'N/A') . " " . ($result['currency'] ?? 'USD') . "\n";
                }
            }
            echo "\n";
        }
    } else {
        echo "运费试算失败或返回数据格式异常\n";
        if (isset($result['message'])) {
            echo "错误信息: " . $result['message'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行数: " . $e->getLine() . "\n";
}
