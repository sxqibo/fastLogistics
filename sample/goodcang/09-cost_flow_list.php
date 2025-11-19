<?php

/**
 * GoodCang API 示例 - 获取费用流水
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

    echo "=== GoodCang API 获取费用流水测试 ===\n";
    echo "App Token: " . $appToken . "\n";
    echo "App Key: " . $appKey . "\n\n";

    // 设置查询参数（必填参数：happen_start_time, happen_end_time, page, page_size）
    $params = [
        // 必填参数
        'happen_start_time' => date('Y-m-d 00:00:00'),  // 发生开始时间（北京时间，今天0点0分0秒）
        'happen_end_time' => date('Y-m-d 23:59:59'),     // 发生结束时间（北京时间，今天23点59分59秒）
        'page' => 1,                                     // 分页页码
        'page_size' => 20,                               // 分页数量（最大200）

        // 可选参数（根据实际需求设置，不需要的可以不传）
        // 'account_code' => 'ACG940701',                   // 账户编号
        // 'business_type' => 0,                            // 业务类型（enum枚举，Int类型）
        // 'charge_type' => 0,                              // 账单状态（enum枚举，Int类型）
        //                                                   // 0: 全部 / 1: 未出账 / 2: 已出账
        //                                                   // 示例：查询未出账和已出账，可分别设置为 0、1 或 2
        // 'currency_code' => 'JPY',                        // 币种（如：JPY, USD, GBP等）
        // 'flow_type' => '1',                              // 流水类型（enum枚举，String类型）
        //                                                   // 1: 扣款 / 2: 入款
        // 'number_type' => 'order_number',                 // 单号类型（必须与order_number成对提交）
        // 'order_number' => '',                            // 订单号（必须与number_type成对提交）
        // 'types_of_fee' => 'CBC',                         // 费用类型（如：CBC）
        // 'next_page_token' => '',                         // 下一页token（当数据>5000条时使用）
        // 'prev_page_token' => '',                         // 上一页token（当数据>5000条时使用）
    ];

    echo "请求参数:\n";
    echo json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 调用API获取费用流水
    echo "正在调用API获取费用流水...\n";
    $result = $goodCang->getCostFlowList($params);

    echo "API调用成功！\n";
    echo "响应数据:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 检查响应状态
    if (!isset($result['code'])) {
        echo "警告: 响应数据缺少 code 字段\n";
        exit(1);
    }

    // 显示响应基本信息
    echo "=== 响应状态 ===\n";
    echo "状态码: " . ($result['code'] ?? 'N/A') . "\n";
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

    // 解析并显示费用流水信息
    if ($result['code'] === 0 && isset($result['data'])) {
        echo "=== 费用流水解析 ===\n";
        $data = $result['data'];
        
        // 显示总数
        if (isset($data['total'])) {
            echo "总记录数: " . $data['total'] . "\n";
        }

        // 显示分页token（当数据>5000条时）
        // 处理null和空字符串，null或空字符串表示没有更多页面
        if (isset($data['next_page_token'])) {
            $nextToken = $data['next_page_token'];
            if ($nextToken !== null && $nextToken !== '') {
                echo "下一页token: " . $nextToken . "\n";
            }
        }
        if (isset($data['prev_page_token'])) {
            $prevToken = $data['prev_page_token'];
            if ($prevToken !== null && $prevToken !== '') {
                echo "上一页token: " . $prevToken . "\n";
            }
        }

        // 显示费用流水列表
        if (isset($data['list']) && is_array($data['list'])) {
            echo "费用流水数量: " . count($data['list']) . "\n\n";
            
            foreach ($data['list'] as $index => $flow) {
                echo "费用流水 " . ($index + 1) . ":\n";
                
                // 必填字段
                echo "  账户编码: " . ($flow['account_code'] ?? 'N/A') . "\n";
                echo "  发生时间: " . ($flow['add_time'] ?? 'N/A') . "\n";
                echo "  发生金额: " . ($flow['amount'] ?? 'N/A') . "\n";
                echo "  币种: " . ($flow['currency_code'] ?? 'N/A') . "\n";
                echo "  当前账户币种余额: " . ($flow['currency_balance'] ?? 'N/A') . "\n";
                echo "  单号: " . ($flow['order_number'] ?? 'N/A') . "\n";
                
                // 可选字段
                if (isset($flow['reference_number']) && $flow['reference_number'] !== '') {
                    echo "  参考号: " . $flow['reference_number'] . "\n";
                }
                
                // 流水类型
                if (isset($flow['flow_type'])) {
                    echo "  流水类型: " . $flow['flow_type'];
                    if (isset($flow['flow_type_text']) && $flow['flow_type_text'] !== '') {
                        echo " (" . $flow['flow_type_text'] . ")";
                    }
                    echo "\n";
                }
                
                // 出账状态
                if (isset($flow['charge_type'])) {
                    echo "  出账状态: " . $flow['charge_type'];
                    if (isset($flow['charge_type_text']) && $flow['charge_type_text'] !== '') {
                        echo " (" . $flow['charge_type_text'] . ")";
                    }
                    echo "\n";
                }
                
                // 费用类型相关
                if (isset($flow['types_of_fee_text']) && $flow['types_of_fee_text'] !== '') {
                    echo "  费用类型: " . $flow['types_of_fee_text'] . "\n";
                }
                
                if (isset($flow['types_of_fee_name_cn']) && $flow['types_of_fee_name_cn'] !== '') {
                    echo "  费用代码中文名称: " . $flow['types_of_fee_name_cn'] . "\n";
                }
                
                if (isset($flow['types_of_fee_name_en']) && $flow['types_of_fee_name_en'] !== '') {
                    echo "  费用代码英文名称: " . $flow['types_of_fee_name_en'] . "\n";
                }
                
                // 汇率
                if (isset($flow['exchange_rate']) && $flow['exchange_rate'] !== '') {
                    echo "  汇率: " . $flow['exchange_rate'] . "\n";
                }
                
                echo "\n";
            }
        } else {
            echo "未返回费用流水列表数据\n";
            if (!isset($data['list'])) {
                echo "注意: data中缺少list字段\n";
            }
        }
    } else {
        echo "=== 错误信息 ===\n";
        echo "获取费用流水失败或返回数据格式异常\n";
        if (isset($result['code'])) {
            echo "错误代码: " . $result['code'] . "\n";
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
