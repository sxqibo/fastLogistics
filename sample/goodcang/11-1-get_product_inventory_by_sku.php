<?php

/**
 * GoodCang API 示例 - 根据SKU获取产品库存
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
    /** @var GoodCang $goodCang */
    $goodCang = new GoodCang($appToken, $appKey);

    echo "=== GoodCang API 根据SKU获取产品库存测试 ===\n";
    echo "App Token: " . $appToken . "\n";
    echo "App Key: " . $appKey . "\n\n";

    // 设置查询参数（必填参数：page, pageSize）
    $params = [
        // 必填参数
        'page' => 1,                                     // 当前页
        'pageSize' => 20,                                // 每页数据长度（最大200）

        // 查询条件
        'product_sku' => 'XXX',        // 商品SKU（精确匹配）
        // 'product_sku_arr' => [],                      // 商品SKU数组（最多200个）
        // 'warehouse_code' => 'USEA',                   // 仓库代码
        // 'warehouse_code_arr' => [],                    // 区域仓编码数组
    ];

    echo "请求参数:\n";
    echo json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 调用API获取产品库存
    echo "正在调用API获取产品库存...\n";
    $result = $goodCang->getProductInventory($params);

    echo "API调用成功！\n";
    echo "响应数据:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 检查响应状态（V1版本API，响应结构为ask, message, count, data）
    if (!isset($result['ask'])) {
        echo "警告: 响应数据缺少 ask 字段\n";
        exit(1);
    }

    // 显示响应基本信息
    echo "=== 响应状态 ===\n";
    echo "状态: " . ($result['ask'] ?? 'N/A') . "\n";
    echo "消息: " . ($result['message'] ?? 'N/A') . "\n";
    
    // 显示额外的响应字段（如果存在）
    if (isset($result['http_code'])) {
        echo "HTTP状态码: " . $result['http_code'] . "\n";
    }
    if (isset($result['error_id']) && !empty($result['error_id'])) {
        echo "错误ID: " . $result['error_id'] . "\n";
    }
    if (isset($result['errors']) && is_array($result['errors']) && !empty($result['errors'])) {
        echo "错误列表: " . json_encode($result['errors'], JSON_UNESCAPED_UNICODE) . "\n";
    }
    if (isset($result['cached_time'])) {
        echo "缓存时间: " . ($result['cached_time'] ?? '无') . "\n";
    }
    echo "\n";

    // 解析并显示库存信息
    if (isset($result['ask']) && $result['ask'] === 'Success' && isset($result['data']) && is_array($result['data'])) {
        echo "=== 库存信息解析 ===\n";
        
        // 显示总数
        if (isset($result['count'])) {
            echo "总记录数: " . $result['count'] . "\n";
        }

        // 显示库存列表
        $inventoryList = $result['data'];
        echo "库存记录数量: " . count($inventoryList) . "\n\n";

        // 筛选德国仓库的库存
        $germanyInventory = [];
        foreach ($inventoryList as $inventory) {
            $warehouseCode = $inventory['warehouse_code'] ?? '';
            $warehouseDesc = $inventory['warehouse_desc'] ?? '';
            
            // 判断是否为德国仓库（通过仓库代码或描述）
            $isGermany = false;
            if (!empty($warehouseCode)) {
                // 仓库代码以DE开头或包含DE
                if (stripos($warehouseCode, 'DE') === 0 || stripos($warehouseCode, 'DE') !== false) {
                    $isGermany = true;
                }
            }
            if (!$isGermany && !empty($warehouseDesc)) {
                // 仓库描述包含德国、Germany等关键词
                $germanyKeywords = ['德国', 'Germany', 'Deutschland', 'DE'];
                foreach ($germanyKeywords as $keyword) {
                    if (stripos($warehouseDesc, $keyword) !== false) {
                        $isGermany = true;
                        break;
                    }
                }
            }
            
            if ($isGermany) {
                $germanyInventory[] = $inventory;
            }
        }

        // 显示德国仓库的良品可售数量
        if (!empty($germanyInventory)) {
            echo "=== 德国仓库良品可售数量 ===\n";
            $totalSellable = 0;
            foreach ($germanyInventory as $index => $inventory) {
                $warehouseCode = $inventory['warehouse_code'] ?? 'N/A';
                $warehouseDesc = $inventory['warehouse_desc'] ?? 'N/A';
                $sellable = isset($inventory['sellable']) ? (int)$inventory['sellable'] : 0;
                $totalSellable += $sellable;
                
                echo "  仓库 " . ($index + 1) . ":\n";
                echo "    仓库代码: " . $warehouseCode . "\n";
                echo "    仓库描述: " . $warehouseDesc . "\n";
                echo "    良品可售数量: " . $sellable . "\n";
                echo "\n";
            }
            echo "德国仓库良品可售总数量: " . $totalSellable . "\n\n";
        } else {
            echo "=== 德国仓库良品可售数量 ===\n";
            echo "未找到德国仓库的库存记录\n\n";
        }
        
        foreach ($inventoryList as $index => $inventory) {
            echo "库存记录 " . ($index + 1) . ":\n";
            
            // 基本信息
            echo "  商品SKU: " . ($inventory['product_sku'] ?? 'N/A') . "\n";
            if (isset($inventory['product_title']) && $inventory['product_title'] !== '') {
                echo "  商品名称: " . $inventory['product_title'] . "\n";
            }
            if (isset($inventory['warehouse_code']) && $inventory['warehouse_code'] !== '') {
                echo "  仓库代码: " . $inventory['warehouse_code'] . "\n";
            }
            if (isset($inventory['warehouse_desc']) && $inventory['warehouse_desc'] !== '') {
                echo "  仓库描述: " . $inventory['warehouse_desc'] . "\n";
            }
            
            // 库存数量信息
            echo "  === 库存数量 ===\n";
            if (isset($inventory['sellable'])) {
                echo "  良品可售数量: " . $inventory['sellable'] . "\n";
            }
            if (isset($inventory['reserved'])) {
                echo "  良品待出库数量: " . $inventory['reserved'] . "\n";
            }
            if (isset($inventory['shipped'])) {
                echo "  良品已出库数量: " . $inventory['shipped'] . "\n";
            }
            if (isset($inventory['pending'])) {
                echo "  待上架数量: " . $inventory['pending'] . "\n";
            }
            if (isset($inventory['stocking'])) {
                echo "  备货数量: " . $inventory['stocking'] . "\n";
            }
            
            // 在途数量
            echo "  === 在途数量 ===\n";
            if (isset($inventory['onway'])) {
                echo "  海外在途数量: " . $inventory['onway'] . "\n";
            }
            if (isset($inventory['transfer_onway'])) {
                echo "  发货在途数量: " . $inventory['transfer_onway'] . "\n";
            }
            if (isset($inventory['total_onway'])) {
                echo "  总在途数量: " . $inventory['total_onway'] . "\n";
            }
            
            // 不良品数量
            echo "  === 不良品数量 ===\n";
            if (isset($inventory['unsellable'])) {
                echo "  不良品可售数量: " . $inventory['unsellable'] . "\n";
            }
            if (isset($inventory['pi_unsellable_reserved'])) {
                echo "  不良品待出库数量: " . $inventory['pi_unsellable_reserved'] . "\n";
            }
            if (isset($inventory['pi_unsellable_shipped'])) {
                echo "  不良品已出库数量: " . $inventory['pi_unsellable_shipped'] . "\n";
            }
            
            // 其他状态
            echo "  === 其他状态 ===\n";
            if (isset($inventory['pi_freeze'])) {
                echo "  冻结数量: " . $inventory['pi_freeze'] . "\n";
            }
            if (isset($inventory['pi_no_stock'])) {
                echo "  缺货数量: " . $inventory['pi_no_stock'] . "\n";
            }
            if (isset($inventory['pi_warning_qty'])) {
                echo "  预警库存数量(可售): " . $inventory['pi_warning_qty'] . "\n";
            }
            
            // 商品冻结状态
            if (isset($inventory['product_freeze_status']) && $inventory['product_freeze_status'] !== '') {
                echo "  商品冻结状态: " . $inventory['product_freeze_status'];
                if (isset($inventory['product_freeze_status_text']) && $inventory['product_freeze_status_text'] !== '') {
                    echo " (" . $inventory['product_freeze_status_text'] . ")";
                }
                echo "\n";
            }
            
            echo "\n";
        }

        // 最后再次显示德国仓库良品可售数量汇总
        if (!empty($germanyInventory)) {
            $totalSellable = 0;
            foreach ($germanyInventory as $inventory) {
                $sellable = isset($inventory['sellable']) ? (int)$inventory['sellable'] : 0;
                $totalSellable += $sellable;
            }
            echo "========================================\n";
            echo "【汇总】德国仓库良品可售总数量: " . $totalSellable . "\n";
            echo "========================================\n";
        }
    } else {
        echo "=== 错误信息 ===\n";
        echo "获取产品库存失败或返回数据格式异常\n";
        if (isset($result['ask'])) {
            echo "状态: " . $result['ask'] . "\n";
        }
        if (isset($result['message'])) {
            echo "错误消息: " . $result['message'] . "\n";
        }
        if (isset($result['errors']) && is_array($result['errors']) && !empty($result['errors'])) {
            echo "详细错误:\n";
            foreach ($result['errors'] as $error) {
                if (is_array($error)) {
                    echo "  - " . json_encode($error, JSON_UNESCAPED_UNICODE) . "\n";
                } else {
                    echo "  - " . $error . "\n";
                }
            }
        }
        if (!isset($result['data'])) {
            echo "注意: 响应数据中缺少data字段\n";
        }
    }

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行数: " . $e->getLine() . "\n";
}
