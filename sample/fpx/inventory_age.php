<?php
/**
 * 递四方 - 查询库存库龄示例
 * 接口: fu.wms.inventory.getdetail
 * 功能: 根据 SKU 列表查询库存库龄信息（上架时间、库龄、失效日期等）
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

    echo "=== 递四方 - 查询库存库龄（fu.wms.inventory.getdetail） ===\n\n";

    // 按照文档准备请求参数
    // customer_code: 客户操作账号（可选）
    // warehouse_code: 仓库代码（可选，不传则查询所有仓）
    // lstsku: SKU 编号列表（必传，单次最多 100 个）
    $customerCode  = '';                       // 示例留空，表示不指定客户账号
    $warehouseCode = '';                       // 示例留空，表示全部仓库
    $skuList       = ['Y-YUANMU-TOU-91'];   // 文档中的示例 SKU

    echo "请求参数:\n";
    echo "  customer_code : " . ($customerCode ?: '未指定') . "\n";
    echo "  warehouse_code: " . ($warehouseCode ?: '未指定（全部仓库）') . "\n";
    echo "  lstsku        : " . implode(', ', $skuList) . "\n\n";

    // 调用封装好的库龄查询方法（内部实际请求 fu.wms.inventory.getdetail）
    $result = $fpx->getInventoryAge(
        $skuList,       // lstsku
        $customerCode,  // customer_code（可选）
        $warehouseCode  // warehouse_code（可选）
    );

    echo "=== 原始响应数据 ===\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 按接口文档解析响应数据
    if (
        isset($result['result'])
        && ($result['result'] === '1' || $result['result'] === 'success')
        && isset($result['data']['inventorydetaillist'])
        && is_array($result['data']['inventorydetaillist'])
    ) {
        $detailList = $result['data']['inventorydetaillist'];
        echo "=== 库存库龄明细解析 ===\n";
        echo "共返回 " . count($detailList) . " 条记录\n\n";

        foreach ($detailList as $index => $detail) {
            echo "记录 " . ($index + 1) . ":\n";
            echo "  序号(serial_no)     : " . ($detail['serial_no']      ?? '') . "\n";
            echo "  客户代码(customer_code): " . ($detail['customer_code'] ?? '') . "\n";
            echo "  仓库代码(warehouse_code): " . ($detail['warehouse_code'] ?? '') . "\n";
            echo "  SKU ID(sku_id)      : " . ($detail['sku_id']        ?? '') . "\n";
            echo "  SKU编码(sku_code)   : " . ($detail['sku_code']      ?? '') . "\n";
            echo "  批次号(batch_no)    : " . (($detail['batch_no']     ?? '') ?: '无') . "\n";
            echo "  库存质量(stock_quality): " . ($detail['stock_quality'] ?? '') . "\n";
            echo "  委托单号(consignment_no): " . ($detail['consignment_no'] ?? '') . "\n";
            echo "  仓库在库库存(warehouse_stock): " . ($detail['warehouse_stock'] ?? '') . "\n";

            // 上架时间 putaway_time 是 long 类型时间戳，文档说明为毫秒
            if (!empty($detail['putaway_time'])) {
                $putawayTimestamp = (int)$detail['putaway_time'];
                // 如果是毫秒时间戳，转换为秒
                if ($putawayTimestamp > 9999999999) {
                    $putawayTimestamp = (int) floor($putawayTimestamp / 1000);
                }
                $putawayDate = date('Y-m-d H:i:s', $putawayTimestamp);
                echo "  上架时间(putaway_time): {$putawayDate} (原始: {$detail['putaway_time']})\n";
            }

            if (isset($detail['inventory_age'])) {
                echo "  库龄(inventory_age) : " . $detail['inventory_age'] . " 天\n";
            }

            if (array_key_exists('expiry_date', $detail)) {
                echo "  失效日期(expiry_date): " . ($detail['expiry_date'] ?: '无') . "\n";
            }

            echo "\n";
        }
    } else {
        echo "=== 错误信息 ===\n";
        echo "调用库存库龄接口失败或返回数据格式异常\n";
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
