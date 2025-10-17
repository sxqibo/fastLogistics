<?php

/**
 * GoodCang API 示例 - 获取仓库信息
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
    
    echo "=== GoodCang API 获取仓库信息测试 ===\n";
    echo "App Token: " . $appToken . "\n";
    echo "App Key: " . $appKey . "\n\n";
    
    // 调用API获取仓库信息
    echo "正在调用API获取仓库信息...\n";
    $result = $goodCang->getWarehouse();
    
    echo "API调用成功！\n";
    echo "响应数据:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    // 解析并显示仓库信息
    if (isset($result['ask']) && $result['ask'] === 'Success' && isset($result['data'])) {
        echo "\n=== 仓库信息解析 ===\n";
        echo "仓库总数: " . count($result['data']) . "\n\n";
        
        foreach ($result['data'] as $index => $warehouse) {
            echo "仓库 " . ($index + 1) . ":\n";
            echo "  区域仓代码: " . ($warehouse['warehouse_code'] ?? 'N/A') . "\n";
            echo "  仓库名称: " . ($warehouse['warehouse_name'] ?? 'N/A') . "\n";
            echo "  所在国家/地区: " . ($warehouse['country_code'] ?? 'N/A') . "\n";
            
            if (isset($warehouse['wp_list']) && is_array($warehouse['wp_list'])) {
                echo "  物理仓数量: " . count($warehouse['wp_list']) . "\n";
                
                foreach ($warehouse['wp_list'] as $wpIndex => $wp) {
                    echo "    物理仓 " . ($wpIndex + 1) . ":\n";
                    echo "      物理仓编码: " . ($wp['code'] ?? 'N/A') . "\n";
                    echo "      物理仓名称: " . ($wp['name'] ?? 'N/A') . "\n";
                    
                    if (isset($wp['address']) && is_array($wp['address'])) {
                        $address = $wp['address'];
                        echo "      地址信息:\n";
                        echo "        门牌号: " . ($address['street_number'] ?? 'N/A') . "\n";
                        echo "        地址1: " . ($address['street_address1'] ?? 'N/A') . "\n";
                        echo "        地址2: " . ($address['street_address2'] ?? 'N/A') . "\n";
                        echo "        城市: " . ($address['city'] ?? 'N/A') . "\n";
                        echo "        州/省份: " . ($address['state'] ?? 'N/A') . "\n";
                        echo "        邮编: " . ($address['postcode'] ?? 'N/A') . "\n";
                        echo "        联系人: " . ($address['contacter'] ?? 'N/A') . "\n";
                        echo "        电话: " . ($address['phone'] ?? 'N/A') . "\n";
                    }
                    echo "\n";
                }
            } else {
                echo "  物理仓信息: 无\n";
            }
            echo "\n";
        }
        
        // 生成仓库代码列表，方便其他API调用时使用
        echo "=== 可用仓库代码列表 ===\n";
        $warehouseCodes = [];
        foreach ($result['data'] as $warehouse) {
            $warehouseCodes[] = $warehouse['warehouse_code'] ?? 'N/A';
        }
        echo "区域仓代码: " . implode(', ', $warehouseCodes) . "\n";
        
        // 生成物理仓代码列表
        $physicalWarehouseCodes = [];
        foreach ($result['data'] as $warehouse) {
            if (isset($warehouse['wp_list']) && is_array($warehouse['wp_list'])) {
                foreach ($warehouse['wp_list'] as $wp) {
                    $physicalWarehouseCodes[] = $wp['code'] ?? 'N/A';
                }
            }
        }
        echo "物理仓代码: " . implode(', ', $physicalWarehouseCodes) . "\n";
        
    } else {
        echo "获取仓库信息失败或返回数据格式异常\n";
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
