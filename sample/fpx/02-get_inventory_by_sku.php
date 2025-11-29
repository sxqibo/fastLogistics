<?php
/**
 * 递四方 - 根据SKU获取库存信息
 */

use Sxqibo\Logistics\Fpx;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/config.php';

try {
    // 初始化递四方客户端
    $fpx = new Fpx(
        $fpxConfig['app_key'],
        $fpxConfig['app_secret'],
        $fpxConfig['environment']
    );

    // 设置访问令牌（如果需要）
    if (!empty($fpxConfig['access_token'])) {
        $fpx->setAccessToken($fpxConfig['access_token']);
    }

    echo "=== 递四方 - 根据SKU获取库存信息 ===\n\n";

    // 设置查询参数
    $skuCode = 'S-HYTAO-CHA';           // SKU编号（请根据实际情况修改）
    $customerCode = '';                 // 客户操作账号（可选，根据接口文档不是必传）
    $warehouseCode = '';                // 仓库代码（可选，留空则查询所有仓库）
    $pageNo = 1;                        // 页码
    $pageSize = 50;                     // 每页记录数（最大500）

    echo "查询参数:\n";
    echo "  SKU编号: {$skuCode}\n";
    echo "  客户操作账号: " . ($customerCode ?: '未指定（查询所有账号）') . "\n";
    echo "  仓库代码: " . ($warehouseCode ?: '全部仓库') . "\n";
    echo "  页码: {$pageNo}\n";
    echo "  每页记录数: {$pageSize}\n\n";

    // 根据SKU查询库存
    echo "正在调用API查询库存...\n";
    $result = $fpx->getInventoryBySku(
        $skuCode,           // SKU编号
        $customerCode,      // 客户操作账号（可选）
        $warehouseCode,     // 仓库代码（可选）
        $pageNo,            // 页码
        $pageSize           // 每页记录数
    );

    echo "API调用成功！\n\n";

    // 显示原始响应数据
    echo "=== 原始响应数据 ===\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 解析并显示库存信息
    // 注意：result为"1"表示成功，"0"表示失败
    if (isset($result['result']) && ($result['result'] === '1' || $result['result'] === 'success') && isset($result['data'])) {
        echo "=== 库存信息解析 ===\n";
        
        $inventoryData = $result['data'];
        
        // 显示分页信息
        if (isset($inventoryData['page_no'])) {
            echo "当前页码: " . $inventoryData['page_no'] . "\n";
        }
        if (isset($inventoryData['page_size'])) {
            echo "每页记录数: " . $inventoryData['page_size'] . "\n";
        }
        if (isset($inventoryData['total'])) {
            echo "总记录数: " . $inventoryData['total'] . "\n";
        }
        echo "\n";

        // 显示库存列表
        $inventoryList = isset($inventoryData['data']) ? $inventoryData['data'] : [];
        if (!empty($inventoryList)) {
            echo "库存记录数量: " . count($inventoryList) . "\n\n";
            
            foreach ($inventoryList as $index => $inventory) {
                echo "库存记录 " . ($index + 1) . ":\n";
                
                // 基本信息
                if (isset($inventory['customer_code'])) {
                    echo "  客户操作账号: " . $inventory['customer_code'] . "\n";
                }
                if (isset($inventory['sku_code'])) {
                    echo "  SKU编号: " . $inventory['sku_code'] . "\n";
                }
                if (isset($inventory['sku_id'])) {
                    echo "  SKU ID: " . $inventory['sku_id'] . "\n";
                }
                if (isset($inventory['warehouse_code'])) {
                    echo "  仓库代码: " . $inventory['warehouse_code'] . "\n";
                }
                if (isset($inventory['batch_no'])) {
                    echo "  批次号: " . $inventory['batch_no'] . "\n";
                }
                if (isset($inventory['stock_quality'])) {
                    echo "  库存质量: " . $inventory['stock_quality'] . "\n";
                }
                
                // 库存数量信息
                echo "  === 库存数量 ===\n";
                if (isset($inventory['available_stock'])) {
                    echo "  可用库存: " . $inventory['available_stock'] . "\n";
                }
                if (isset($inventory['pending_stock'])) {
                    echo "  待出库库存: " . $inventory['pending_stock'] . "\n";
                }
                if (isset($inventory['onway_stock'])) {
                    echo "  在途库存: " . $inventory['onway_stock'] . "\n";
                }
                
                // 计算总库存
                $availableStock = isset($inventory['available_stock']) ? (int)$inventory['available_stock'] : 0;
                $pendingStock = isset($inventory['pending_stock']) ? (int)$inventory['pending_stock'] : 0;
                $onwayStock = isset($inventory['onway_stock']) ? (int)$inventory['onway_stock'] : 0;
                $totalStock = $availableStock + $pendingStock + $onwayStock;
                echo "  总库存: " . $totalStock . "\n";
                
                echo "\n";
            }

            // 格式化数据示例
            echo "=== 格式化后的库存数据 ===\n";
            $formattedData = $fpx->formatInventoryData($result);
            echo json_encode($formattedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

        } else {
            echo "未找到库存记录\n";
            echo "提示: SKU '{$skuCode}' 在当前条件下没有库存数据\n";
        }
    } else {
        echo "=== 错误信息 ===\n";
        echo "获取库存失败或返回数据格式异常\n";
        if (isset($result['result'])) {
            $resultStatus = $result['result'];
            echo "结果状态: " . $resultStatus . "\n";
            // result为"0"表示失败，"1"表示成功
            if ($resultStatus === '0') {
                echo "API调用失败\n";
            }
        }
        if (isset($result['msg'])) {
            echo "消息: " . $result['msg'] . "\n";
        }
        if (!isset($result['data'])) {
            echo "注意: 响应数据中缺少data字段\n";
        }
    }

    // ========================================
    // 示例2：查询指定仓库的有效库存
    // ========================================
    echo "\n\n";
    echo "========================================\n";
    echo "=== 示例2：查询指定仓库的有效库存 ===\n";
    echo "========================================\n\n";

    // 设置查询参数
    $skuCode2 = 'S-HYTAO-CHA';          // SKU编号
    $warehouseCode2 = 'DEFRAA';         // 指定仓库代码
    $customerCode2 = '';                // 客户操作账号（可选）
    $pageNo2 = 1;                       // 页码
    $pageSize2 = 50;                    // 每页记录数

    echo "查询参数:\n";
    echo "  SKU编号: {$skuCode2}\n";
    echo "  仓库代码: {$warehouseCode2}\n";
    echo "  客户操作账号: " . ($customerCode2 ?: '未指定（查询所有账号）') . "\n";
    echo "  页码: {$pageNo2}\n";
    echo "  每页记录数: {$pageSize2}\n\n";

    // 根据SKU和仓库代码查询库存
    echo "正在调用API查询指定仓库的库存...\n";
    $result2 = $fpx->getInventoryBySku(
        $skuCode2,          // SKU编号
        $customerCode2,      // 客户操作账号（可选）
        $warehouseCode2,     // 仓库代码
        $pageNo2,           // 页码
        $pageSize2          // 每页记录数
    );

    echo "API调用成功！\n\n";

    // 解析并显示指定仓库的有效库存
    if (isset($result2['result']) && ($result2['result'] === '1' || $result2['result'] === 'success') && isset($result2['data'])) {
        $inventoryData2 = $result2['data'];
        $inventoryList2 = isset($inventoryData2['data']) ? $inventoryData2['data'] : [];
        
        echo "=== 仓库 [{$warehouseCode2}] 的有效库存 ===\n";
        
        if (!empty($inventoryList2)) {
            $totalAvailableStock = 0;
            $foundWarehouse = false;
            
            foreach ($inventoryList2 as $index => $inventory) {
                $warehouseCode = $inventory['warehouse_code'] ?? '';
                
                // 只显示指定仓库的记录
                if ($warehouseCode === $warehouseCode2) {
                    $foundWarehouse = true;
                    $availableStock = isset($inventory['available_stock']) ? (int)$inventory['available_stock'] : 0;
                    $totalAvailableStock += $availableStock;
                    
                    echo "记录 " . ($index + 1) . ":\n";
                    echo "  SKU编号: " . ($inventory['sku_code'] ?? 'N/A') . "\n";
                    echo "  仓库代码: " . $warehouseCode . "\n";
                    if (isset($inventory['batch_no'])) {
                        echo "  批次号: " . $inventory['batch_no'] . "\n";
                    }
                    if (isset($inventory['stock_quality'])) {
                        echo "  库存质量: " . $inventory['stock_quality'] . "\n";
                    }
                    echo "  有效库存: " . $availableStock . "\n";
                    echo "\n";
                }
            }
            
            if ($foundWarehouse) {
                echo "----------------------------------------\n";
                echo "仓库 [{$warehouseCode2}] 的有效库存总计: {$totalAvailableStock}\n";
                echo "----------------------------------------\n";
            } else {
                echo "未找到仓库 [{$warehouseCode2}] 的库存记录\n";
            }
        } else {
            echo "未找到库存记录\n";
        }
    } else {
        echo "=== 错误信息 ===\n";
        echo "获取库存失败\n";
        if (isset($result2['result'])) {
            echo "结果状态: " . $result2['result'] . "\n";
        }
        if (isset($result2['msg'])) {
            echo "消息: " . $result2['msg'] . "\n";
        }
    }

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行数: " . $e->getLine() . "\n";
}
