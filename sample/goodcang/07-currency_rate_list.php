<?php

/**
 * GoodCang API 示例 - 获取货币列表
 */

// 引入配置文件
$config = require_once __DIR__ . '/config.php';

// 引入GoodCang类
require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\GoodCang;

$appToken = $config['goodcang']['app_token'];
$appKey  = $config['goodcang']['app_key'];

try {
    // 创建GoodCang实例
    $goodCang = new GoodCang($appToken, $appKey);

    echo "=== GoodCang API 获取货币列表测试 ===\n";
    echo "App Token: " . $appToken . "\n";
    echo "App Key: " . $appKey . "\n\n";

    // 调用API获取货币列表
    echo "正在调用API获取货币列表...\n";
    $result = $goodCang->getCurrencyRateList();

    echo "API调用成功！\n";
    echo "响应数据:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

    // 解析货币列表
    if (isset($result['code']) && $result['code'] === 0 && isset($result['data']) && is_array($result['data'])) {
        echo "\n=== 货币列表解析 ===\n";
        echo "货币数量: " . count($result['data']) . "\n\n";

        foreach ($result['data'] as $index => $currency) {
            echo "货币 " . ($index + 1) . ":\n";
            echo "  货币缩写: " . ($currency['currency_code'] ?? 'N/A') . "\n";
            echo "  货币名称: " . ($currency['currency_name'] ?? 'N/A') . "\n";
            echo "  标识符: " . ($currency['symbol'] ?? 'N/A') . "\n\n";
        }
    } else {
        echo "获取货币列表失败或返回数据格式异常\n";
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
