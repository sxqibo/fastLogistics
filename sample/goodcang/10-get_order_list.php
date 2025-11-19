<?php

/**
 * GoodCang API 示例 - 获取订单列表
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

    echo "=== GoodCang API 获取订单列表测试 ===\n";
    echo "App Token: " . $appToken . "\n";
    echo "App Key: " . $appKey . "\n\n";

    // 设置查询参数（必填参数：page, pageSize）
    $params = [
        // 必填参数
        'page' => 1,                                     // 当前页
        'pageSize' => 20,                                // 每页数据长度（最大200）

        // 可选参数（根据实际需求设置，不需要的可以不传）
        // 'code_type' => 'order_code',                   // 单号类型（默认：order_code）
        // 'order_code' => '000010-200716-0006',           // 订单号（如果传了此值，系统将忽略其它过滤选项）
        // 'order_code_arr' => [],                        // 订单号数组（最多200个，如果传了此值，系统将忽略其它过滤选项）
        // 'order_status' => 'D',                         // 订单状态（enum枚举）
        // 'shipping_method' => 'FEDEX-SMALLPARCEL',      // 物流产品代码
        // 'ship_status' => 1,                            // 提货状态（enum枚举，Int类型）
        // 'ooh_code' => 'XXX',                           // 自提编码
        
        // 时间范围参数（如果传了order_code或order_code_arr，这些参数无效）
        // 'create_date_from' => '2020-07-20 01:00:00',   // 订单创建开始时间
        // 'create_date_to' => '2020-07-20 23:59:59',     // 订单创建结束时间
        // 'date_shipping_from' => '2020-07-20 01:00:00', // 订单物流开始时间
        // 'date_shipping_to' => '2020-07-20 23:59:59',   // 订单物流结束时间
        // 'modify_date_from' => '2020-07-20 01:00:00',   // 订单修改开始时间
        // 'modify_date_to' => '2020-07-20 23:59:59',      // 订单修改结束时间
    ];

    echo "请求参数:\n";
    echo json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 调用API获取订单列表
    echo "正在调用API获取订单列表...\n";
    $result = $goodCang->getOrderList($params);

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

    // 解析并显示订单列表信息
    if (isset($result['ask']) && $result['ask'] === 'Success' && isset($result['data']) && is_array($result['data'])) {
        echo "=== 订单列表解析 ===\n";
        
        // 显示总数
        if (isset($result['count'])) {
            echo "总记录数: " . $result['count'] . "\n";
        }

        // 显示订单列表
        $orderList = $result['data'];
        echo "订单数量: " . count($orderList) . "\n\n";
        
        foreach ($orderList as $index => $order) {
            echo "订单 " . ($index + 1) . ":\n";
            
            // 基本信息
            echo "  订单号: " . ($order['order_code'] ?? 'N/A') . "\n";
            if (isset($order['order_type'])) {
                echo "  订单类型: " . $order['order_type'] . "\n";
            }
            if (isset($order['order_status'])) {
                echo "  订单状态: " . $order['order_status'] . "\n";
            }
            if (isset($order['ship_status'])) {
                echo "  提货状态: " . $order['ship_status'] . "\n";
            }
            if (isset($order['platform'])) {
                echo "  平台: " . $order['platform'] . "\n";
            }
            if (isset($order['platform_order_code']) && $order['platform_order_code'] !== '') {
                echo "  平台订单号: " . $order['platform_order_code'] . "\n";
            }
            if (isset($order['reference_no']) && $order['reference_no'] !== '') {
                echo "  客户参考号: " . $order['reference_no'] . "\n";
            }
            if (isset($order['shipping_method']) && $order['shipping_method'] !== '') {
                echo "  物流产品代码: " . $order['shipping_method'] . "\n";
            }
            if (isset($order['tracking_no']) && $order['tracking_no'] !== '') {
                echo "  跟踪号: " . $order['tracking_no'] . "\n";
            }
            if (isset($order['warehouse_code']) && $order['warehouse_code'] !== '') {
                echo "  配送仓库代码: " . $order['warehouse_code'] . "\n";
            }
            if (isset($order['site_source'])) {
                echo "  站点来源: " . $order['site_source'] . "\n";
            }
            
            // 时间信息
            if (isset($order['date_create'])) {
                echo "  创建时间: " . $order['date_create'] . "\n";
            }
            if (isset($order['date_modify'])) {
                echo "  修改时间: " . $order['date_modify'] . "\n";
            }
            if (isset($order['date_shipping'])) {
                echo "  出库时间: " . $order['date_shipping'] . "\n";
            }
            
            // 收件人信息（注意：美国站、欧洲站订单在中国站查询时，个人信息会以***返回）
            if (isset($order['consignee_name']) && $order['consignee_name'] !== '') {
                echo "  收件人姓名: " . $order['consignee_name'] . "\n";
            }
            if (isset($order['consignee_country_name']) && $order['consignee_country_name'] !== '') {
                echo "  收件人国家: " . $order['consignee_country_name'] . "\n";
            }
            if (isset($order['consignee_city']) && $order['consignee_city'] !== '') {
                echo "  收件人城市: " . $order['consignee_city'] . "\n";
            }
            if (isset($order['consignee_state']) && $order['consignee_state'] !== '') {
                echo "  收件人省/州: " . $order['consignee_state'] . "\n";
            }
            if (isset($order['consignee_zipcode']) && $order['consignee_zipcode'] !== '') {
                echo "  收件人邮编: " . $order['consignee_zipcode'] . "\n";
            }
            
            // 商品明细
            if (isset($order['items']) && is_array($order['items']) && !empty($order['items'])) {
                echo "  商品明细 (" . count($order['items']) . " 件):\n";
                foreach ($order['items'] as $itemIndex => $item) {
                    echo "    商品 " . ($itemIndex + 1) . ":\n";
                    echo "      SKU: " . ($item['product_sku'] ?? 'N/A') . "\n";
                    echo "      数量: " . ($item['quantity'] ?? 'N/A') . "\n";
                    if (isset($item['fba_product_code']) && $item['fba_product_code'] !== '') {
                        echo "      FNSKU/VC商品条码/WFS UPC: " . $item['fba_product_code'] . "\n";
                    }
                    if (isset($item['hs_code']) && $item['hs_code'] !== '') {
                        echo "      海关编码: " . $item['hs_code'] . "\n";
                    }
                }
            }
            
            // 费用明细
            if (isset($order['charge_details']) && is_array($order['charge_details'])) {
                $chargeDetails = $order['charge_details'];
                if (isset($chargeDetails['total_fee'])) {
                    echo "  总费用: " . $chargeDetails['total_fee'] . " " . ($chargeDetails['currency_code'] ?? '') . "\n";
                }
                if (isset($chargeDetails['charge_list']) && is_array($chargeDetails['charge_list']) && !empty($chargeDetails['charge_list'])) {
                    echo "  费用明细 (" . count($chargeDetails['charge_list']) . " 项):\n";
                    foreach ($chargeDetails['charge_list'] as $feeIndex => $fee) {
                        echo "    费用 " . ($feeIndex + 1) . ": " . ($fee['fee_name'] ?? 'N/A') . 
                             " - " . ($fee['fee_amount'] ?? '0') . " " . ($fee['currency_code'] ?? '') . "\n";
                    }
                }
            }
            
            echo "\n";
        }
    } else {
        echo "=== 错误信息 ===\n";
        echo "获取订单列表失败或返回数据格式异常\n";
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
