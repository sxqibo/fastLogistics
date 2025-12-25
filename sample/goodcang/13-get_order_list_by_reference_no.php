<?php

/**
 * GoodCang API 示例 - 根据平台单号获取订单尾程费用
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

    echo "=== GoodCang API 根据平台单号获取订单尾程费用 ===\n";
    echo "App Token: " . $appToken . "\n";
    echo "App Key: " . $appKey . "\n\n";

    // 要查询的平台单号
    $platformOrderCode = 'PO-098-16672319370870576';

    // 设置查询参数（必填参数：page, pageSize）
    // 注意：API可能不支持直接通过平台单号查询，需要通过时间范围查询后过滤
    // 根据订单日期（12月2号）设置时间范围
    $params = [
        // 必填参数
        'page' => 1,                                     // 当前页
        'pageSize' => 200,                               // 每页数据长度（最大200，设置为最大以便查询更多数据）

        // 时间范围参数：根据订单日期（12月2号）设置查询范围
        // 订单创建时间为 2025年12月2号
        'create_date_from' => '2025-12-02 00:00:00',    // 订单创建开始时间（12月2号0点）
        'create_date_to' => '2025-12-02 23:59:59',      // 订单创建结束时间（12月2号23点59分59秒）
    ];

    echo "请求参数:\n";
    echo json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 调用API获取订单列表（支持多页查询）
    echo "正在调用API获取订单列表（将根据平台单号过滤）...\n";
    echo "查询平台单号: {$platformOrderCode}\n";
    echo "查询时间范围: " . ($params['create_date_from'] ?? 'N/A') . " 至 " . ($params['create_date_to'] ?? 'N/A') . "\n\n";
    
    $found = false;
    $allOrders = [];
    $currentPage = 1;
    $maxPages = 10; // 最多查询10页，防止无限循环
    
    // 循环查询多页数据，直到找到目标订单或查询完所有页
    while ($currentPage <= $maxPages && !$found) {
        $params['page'] = $currentPage;
        echo "正在查询第 {$currentPage} 页...\n";
        
        $result = $goodCang->getOrderList($params);
        
        // 检查响应状态（V1版本API，响应结构为ask, message, count, data）
        if (!isset($result['ask'])) {
            echo "警告: 响应数据缺少 ask 字段\n";
            break;
        }
        
        // 如果第一页，显示响应基本信息
        if ($currentPage === 1) {
            echo "API调用成功！\n\n";
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
            
            // 显示总数
            if (isset($result['count'])) {
                echo "总记录数: " . $result['count'] . "\n";
                $totalCount = (int)$result['count'];
                $totalPages = ceil($totalCount / $params['pageSize']);
                echo "预计总页数: " . $totalPages . "\n";
            }
            echo "\n";
        }
        
        // 解析并显示订单信息和尾程费用
        if (isset($result['ask']) && $result['ask'] === 'Success' && isset($result['data']) && is_array($result['data'])) {
            $orderList = $result['data'];
            echo "第 {$currentPage} 页订单数量: " . count($orderList) . "\n";
            
            // 如果当前页没有数据，停止查询
            if (empty($orderList)) {
                echo "当前页无数据，停止查询\n\n";
                break;
            }
            
            // 在当前页中查找目标订单
            foreach ($orderList as $order) {
                $orderPlatformOrderCode = $order['platform_order_code'] ?? '';
                
                // 如果找到匹配的平台单号
                if ($orderPlatformOrderCode === $platformOrderCode) {
                    $found = true;
                    echo "✓ 找到目标订单！\n\n";
                    
                    echo "=== 订单信息解析 ===\n";
            // 基本信息
            echo "订单号: " . ($order['order_code'] ?? 'N/A') . "\n";
            echo "平台订单号: " . $orderPlatformOrderCode . "\n";
            if (isset($order['reference_no']) && $order['reference_no'] !== '') {
                echo "参考号: " . $order['reference_no'] . "\n";
            }
            if (isset($order['order_status'])) {
                echo "订单状态: " . $order['order_status'] . "\n";
            }
            if (isset($order['warehouse_code']) && $order['warehouse_code'] !== '') {
                echo "配送仓库代码: " . $order['warehouse_code'] . "\n";
            }
            if (isset($order['shipping_method']) && $order['shipping_method'] !== '') {
                echo "物流产品代码: " . $order['shipping_method'] . "\n";
            }
            if (isset($order['tracking_no']) && $order['tracking_no'] !== '') {
                echo "跟踪号: " . $order['tracking_no'] . "\n";
            }
            
            // 时间信息
            if (isset($order['date_create'])) {
                echo "创建时间: " . $order['date_create'] . "\n";
            }
            if (isset($order['date_shipping'])) {
                echo "出库时间: " . $order['date_shipping'] . "\n";
            }
            
            // 费用明细
            echo "\n=== 费用明细 ===\n";
            if (isset($order['charge_details']) && is_array($order['charge_details'])) {
                $chargeDetails = $order['charge_details'];
                
                // 显示总费用
                if (isset($chargeDetails['total_fee'])) {
                    $currency = $chargeDetails['currency_code'] ?? 'N/A';
                    echo "总费用: " . $chargeDetails['total_fee'] . " " . $currency . "\n";
                }
                
                // 显示费用列表
                if (isset($chargeDetails['charge_list']) && is_array($chargeDetails['charge_list']) && !empty($chargeDetails['charge_list'])) {
                    echo "\n费用明细列表 (" . count($chargeDetails['charge_list']) . " 项):\n";
                    
                    $tailFeeTotal = 0;  // 尾程费用总计
                    $tailFeeList = [];  // 尾程费用列表
                    
                    foreach ($chargeDetails['charge_list'] as $feeIndex => $fee) {
                        $feeGroup = $fee['fee_group'] ?? '';
                        $feeName = $fee['fee_name'] ?? 'N/A';
                        $feeAmount = $fee['fee_amount'] ?? '0';
                        $feeCurrency = $fee['currency_code'] ?? 'N/A';
                        $feeCode = $fee['fee_code'] ?? 'N/A';
                        
                        echo "  费用 " . ($feeIndex + 1) . ":\n";
                        echo "    费用名称: " . $feeName . "\n";
                        echo "    费用编码: " . $feeCode . "\n";
                        echo "    费用环节: " . $feeGroup . " (" . getFeeGroupText($feeGroup) . ")\n";
                        echo "    费用金额: " . $feeAmount . " " . $feeCurrency . "\n";
                        if (isset($fee['billing_time'])) {
                            echo "    计费时间: " . $fee['billing_time'] . "\n";
                        }
                        if (isset($fee['ut_code'])) {
                            echo "    计费单位: " . $fee['ut_code'] . "\n";
                        }
                        echo "\n";
                        
                        // 如果是尾程费用（fee_group = "T"），记录到尾程费用列表
                        if ($feeGroup === 'T') {
                            $tailFeeList[] = [
                                'fee_name' => $feeName,
                                'fee_code' => $feeCode,
                                'fee_amount' => $feeAmount,
                                'currency_code' => $feeCurrency,
                                'billing_time' => $fee['billing_time'] ?? '',
                            ];
                            $tailFeeTotal += (float)$feeAmount;
                        }
                    }
                    
                    // 显示尾程费用汇总
                    echo "=== 尾程费用汇总 ===\n";
                    if (!empty($tailFeeList)) {
                        echo "尾程费用项数: " . count($tailFeeList) . "\n\n";
                        foreach ($tailFeeList as $tailIndex => $tailFee) {
                            echo "尾程费用 " . ($tailIndex + 1) . ":\n";
                            echo "  费用名称: " . $tailFee['fee_name'] . "\n";
                            echo "  费用编码: " . $tailFee['fee_code'] . "\n";
                            echo "  费用金额: " . $tailFee['fee_amount'] . " " . $tailFee['currency_code'] . "\n";
                            if (!empty($tailFee['billing_time'])) {
                                echo "  计费时间: " . $tailFee['billing_time'] . "\n";
                            }
                            echo "\n";
                        }
                        $currency = !empty($tailFeeList) ? $tailFeeList[0]['currency_code'] : 'N/A';
                        echo "尾程费用总计: " . number_format($tailFeeTotal, 2) . " " . $currency . "\n";
                    } else {
                        echo "未找到尾程费用（fee_group = T）\n";
                        echo "提示: 尾程费用的费用环节(fee_group)字段值为 'T'\n";
                    }
                } else {
                    echo "费用明细列表为空\n";
                }
            } else {
                echo "未返回费用明细数据\n";
            }
            
                    echo "\n";
                    break 2; // 跳出两层循环（foreach 和 while）
                }
            }
            
            // 如果当前页没有找到，继续查询下一页
            $currentPage++;
            if ($currentPage <= $maxPages) {
                echo "当前页未找到目标订单，继续查询下一页...\n\n";
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
            break;
        }
    }
    
    if (!$found) {
        echo "\n=== 查询结果 ===\n";
        echo "未找到平台单号为 '{$platformOrderCode}' 的订单\n";
        echo "已查询页数: " . ($currentPage - 1) . "\n";
        echo "提示:\n";
        echo "  1. 请确认时间范围是否正确（当前设置为: " . ($params['create_date_from'] ?? 'N/A') . " 至 " . ($params['create_date_to'] ?? 'N/A') . "）\n";
        echo "  2. 如果订单是其他年份的12月2号，请修改代码中的 create_date_from 和 create_date_to 参数\n";
        echo "  3. 如果订单数量较多，可能需要增加查询页数\n";
    }

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行数: " . $e->getLine() . "\n";
}

/**
 * 获取费用环节文本说明
 * 
 * @param string $feeGroup 费用环节代码
 * @return string
 */
function getFeeGroupText($feeGroup)
{
    $feeGroupMap = [
        'T' => '尾程',
        'H' => '头程',
        'O' => '其他',
    ];
    
    return $feeGroupMap[$feeGroup] ?? '未知';
}
