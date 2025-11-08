<?php

/**
 * GoodCang API 示例 - 获取公司账户
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
    
    echo "=== GoodCang API 获取公司账户测试 ===\n";
    echo "App Token: " . $appToken . "\n";
    echo "App Key: " . $appKey . "\n\n";
    
    // 调用API获取公司账户
    echo "正在调用API获取公司账户列表...\n";
    $result = $goodCang->getAccountList();
    
    echo "API调用成功！\n";
    echo "响应数据:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    // 解析并显示账户信息
    if (isset($result['ask']) && $result['ask'] === 'Success' && isset($result['data'])) {
        echo "\n=== 公司账户解析 ===\n";
        $data = $result['data'];
        echo "客户代码: " . ($data['customer_code'] ?? 'N/A') . "\n";
        
        if (isset($data['account_list']) && is_array($data['account_list'])) {
            echo "账户数量: " . count($data['account_list']) . "\n\n";
            
            foreach ($data['account_list'] as $index => $account) {
                echo "账户 " . ($index + 1) . ":\n";
                echo "  账户编号: " . ($account['account_code'] ?? 'N/A') . "\n";
                echo "  签约主体名称: " . ($account['firm_name'] ?? 'N/A') . "\n";
                echo "  服务主体名称: " . ($account['server_firm_name'] ?? 'N/A') . "\n";
                echo "  签约主体状态: " . ($account['firm_status'] ?? 'N/A') . "\n";
                
                if (isset($account['business_type_list']) && is_array($account['business_type_list'])) {
                    echo "  签约业务类型: " . (empty($account['business_type_list']) ? '无' : implode(', ', $account['business_type_list'])) . "\n";
                }
                
                if (isset($account['balance_list']) && is_array($account['balance_list'])) {
                    echo "  账户余额信息:\n";
                    foreach ($account['balance_list'] as $balance) {
                        echo "    币种: " . ($balance['currency_code'] ?? 'N/A') . "\n";
                        echo "    金额: " . ($balance['amount'] ?? 'N/A') . "\n";
                    }
                }
                echo "\n";
            }
        } else {
            echo "未返回账户列表数据\n";
        }
    } else {
        echo "获取公司账户失败或返回数据格式异常\n";
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


/**
 * 
 */