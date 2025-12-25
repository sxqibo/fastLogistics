<?php
/**
 * 递四方 - 查询库存库龄
 * 查询库存库龄信息，包括上架时间、库龄、失效日期等
 * 支持查询全部库存库龄，也支持查询指定SKU的库龄
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

    echo "=== 递四方 - 查询全部库存库龄 ===\n\n";

    // 设置查询参数
    $customerCode = '900278';          // 客户操作账号（可选，留空则查询所有账号）
    $warehouseCode = '';                // 仓库代码（可选，留空则查询所有仓库）

    echo "查询参数:\n";
    echo "  客户操作账号: " . ($customerCode ?: '未指定（查询所有账号）') . "\n";
    echo "  仓库代码: " . ($warehouseCode ?: '全部仓库') . "\n\n";

    // 步骤1：先获取所有库存，提取所有SKU
    echo "步骤1：正在获取所有库存SKU列表...\n";
    $allSkus = [];
    $currentPage = 1;
    $pageSize = 500; // 每页最大500条
    $hasMore = true;

    while ($hasMore) {
        echo "  正在获取第 {$currentPage} 页库存数据...\n";
        $inventoryResult = $fpx->getAllInventory($customerCode, $warehouseCode, $currentPage, $pageSize);
        
        if (isset($inventoryResult['result']) && ($inventoryResult['result'] === '1' || $inventoryResult['result'] === 'success') 
            && isset($inventoryResult['data']['data']) && is_array($inventoryResult['data']['data'])) {
            
            $inventoryList = $inventoryResult['data']['data'];
            
            // 提取所有唯一的SKU
            foreach ($inventoryList as $item) {
                if (isset($item['sku_code']) && !empty($item['sku_code'])) {
                    $sku = $item['sku_code'];
                    if (!in_array($sku, $allSkus)) {
                        $allSkus[] = $sku;
                    }
                }
            }
            
            // 检查是否还有更多页
            $total = isset($inventoryResult['data']['total']) ? (int)$inventoryResult['data']['total'] : 0;
            $currentCount = count($inventoryList);
            $totalPages = ceil($total / $pageSize);
            
            echo "  第 {$currentPage} 页: 获取到 {$currentCount} 条记录，已提取 " . count($allSkus) . " 个唯一SKU\n";
            
            if ($currentPage >= $totalPages || $currentCount < $pageSize) {
                $hasMore = false;
            } else {
                $currentPage++;
            }
        } else {
            echo "  获取库存失败，停止获取SKU列表\n";
            if (isset($inventoryResult['msg'])) {
                echo "  错误信息: " . $inventoryResult['msg'] . "\n";
            }
            $hasMore = false;
        }
    }

    echo "\n总共获取到 " . count($allSkus) . " 个唯一SKU\n\n";

    if (empty($allSkus)) {
        echo "未找到任何SKU，无法查询库存库龄\n";
        exit(1);
    }

    // 步骤2：分批查询所有SKU的库存库龄（每批最多100个）
    echo "步骤2：正在分批查询所有SKU的库存库龄...\n";
    $allInventoryAgeDetails = [];
    $batchSize = 100; // 每批最多100个SKU
    $totalBatches = ceil(count($allSkus) / $batchSize);

    for ($batchIndex = 0; $batchIndex < $totalBatches; $batchIndex++) {
        $startIndex = $batchIndex * $batchSize;
        $batchSkus = array_slice($allSkus, $startIndex, $batchSize);
        $batchNumber = $batchIndex + 1;
        
        echo "  正在查询第 {$batchNumber}/{$totalBatches} 批（" . count($batchSkus) . " 个SKU）...\n";
        
        try {
            $ageResult = $fpx->getInventoryAge(
                $batchSkus,      // SKU编号数组
                $customerCode,   // 客户操作账号（可选）
                $warehouseCode    // 仓库代码（可选）
            );

            if (isset($ageResult['result']) && ($ageResult['result'] === '1' || $ageResult['result'] === 'success') 
                && isset($ageResult['data']['inventorydetaillist'])) {
                
                $details = $ageResult['data']['inventorydetaillist'];
                $allInventoryAgeDetails = array_merge($allInventoryAgeDetails, $details);
                echo "    成功获取 " . count($details) . " 条库存库龄记录\n";
            } else {
                echo "    第 {$batchNumber} 批查询失败\n";
                if (isset($ageResult['msg'])) {
                    echo "    错误信息: " . $ageResult['msg'] . "\n";
                }
                if (isset($ageResult['errors']) && is_array($ageResult['errors'])) {
                    echo "    详细错误:\n";
                    foreach ($ageResult['errors'] as $error) {
                        echo "      - 错误码: " . ($error['error_code'] ?? 'N/A') . "\n";
                        echo "        错误消息: " . ($error['error_msg'] ?? 'N/A') . "\n";
                        if (isset($error['error_data'])) {
                            echo "        错误数据: " . $error['error_data'] . "\n";
                        }
                    }
                }
                // 显示前3个SKU作为调试信息
                if (count($batchSkus) > 0) {
                    echo "    查询的SKU示例（前3个）: " . implode(', ', array_slice($batchSkus, 0, 3)) . "\n";
                }
                // 显示完整响应（仅前2批，避免输出过多）
                if ($batchNumber <= 2) {
                    echo "    完整响应: " . json_encode($ageResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                }
            }
        } catch (Exception $e) {
            echo "    第 {$batchNumber} 批查询异常: " . $e->getMessage() . "\n";
        }
    }

    echo "\n总共获取到 " . count($allInventoryAgeDetails) . " 条库存库龄记录\n\n";

    // 显示结果
    $result = [
        'result' => '1',
        'msg' => 'Success',
        'data' => [
            'inventorydetaillist' => $allInventoryAgeDetails
        ]
    ];

    // 显示原始响应数据（仅显示前几条作为示例）
    echo "=== 原始响应数据（前3条示例） ===\n";
    $sampleData = array_slice($allInventoryAgeDetails, 0, 3);
    echo json_encode(['result' => '1', 'msg' => 'Success', 'data' => ['inventorydetaillist' => $sampleData]], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    if (count($allInventoryAgeDetails) > 3) {
        echo "... (共 " . count($allInventoryAgeDetails) . " 条记录，仅显示前3条)\n";
    }
    echo "\n";

    // 解析并显示库存库龄信息
    // 注意：result为"1"表示成功，"0"表示失败
    if (isset($result['result']) && ($result['result'] === '1' || $result['result'] === 'success') && isset($result['data'])) {
        echo "=== 库存库龄信息解析 ===\n";
        
        $inventoryData = $result['data'];
        $inventoryDetailList = isset($inventoryData['inventorydetaillist']) ? $inventoryData['inventorydetaillist'] : [];
        
        if (count($inventoryDetailList) > 50) {
            echo "注意: 记录数量较多（" . count($inventoryDetailList) . " 条），仅显示前50条详细信息，其余仅显示统计信息\n\n";
        }
        
        if (!empty($inventoryDetailList)) {
            echo "库存详情记录数量: " . count($inventoryDetailList) . "\n\n";
            
            $displayLimit = 50; // 最多显示50条详细信息
            $displayCount = min(count($inventoryDetailList), $displayLimit);
            
            for ($index = 0; $index < $displayCount; $index++) {
                $detail = $inventoryDetailList[$index];
                echo "库存详情记录 " . ($index + 1) . ":\n";
                
                // 基本信息
                if (isset($detail['serial_no'])) {
                    echo "  序号: " . $detail['serial_no'] . "\n";
                }
                if (isset($detail['customer_code'])) {
                    echo "  客户代码: " . $detail['customer_code'] . "\n";
                }
                if (isset($detail['warehouse_code'])) {
                    echo "  仓库代码: " . $detail['warehouse_code'] . "\n";
                }
                if (isset($detail['sku_id'])) {
                    echo "  SKU ID（数字条码）: " . $detail['sku_id'] . "\n";
                }
                if (isset($detail['sku_code'])) {
                    echo "  SKU编码: " . $detail['sku_code'] . "\n";
                }
                if (isset($detail['batch_no'])) {
                    echo "  批次号: " . ($detail['batch_no'] ?: '无') . "\n";
                }
                if (isset($detail['stock_quality'])) {
                    echo "  库存质量: " . $detail['stock_quality'] . "\n";
                }
                if (isset($detail['consignment_no'])) {
                    echo "  委托单号: " . $detail['consignment_no'] . "\n";
                }
                
                // 库存数量
                if (isset($detail['warehouse_stock'])) {
                    echo "  仓库在库库存: " . $detail['warehouse_stock'] . "\n";
                }
                
                // 上架时间和库龄
                if (isset($detail['putaway_time'])) {
                    // putaway_time是long类型（毫秒时间戳），需要转换为日期时间
                    $putawayTimestamp = (int)$detail['putaway_time'];
                    // 如果是毫秒时间戳，转换为秒
                    if ($putawayTimestamp > 9999999999) {
                        $putawayTimestamp = $putawayTimestamp / 1000;
                    }
                    $putawayDate = date('Y-m-d H:i:s', $putawayTimestamp);
                    echo "  SKU上架时间: " . $putawayDate . " (原始值: " . $detail['putaway_time'] . ")\n";
                }
                
                if (isset($detail['inventory_age'])) {
                    echo "  SKU库龄/在库时间: " . $detail['inventory_age'] . " 天\n";
                }
                
                // 失效日期
                if (isset($detail['expiry_date'])) {
                    echo "  商品失效日期: " . ($detail['expiry_date'] ?: '无') . "\n";
                }
                
                echo "\n";
            }

            // 统计信息
            echo "=== 统计信息 ===\n";
            $totalStock = 0;
            $totalAge = 0;
            $warehouseCount = [];
            
            foreach ($inventoryDetailList as $detail) {
                // 统计总库存
                if (isset($detail['warehouse_stock'])) {
                    $totalStock += (int)$detail['warehouse_stock'];
                }
                
                // 统计库龄
                if (isset($detail['inventory_age'])) {
                    $totalAge += (int)$detail['inventory_age'];
                }
                
                // 统计各仓库数量
                $warehouse = $detail['warehouse_code'] ?? '未知';
                if (!isset($warehouseCount[$warehouse])) {
                    $warehouseCount[$warehouse] = 0;
                }
                $warehouseCount[$warehouse] += (int)($detail['warehouse_stock'] ?? 0);
            }
            
            echo "总库存数量: " . $totalStock . "\n";
            if (count($inventoryDetailList) > 0) {
                $avgAge = round($totalAge / count($inventoryDetailList), 2);
                echo "平均库龄: " . $avgAge . " 天\n";
            }
            
            if (!empty($warehouseCount)) {
                echo "\n各仓库库存分布:\n";
                foreach ($warehouseCount as $warehouse => $stock) {
                    echo "  {$warehouse}: {$stock} 件\n";
                }
            }

        } else {
            echo "未找到库存库龄记录\n";
            echo "提示: 在当前条件下没有找到任何库存库龄数据\n";
        }
    } else {
        echo "=== 错误信息 ===\n";
        echo "获取库存库龄失败或返回数据格式异常\n";
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
    // 示例2：查询指定SKU的库存库龄
    // ========================================
    echo "\n\n";
    echo "========================================\n";
    echo "=== 示例2：查询指定SKU的库存库龄 ===\n";
    echo "========================================\n\n";

    // 设置查询参数
    $skuCode2 = 'CES-ST0';              // 指定SKU编号
    $customerCode2 = '900278';           // 客户操作账号
    $warehouseCode2 = '';                // 仓库代码（可选）

    echo "查询参数:\n";
    echo "  SKU编号: {$skuCode2}\n";
    echo "  客户操作账号: " . ($customerCode2 ?: '未指定（查询所有账号）') . "\n";
    echo "  仓库代码: " . ($warehouseCode2 ?: '全部仓库') . "\n\n";

    // 查询指定SKU的库存库龄
    echo "正在调用API查询指定SKU的库存库龄...\n";
    $result2 = $fpx->getInventoryAge(
        $skuCode2,          // SKU编号
        $customerCode2,     // 客户操作账号（可选）
        $warehouseCode2     // 仓库代码（可选）
    );

    echo "API调用成功！\n\n";

    // 解析并显示结果
    if (isset($result2['result']) && ($result2['result'] === '1' || $result2['result'] === 'success') && isset($result2['data'])) {
        $inventoryData2 = $result2['data'];
        $inventoryDetailList2 = isset($inventoryData2['inventorydetaillist']) ? $inventoryData2['inventorydetaillist'] : [];
        
        echo "=== 指定SKU查询结果 ===\n";
        echo "库存详情记录数量: " . count($inventoryDetailList2) . "\n\n";
        
        if (!empty($inventoryDetailList2)) {
            // 按仓库分组显示
            $groupedByWarehouse = [];
            foreach ($inventoryDetailList2 as $detail) {
                $warehouse = $detail['warehouse_code'] ?? '未知';
                if (!isset($groupedByWarehouse[$warehouse])) {
                    $groupedByWarehouse[$warehouse] = [];
                }
                $groupedByWarehouse[$warehouse][] = $detail;
            }
            
            foreach ($groupedByWarehouse as $warehouse => $details) {
                echo "仓库: {$warehouse}\n";
                echo str_repeat("-", 50) . "\n";
                
                $warehouseTotalStock = 0;
                foreach ($details as $detail) {
                    $stock = (int)($detail['warehouse_stock'] ?? 0);
                    $age = $detail['inventory_age'] ?? 'N/A';
                    $batchNo = $detail['batch_no'] ?? '无';
                    $warehouseTotalStock += $stock;
                    
                    echo "  批次号: {$batchNo}, 库存: {$stock}, 库龄: {$age} 天\n";
                }
                echo "  仓库总库存: {$warehouseTotalStock}\n\n";
            }
        } else {
            echo "未找到库存库龄记录\n";
        }
    } else {
        echo "=== 错误信息 ===\n";
        echo "查询指定SKU库存库龄失败\n";
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
