<?php
/**
 * 递四方 - 库存查询 DEMO
 * 接口: fu.wms.inventory.get
 * 功能: 根据 SKU 列表查询库存信息（可用库存、待出库库存、在途库存等）
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

    echo "=== 递四方 - 库存查询（fu.wms.inventory.get） DEMO ===\n\n";

    // 按照接口文档准备请求参数
    // customer_code : 客户操作账号（可选）
    // lstsku        : SKU 编号列表（单次最大支持 100 种 SKU）
    // lstbatch_no   : 批次号列表（可选）
    // warehouse_code: 仓库代码（可选）
    // page_no       : 页码
    // page_size     : 每页记录数（最大 500）
    $customerCode  = '';                       // 示例中留空，表示不限制客户账号
    $warehouseCode = '';                       // 示例中留空，表示全部仓库
    $skuList       = ['Y-YUANMU-TOU-91'];   // 文档示例中的 SKU 列表
    $pageNo        = 1;
    $pageSize      = 10;

    // 组装请求参数（与文档字段对应）
    $params = [
        'lstsku'    => $skuList,
        'page_no'   => $pageNo,
        'page_size' => $pageSize,
    ];

    if (!empty($customerCode)) {
        $params['customer_code'] = $customerCode;
    }
    if (!empty($warehouseCode)) {
        $params['warehouse_code'] = $warehouseCode;
    }

    echo "请求参数:\n";
    echo "  customer_code : " . ($customerCode ?: '未指定') . "\n";
    echo "  warehouse_code: " . ($warehouseCode ?: '未指定（全部仓库）') . "\n";
    echo "  lstsku        : " . implode(', ', $skuList) . "\n";
    echo "  page_no       : {$pageNo}\n";
    echo "  page_size     : {$pageSize}\n\n";

    // 调用封装好的库存查询方法（内部实际请求 fu.wms.inventory.get）
    $result = $fpx->getInventory($params);

    echo "=== 原始响应数据 ===\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 解析响应数据
    // 按文档: result = "1" 表示成功，"0" 表示失败
    if (
        isset($result['result'])
        && ($result['result'] === '1' || $result['result'] === 'success')
        && isset($result['data'])
    ) {
        $data = $result['data'];

        // 分页信息
        $pageNoRes   = $data['page_no']   ?? null;
        $pageSizeRes = $data['page_size'] ?? null;
        $totalRes    = $data['total']     ?? null;

        echo "=== 库存查询结果解析 ===\n";
        if ($pageNoRes !== null) {
            echo "页码(page_no)      : {$pageNoRes}\n";
        }
        if ($pageSizeRes !== null) {
            echo "每页记录数(page_size): {$pageSizeRes}\n";
        }
        if ($totalRes !== null) {
            echo "总记录数(total)    : {$totalRes}\n";
        }
        echo "\n";

        // 库存列表（接口文档中描述为 inventorylist，实际返回字段为 data）
        $inventoryList = [];
        if (isset($data['inventorylist']) && is_array($data['inventorylist'])) {
            $inventoryList = $data['inventorylist'];
        } elseif (isset($data['data']) && is_array($data['data'])) {
            $inventoryList = $data['data'];
        }

        if (!empty($inventoryList)) {
            echo "库存记录数量: " . count($inventoryList) . "\n\n";

            foreach ($inventoryList as $index => $item) {
                echo "库存记录 " . ($index + 1) . ":\n";
                echo "  客户操作账号(customer_code): " . ($item['customer_code']   ?? '') . "\n";
                echo "  仓库代码(warehouse_code)    : " . ($item['warehouse_code']  ?? '') . "\n";
                echo "  SKU ID(sku_id)             : " . ($item['sku_id']          ?? '') . "\n";
                echo "  SKU编码(sku_code)          : " . ($item['sku_code']        ?? '') . "\n";
                echo "  批次号(batch_no)           : " . (($item['batch_no']       ?? '') ?: '无') . "\n";
                echo "  库存质量(stock_quality)    : " . ($item['stock_quality']   ?? '') . "\n";

                // 库存数量
                $availableStock = (int)($item['available_stock'] ?? 0);
                $pendingStock   = (int)($item['pending_stock']   ?? 0);
                $onwayStock     = (int)($item['onway_stock']     ?? 0);
                $freezeStock    = (int)($item['freeze_stock']    ?? 0);
                $totalStock     = $availableStock + $pendingStock + $onwayStock;

                echo "  仓库可用库存(available_stock): {$availableStock}\n";
                echo "  待出库库存(pending_stock)    : {$pendingStock}\n";
                echo "  在途库存(onway_stock)       : {$onwayStock}\n";
                echo "  冻结库存(freeze_stock)      : {$freezeStock}\n";
                echo "  总库存(available+pending+onway): {$totalStock}\n";
                echo "\n";
            }
        } else {
            echo "未找到任何库存记录\n";
        }
    } else {
        echo "=== 错误信息 ===\n";
        echo "调用库存查询接口失败或返回数据格式异常\n";
        if (isset($result['result'])) {
            echo "result: " . $result['result'] . "\n";
        }
        if (isset($result['msg'])) {
            echo "msg   : " . $result['msg'] . "\n";
        }
        if (isset($result['errors']) && is_array($result['errors'])) {
            echo "errors:\n";
            foreach ($result['errors'] as $error) {
                echo "  - 错误码: " . ($error['error_code'] ?? '') . "，错误信息: " . ($error['error_msg'] ?? '') . "\n";
            }
        }
    }

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行数: " . $e->getLine() . "\n";
}
