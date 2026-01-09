<?php

/**
 * GoodCang API 示例 - 获取入库单列表 (GRN List)
 * 接口: POST https://oms.goodcang.net/public_open/inbound_order/get_grn_list
 * 功能: 获取已创建的所有入库单列表信息
 */

// 引入配置文件
$config = require_once __DIR__ . '/config.php';

// 如果后续需要用到 GoodCang 类，可保留自动加载
require_once __DIR__ . '/../../vendor/autoload.php';

$appToken = $config['goodcang']['app_token'] ?? '';
$appKey   = $config['goodcang']['app_key'] ?? '';

if ($appToken === '' || $appKey === '') {
    echo "请先在 sample/goodcang/config.php 中配置 app_token 和 app_key\n";
    exit(1);
}

try {
    echo "=== GoodCang API 获取入库单列表测试 ===\n";
    echo "App Token: {$appToken}\n";
    echo "App Key  : {$appKey}\n\n";

    // 按文档准备请求参数
    // 必填: page, pageSize
    // 可选: code_type, create_date_from, create_date_to, is_rollover,
    //       modify_date_from, modify_date_to, receiving_code_arr
    $params = [
        'page'     => 1,   // 当前页（必填）
        'pageSize' => 20,  // 每页数据长度（必填，最大200）

        // 以下参数根据需要启用（示例中给出文档示例值，可自行调整）
        // 'code_type'        => 1,                             // 单号类型，枚举，默认1
        // 'create_date_from' => '2020-07-07 00:00:00',         // 创建开始日期
        // 'create_date_to'   => '2020-07-07 23:59:59',         // 创建结束日期
        // 'is_rollover'      => 1,                             // 是否仓库装箱商品（枚举 0/1）
        // 'modify_date_from' => '2020-07-07 00:00:00',         // 修改开始时间
        // 'modify_date_to'   => '2020-07-07 23:59:59',         // 修改结束时间
        // 'receiving_code_arr' => ['RVG296-190117-0005'],      // 入库单号数组，最多100个
    ];

    echo "请求参数:\n";
    echo json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 组装请求
    $url = 'https://oms.goodcang.net/public_open/inbound_order/get_grn_list';

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($params, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'app-token: ' . $appToken,
            'app-key: ' . $appKey,
            'Accept: application/json',
            'Content-Type: application/json',
        ],
    ]);

    echo "正在调用 API 获取入库单列表...\n";
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception('CURL 错误: ' . $curlError);
    }

    echo "HTTP 状态码: {$httpCode}\n";

    if ($httpCode !== 200) {
        echo "HTTP 请求失败，响应内容:\n";
        echo $response . "\n";
        exit(1);
    }

    // 解析 JSON 响应（V1 结构: ask, message, count, data）
    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON 解析失败: ' . json_last_error_msg());
    }

    echo "\n=== 原始响应数据 ===\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 检查基础字段
    if (!isset($result['ask'])) {
        echo "响应中缺少 ask 字段，可能不是标准V1返回结构\n";
        exit(1);
    }

    echo "=== 响应状态 ===\n";
    echo "ask    : " . ($result['ask'] ?? 'N/A') . "\n";
    echo "message: " . ($result['message'] ?? 'N/A') . "\n";
    if (isset($result['count'])) {
        echo "count  : " . $result['count'] . "\n";
    }
    echo "\n";

    // 成功时解析 data 列表
    if ($result['ask'] === 'Success' && isset($result['data']) && is_array($result['data'])) {
        $list = $result['data'];
        echo "=== 入库单列表解析 ===\n";
        echo "入库单数量: " . count($list) . "\n\n";

        foreach ($list as $index => $item) {
            echo "入库单 " . ($index + 1) . ":\n";
            echo "  入库单号(receiving_code)       : " . ($item['receiving_code']        ?? '') . "\n";
            echo "  入库单类型(transit_type)       : " . ($item['transit_type']          ?? '') . "\n";
            echo "  入库单状态(receiving_status)   : " . ($item['receiving_status']      ?? '') . "\n";
            echo "  跟踪号(tracking_number)        : " . ($item['tracking_number']       ?? '') . "\n";
            echo "  客户参考号(reference_no)       : " . ($item['reference_no']          ?? '') . "\n";
            echo "  是否仓库装箱(is_rollover)      : " . ($item['is_rollover']           ?? '') . "\n";
            echo "  海外仓仓库编码(warehouse_code) : " . ($item['warehouse_code']        ?? '') . "\n";
            echo "  物理仓编码(wp_code)            : " . ($item['wp_code']               ?? '') . "\n";
            echo "  中转仓代码(transit_warehouse_code): " . ($item['transit_warehouse_code'] ?? '') . "\n";
            echo "  服务方式(sm_code)              : " . ($item['sm_code']               ?? '') . "\n";
            echo "  货运方式(receiving_shipping_type): " . ($item['receiving_shipping_type'] ?? '') . "\n";
            echo "  轨迹状态(track_status)         : " . ($item['track_status']          ?? '') . "\n";
            echo "  报关资料状态(customs_docs_status): " . ($item['customs_docs_status']   ?? '') . "\n";
            echo "  创建时间(create_at)            : " . ($item['create_at']             ?? '') . "\n";
            echo "  修改时间(update_at)            : " . ($item['update_at']             ?? '') . "\n";
            echo "  预报箱数(box_total_count)      : " . ($item['box_total_count']       ?? '') . "\n";
            echo "  海外收货总箱数(overseas_box_total): " . ($item['overseas_box_total']  ?? '') . "\n";
            echo "  海外收货总件数(overseas_sku_total): " . ($item['overseas_sku_total']  ?? '') . "\n";
            echo "  预报SKU件数(sku_total_count)   : " . ($item['sku_total_count']       ?? '') . "\n";
            echo "  备注(remark)                   : " . ($item['remark']                ?? '') . "\n";
            echo "\n";
        }
    } else {
        echo "=== 错误信息或无数据 ===\n";
        if (isset($result['errors']) && is_array($result['errors']) && !empty($result['errors'])) {
            echo "errors:\n";
            foreach ($result['errors'] as $err) {
                if (is_array($err)) {
                    echo "  - " . json_encode($err, JSON_UNESCAPED_UNICODE) . "\n";
                } else {
                    echo "  - " . $err . "\n";
                }
            }
        } else {
            echo "没有返回 data 数组，或 ask 不为 Success\n";
        }
    }

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行数: " . $e->getLine() . "\n";
}
