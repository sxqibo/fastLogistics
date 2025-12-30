<?php
/**
 * 递四方 - 费用查询
 * 使用此接口可根据业务单号和业务类型查询出业务单的详细费用项
 */

use Sxqibo\Logistics\Fpx;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/config.php';

// 费用类型映射表（费用代码 => 中文名称）
$billingTypeMap = [
    '100' => '上门提货费',
    '101' => '散装卸货费',
    '102' => '超时费',
    '103' => '压夜费',
    '105' => 'SKU查验费',
    '106' => '更换条码费',
    '107' => '更换包装费',
    '108' => '拍照费',
    '109' => '添加附件费',
    '110' => '运费',
    '118' => '报关费',
    '119' => '查验费',
    '120' => '商检费',
    '121' => '续页费',
    '122' => '关税',
    '123' => 'VAT税',
    '124' => '关税杂费',
    '125' => '关税代垫费',
    '126' => '税号使用费',
    '127' => '卸货费',
    '128' => '仓租费',
    '129' => '下架+订单操作费',
    '130' => '更换包装费',
    '131' => '拍照费',
    '132' => '添加附件费',
    '133' => '托盘费',
    '134' => '库存盘点费',
    '135' => '运费',
    '136' => '超长费',
    '137' => '超重费',
    '138' => '偏远费',
    '139' => '地址更改费',
    '140' => '特殊操作费',
    '141' => 'BFPO地址费',
    '142' => '重派费',
    '143' => '卷状超长费',
    '144' => '卷状超重费',
    '145' => '非规则货物费',
    '147' => '托盘费',
    '148' => '超尺寸附加费',
    '149' => '高风险地区费',
    '150' => '限运目的地费',
    '151' => '过港费',
    '152' => '到付手续费',
    '153' => '仓储头程杂费',
    '154' => '超长超重附加费',
    '155' => '超长超重燃油费',
    '156' => '挂号费',
    '157' => '清关使用费',
    '158' => '无退税报关',
    '159' => '出口退税',
    '160' => '非出口退税',
    '161' => '单独退税自VAT',
    '162' => 'DDP手续费',
    '163' => '更换条码费',
    '164' => '分箱费',
    '165' => '销毁费',
    '166' => '退货操作附加费按件',
    '167' => '退货操作附加费按重量',
    '168' => '退货操作费',
    '169' => '入仓费',
    '170' => '仓储操作附加费按件',
    '171' => '仓储操作附加费按重量',
    '172' => '仓储自带包装操作费',
    '173' => '保险费',
    '174' => '仓储入库费按票',
    '175' => '仓储非自带包装操作费',
    '176' => '包装费',
    '180' => '电池费',
    '181' => '仓储头程杂费',
    '182' => '低保-保险',
    '183' => '高保-保险',
    '184' => '6‰保险',
    '185' => '清关费',
    '186' => '提货费',
    '187' => '电池费',
    '188' => '提货费',
    '189' => '操作手续费',
    '190' => '低保-保险',
    '191' => '高保-保险',
    '192' => '6‰保险',
    '193' => '挂号-快递保价',
    '194' => '平邮保价',
];

// 业务类型映射表
$businessTypeMap = [
    'I' => '入库委托',
    'O' => '出库委托',
    'T' => '调拨委托',
    'L' => '尾程共享订单',
];

// 扣费类型映射表
$deductionTypeMap = [
    'CA' => '现金',
    'CR' => '信用额度',
];

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

    // 设置响应语言为中文（默认已经是中文）
    $fpx->setLanguage('cn');

    echo "=== 递四方 - 费用查询 ===\n\n";

    // 示例：查询业务单号费用
    // 注意：如果业务单号查询失败（错误：err_consignment_not_found），可能的原因：
    // 1. 该单号可能是参考号而不是业务单号，请使用 ref_no 参数
    // 2. 该单号不属于当前客户账号
    // 3. 该单号在系统中不存在
    
    // 使用参考号查询（因为业务单号查询失败）
    $orderNo = 'OC9474952512250002';  // 业务单号（已清空，改用参考号查询）
    // $refNo = '103650795209242386';  // 参考号（将原来的业务单号作为参考号查询）
    $businessType = 'O';  // 出库委托（根据业务单号格式判断，PO通常表示出库单）

    echo "========================================\n";
    echo "=== 查询业务单费用 ===\n";
    echo "========================================\n\n";

    echo "查询参数:\n";
    if (!empty($orderNo)) {
        echo "  业务单号: {$orderNo}\n";
    }
    if (!empty($refNo)) {
        echo "  参考号: {$refNo}\n";
    }
    echo "  业务类型: {$businessType} ({$businessTypeMap[$businessType]})\n\n";

    echo "正在调用API查询费用信息...\n";

    // 尝试不同的业务类型（如果第一个失败）
    $businessTypesToTry = [$businessType, 'I', 'T', 'L'];
    $result = null;
    $successBusinessType = null;

    foreach ($businessTypesToTry as $bt) {
        try {
            echo "尝试业务类型: {$bt} ({$businessTypeMap[$bt]})...\n";
            
            // 优先使用业务单号，如果没有则使用参考号
            if (!empty($orderNo)) {
                $result = $fpx->getBilling($bt, $orderNo);
            } elseif (!empty($refNo)) {
                $result = $fpx->getBilling($bt, '', $refNo);
            } else {
                throw new Exception('业务单号和参考号都为空');
            }
            
            // 显示完整响应（用于调试）
            echo "  完整响应: " . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
            
            // 检查是否成功
            if (isset($result['result']) && $result['result'] === '1') {
                $billingList = isset($result['data']['billinglist']) && is_array($result['data']['billinglist']) 
                    ? $result['data']['billinglist'] : [];
                
                if (!empty($billingList)) {
                    $successBusinessType = $bt;
                    echo "✓ 查询成功！业务类型: {$bt} ({$businessTypeMap[$bt]})\n\n";
                    break;
                } else {
                    echo "  未找到费用信息，尝试下一个业务类型...\n\n";
                }
            } else {
                $msg = isset($result['msg']) ? $result['msg'] : '未知错误';
                echo "  查询失败: {$msg}\n";
                
                // 显示详细错误信息
                if (isset($result['errors']) && !empty($result['errors'])) {
                    foreach ($result['errors'] as $error) {
                        $errorCode = isset($error['error_code']) ? $error['error_code'] : '';
                        $errorMsg = isset($error['error_msg']) ? $error['error_msg'] : '';
                        echo "  错误代码: {$errorCode}\n";
                        echo "  错误信息: {$errorMsg}\n";
                        
                        // 针对特定错误给出建议
                        if ($errorCode === 'err_consignment_not_found') {
                            echo "  ⚠️  提示: 委托单不存在，可能原因：\n";
                            echo "     - 业务单号格式不正确或不存在\n";
                            echo "     - 该业务单号不属于当前客户账号\n";
                            echo "     - 建议尝试使用参考号（ref_no）查询\n";
                        }
                    }
                }
                
                // 显示result字段值
                if (isset($result['result'])) {
                    echo "  响应状态码: " . $result['result'] . "\n";
                }
                
                if ($bt !== $businessTypesToTry[count($businessTypesToTry) - 1]) {
                    echo "  尝试下一个业务类型...\n\n";
                }
            }
        } catch (Exception $e) {
            echo "  查询异常: " . $e->getMessage() . "\n";
            echo "  异常详情: " . $e->getTraceAsString() . "\n";
            if ($bt !== $businessTypesToTry[count($businessTypesToTry) - 1]) {
                echo "  尝试下一个业务类型...\n\n";
            }
        }
    }

    if ($result === null || !isset($result['result']) || $result['result'] !== '1') {
        echo "\n所有业务类型查询均失败！\n";
        if ($result !== null) {
            echo "最后响应结果:\n";
            echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
            
            if (isset($result['msg'])) {
                echo "\n错误信息: " . $result['msg'] . "\n";
            }
            
            if (isset($result['errors']) && !empty($result['errors'])) {
                echo "错误详情: " . json_encode($result['errors'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
            }
        } else {
            echo "未获取到任何响应数据。\n";
        }
        
        // 检查是否有特定的错误代码
        $hasConsignmentNotFound = false;
        if ($result !== null && isset($result['errors']) && is_array($result['errors'])) {
            foreach ($result['errors'] as $error) {
                if (isset($error['error_code']) && $error['error_code'] === 'err_consignment_not_found') {
                    $hasConsignmentNotFound = true;
                    break;
                }
            }
        }
        
        if ($hasConsignmentNotFound) {
            echo "\n❌ 错误分析：委托单不存在\n\n";
            echo "可能的原因：\n";
            echo "1. 业务单号 '{$orderNo}' 在系统中不存在\n";
            echo "2. 该业务单号不属于当前客户账号（请检查配置的 app_key 和 app_secret）\n";
            echo "3. 业务单号格式不正确\n\n";
            echo "建议：\n";
            echo "1. 确认业务单号是否正确（可以从订单列表或其他接口获取）\n";
            echo "2. 如果该单号是参考号，请使用参考号（ref_no）参数查询\n";
            echo "3. 检查当前使用的客户账号是否有权限查询该业务单\n";
            echo "4. 可以尝试查询其他已知存在的业务单号进行测试\n";
        } else {
            echo "\n可能的原因：\n";
            echo "1. 业务单号不存在或格式不正确\n";
            echo "2. 该业务单号不属于当前客户账号\n";
            echo "3. 该业务单号没有费用记录\n";
            echo "4. 需要检查业务单号是否正确（可能需要使用参考号 ref_no 而不是业务单号 order_no）\n";
        }
        
        exit(1);
    }

    // 显示响应消息
    if (isset($result['msg'])) {
        echo "响应消息: {$result['msg']}\n\n";
    }

    // 获取费用列表
    $billingList = isset($result['data']['billinglist']) && is_array($result['data']['billinglist']) 
        ? $result['data']['billinglist'] : [];
    
    $orderNoFromResponse = isset($result['data']['order_no']) ? $result['data']['order_no'] : $orderNo;

    if (empty($billingList)) {
        echo "未找到费用信息。\n";
        echo "业务单号: {$orderNoFromResponse}\n";
        exit(0);
    }

    // 统计信息
    $totalCount = count($billingList);
    $totalAmount = 0;
    $currencyStats = [];
    $deductionTypeStats = [];
    $shippingFeeAmount = 0;  // 运费（135）总额
    $shippingFeeList = [];   // 运费明细列表

    foreach ($billingList as $billing) {
        $amount = isset($billing['billing_amount']) ? floatval($billing['billing_amount']) : 0;
        $totalAmount += $amount;

        $currency = isset($billing['currency']) ? $billing['currency'] : '未知';
        if (!isset($currencyStats[$currency])) {
            $currencyStats[$currency] = 0;
        }
        $currencyStats[$currency] += $amount;

        $deductionType = isset($billing['deduction_type']) ? $billing['deduction_type'] : '未知';
        if (!isset($deductionTypeStats[$deductionType])) {
            $deductionTypeStats[$deductionType] = 0;
        }
        $deductionTypeStats[$deductionType] += $amount;

        // 单独统计运费（135）
        $billingType = isset($billing['billing_type']) ? $billing['billing_type'] : '';
        if ($billingType === '135') {
            $shippingFeeAmount += $amount;
            $shippingFeeList[] = $billing;
        }
    }

    echo "=== 统计信息 ===\n";
    echo "业务单号: {$orderNoFromResponse}\n";
    echo "业务类型: {$successBusinessType} ({$businessTypeMap[$successBusinessType]})\n";
    echo "费用项总数: {$totalCount}\n";
    echo "总费用金额: " . number_format($totalAmount, 2) . "\n";
    

    // 按币种统计
    if (!empty($currencyStats)) {
        echo "=== 按币种统计 ===\n";
        foreach ($currencyStats as $currency => $amount) {
            echo "  {$currency}: " . number_format($amount, 2) . "\n";
        }
        echo "\n";
    }

    // 按扣费类型统计
    if (!empty($deductionTypeStats)) {
        echo "=== 按扣费类型统计 ===\n";
        foreach ($deductionTypeStats as $type => $amount) {
            $typeName = isset($deductionTypeMap[$type]) ? $deductionTypeMap[$type] : $type;
            echo "  {$type} ({$typeName}): " . number_format($amount, 2) . "\n";
        }
        echo "\n";
    }

    // 优先显示运费（135）明细
    if (!empty($shippingFeeList)) {
        echo "=== ⭐ 运费（135）明细 ===\n";
        echo str_pad('序号', 6, ' ', STR_PAD_RIGHT) .
             str_pad('费用类型', 25, ' ', STR_PAD_RIGHT) .
             str_pad('费用代码', 12, ' ', STR_PAD_RIGHT) .
             str_pad('计费金额', 15, ' ', STR_PAD_RIGHT) .
             str_pad('币种', 8, ' ', STR_PAD_RIGHT) .
             str_pad('扣费类型', 15, ' ', STR_PAD_RIGHT) .
             str_pad('计费时间', 20, ' ', STR_PAD_RIGHT) . "\n";
        echo str_repeat('-', 110) . "\n";

        $index = 1;
        foreach ($shippingFeeList as $billing) {
            $billingType = isset($billing['billing_type']) ? $billing['billing_type'] : '';
            $billingTypeName = isset($billingTypeMap[$billingType]) ? $billingTypeMap[$billingType] : $billingType;
            $billingAmount = isset($billing['billing_amount']) ? number_format(floatval($billing['billing_amount']), 2) : '0.00';
            $currency = isset($billing['currency']) ? $billing['currency'] : '';
            $deductionType = isset($billing['deduction_type']) ? $billing['deduction_type'] : '';
            $deductionTypeName = isset($deductionTypeMap[$deductionType]) ? $deductionTypeMap[$deductionType] : $deductionType;
            
            // 处理计费时间（Long类型转日期）
            $billingDate = '';
            if (isset($billing['billing_date'])) {
                $timestamp = intval($billing['billing_date']);
                // 如果是毫秒时间戳，转换为秒
                if ($timestamp > 1000000000000) {
                    $timestamp = $timestamp / 1000;
                }
                $billingDate = date('Y-m-d H:i:s', $timestamp);
            }

            echo str_pad($index, 6, ' ', STR_PAD_RIGHT) .
                 str_pad(mb_substr($billingTypeName, 0, 23), 25, ' ', STR_PAD_RIGHT) .
                 str_pad($billingType, 12, ' ', STR_PAD_RIGHT) .
                 str_pad($billingAmount, 15, ' ', STR_PAD_RIGHT) .
                 str_pad($currency, 8, ' ', STR_PAD_RIGHT) .
                 str_pad($deductionType . '(' . mb_substr($deductionTypeName, 0, 5) . ')', 15, ' ', STR_PAD_RIGHT) .
                 str_pad($billingDate, 20, ' ', STR_PAD_RIGHT) . "\n";

            $index++;
        }
        echo "\n";
    }

    // 显示所有费用明细
    echo "=== 所有费用明细 ===\n";
    echo str_pad('序号', 6, ' ', STR_PAD_RIGHT) .
         str_pad('费用类型', 25, ' ', STR_PAD_RIGHT) .
         str_pad('费用代码', 12, ' ', STR_PAD_RIGHT) .
         str_pad('计费金额', 15, ' ', STR_PAD_RIGHT) .
         str_pad('币种', 8, ' ', STR_PAD_RIGHT) .
         str_pad('扣费类型', 15, ' ', STR_PAD_RIGHT) .
         str_pad('计费时间', 20, ' ', STR_PAD_RIGHT) . "\n";
    echo str_repeat('-', 110) . "\n";

    $index = 1;
    foreach ($billingList as $billing) {
        $billingType = isset($billing['billing_type']) ? $billing['billing_type'] : '';
        $billingTypeName = isset($billingTypeMap[$billingType]) ? $billingTypeMap[$billingType] : $billingType;
        $billingAmount = isset($billing['billing_amount']) ? number_format(floatval($billing['billing_amount']), 2) : '0.00';
        $currency = isset($billing['currency']) ? $billing['currency'] : '';
        $deductionType = isset($billing['deduction_type']) ? $billing['deduction_type'] : '';
        $deductionTypeName = isset($deductionTypeMap[$deductionType]) ? $deductionTypeMap[$deductionType] : $deductionType;
        
        // 处理计费时间（Long类型转日期）
        $billingDate = '';
        if (isset($billing['billing_date'])) {
            $timestamp = intval($billing['billing_date']);
            // 如果是毫秒时间戳，转换为秒
            if ($timestamp > 1000000000000) {
                $timestamp = $timestamp / 1000;
            }
            $billingDate = date('Y-m-d H:i:s', $timestamp);
        }

        // 如果是运费（135），添加标记
        $marker = ($billingType === '135') ? '⭐ ' : '   ';
        
        echo $marker . str_pad($index, 4, ' ', STR_PAD_RIGHT) .
             str_pad(mb_substr($billingTypeName, 0, 23), 25, ' ', STR_PAD_RIGHT) .
             str_pad($billingType, 12, ' ', STR_PAD_RIGHT) .
             str_pad($billingAmount, 15, ' ', STR_PAD_RIGHT) .
             str_pad($currency, 8, ' ', STR_PAD_RIGHT) .
             str_pad($deductionType . '(' . mb_substr($deductionTypeName, 0, 5) . ')', 15, ' ', STR_PAD_RIGHT) .
             str_pad($billingDate, 20, ' ', STR_PAD_RIGHT) . "\n";

        $index++;
    }

    echo "\n";

    // 显示原始响应数据（可选，用于调试）
    if (defined('DEBUG') && DEBUG) {
        echo "=== 原始响应数据 ===\n";
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
    }

    echo "=== 查询完成 ===\n";

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . "\n";
    echo "行号: " . $e->getLine() . "\n";
    if (isset($e->getTrace()[0])) {
        echo "调用堆栈: " . $e->getTraceAsString() . "\n";
    }
    exit(1);
}
