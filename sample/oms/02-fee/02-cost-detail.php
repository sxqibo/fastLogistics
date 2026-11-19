<?php

/**
 * OMS 仓租明细查询示例
 *
 * 接口说明：
 * - pageFundsFlow  按日期/类型分页查业务流水（支持 statisticDate 仓租日期筛选）
 * - queryCostDetail 查单笔费用的计费项明细 costItemList（需已知 OMS 单据号或费用单据号）
 *
 * 结论：查 2026-06-29 仓租不能只用 queryCostDetail，需先用 pageFundsFlow 定位流水，
 *       若有单据号再调 queryCostDetail 获取计费项 breakdown。
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use Sxqibo\Logistics\Oms;

$config = require __DIR__ . '/../config.php';
$appKey = $config['appKey'] ?? '';
$appSecret = $config['appSecret'] ?? '';

$targetDate = '2026-06-29';

$costStatusMap = [
    1 => '未计费',
    2 => '已计费',
    3 => '计费异常',
    4 => '重新计费中',
    5 => '计费中',
];

$billStatusMap = [
    1 => '未出账',
    2 => '已出账',
];

try {
    $oms = new Oms($appKey, $appSecret);

    echo "=== OMS - 查询 {$targetDate} 仓租明细 ===\n\n";

    // 第一步：pageFundsFlow 按仓租日期筛选流水
    echo "【步骤1】pageFundsFlow - 按仓租日期查询流水\n";
    echo str_repeat('-', 50) . "\n";

    $listParams = [
        'moduleTypeList' => [6],
        'timeType' => 'statisticDate',
        'startTime' => "{$targetDate} 00:00:00",
        'endTime' => "{$targetDate} 23:59:59",
        'page' => 1,
        'pageSize' => 50,
    ];

    echo "请求参数: " . json_encode($listParams, JSON_UNESCAPED_UNICODE) . "\n\n";

    $listResponse = $oms->pageFundsFlow($listParams);

    if (($listResponse['code'] ?? 0) !== 200) {
        echo "❌ pageFundsFlow 查询失败: " . ($listResponse['msg'] ?? '未知错误') . "\n";
        exit(1);
    }

    $records = $listResponse['data']['records'] ?? [];
    $total = $listResponse['data']['total'] ?? 0;

    echo "✓ 查到 {$total} 条仓租流水\n\n";

    if (empty($records)) {
        echo "⚠️  未找到 {$targetDate} 的仓租流水\n";
        exit(0);
    }

    foreach ($records as $index => $record) {
        $no = $index + 1;
        echo "--- 流水 {$no} ---\n";
        echo "  仓库: " . ($record['whCode'] ?? '-') . "\n";
        echo "  OMS单据号: " . ($record['orderNo'] ?: '（空）') . "\n";
        echo "  仓租费用: " . ($record['costTotal'] ?? 0) . ' ' . ($record['currencyCode'] ?? '') . "\n";
        echo "  计费状态: " . ($costStatusMap[intval($record['costStatus'] ?? 0)] ?? '-') . "\n";
        echo "  出账状态: " . ($billStatusMap[intval($record['billStatus'] ?? 0)] ?? '-') . "\n";
        echo "  计费时间: " . ($record['costTime'] ?? '-') . "\n\n";
    }

    // 第二步：queryCostDetail 查计费项明细（需单据号）
    echo "【步骤2】queryCostDetail - 查询计费项明细\n";
    echo str_repeat('-', 50) . "\n";

    $hasDetail = false;

    foreach ($records as $index => $record) {
        $no = $index + 1;
        $orderNo = trim($record['orderNo'] ?? '');
        $whCode = $record['whCode'] ?? '-';

        if ($orderNo === '') {
            echo "流水 {$no}（仓库 {$whCode}）：无 OMS 单据号，无法调用 queryCostDetail\n";
            echo "  说明：仓租流水通常不返回单据号，汇总信息以 pageFundsFlow 为准\n\n";
            continue;
        }

        $detailParams = [
            'queryOrderNo' => $orderNo,
            'queryOrderType' => 1,
            'moduleType' => 6,
        ];

        echo "流水 {$no} - 查询单据 {$orderNo} 的费用详情...\n";
        $detailResponse = $oms->queryCostDetail($detailParams);

        if (($detailResponse['code'] ?? 0) !== 200) {
            echo "  ❌ 查询失败: " . ($detailResponse['msg'] ?? '未知错误') . "\n\n";
            continue;
        }

        $detail = $detailResponse['data'] ?? [];
        if (empty($detail)) {
            echo "  ⚠️  暂无费用详情\n\n";
            continue;
        }

        $hasDetail = true;
        echo "  费用单据号: " . ($detail['costNo'] ?? '-') . "\n";
        echo "  总费用: " . ($detail['costTotal'] ?? 0) . ' ' . ($detail['currencyCode'] ?? '') . "\n";
        echo "  创建时间: " . ($detail['createTime'] ?? '-') . "\n";

        $items = $detail['costItemList'] ?? [];
        if (empty($items)) {
            echo "  计费项: （无）\n\n";
            continue;
        }

        echo "  计费项明细:\n";
        echo '  ' . str_pad('计费项', 30) . str_pad('金额', 12) . str_pad('计费时间', 20) . "\n";
        echo '  ' . str_repeat('-', 62) . "\n";
        foreach ($items as $item) {
            echo '  ' .
                 str_pad($item['billItemName'] ?? '-', 30) .
                 str_pad($item['billItemTotal'] ?? 0, 12) .
                 str_pad($item['chargeTime'] ?? '-', 20) . "\n";
        }
        echo "\n";
    }

    if (!$hasDetail) {
        echo "提示：{$targetDate} 仓租流水未返回单据号，计费项明细需从 OMS 后台获取费用单据号后，\n";
        echo "      手动调用 queryCostDetail（queryOrderType=2）查询，例如：\n\n";
        echo "      \$oms->queryCostDetail([\n";
        echo "          'queryOrderNo' => '费用单据号',\n";
        echo "          'queryOrderType' => 2,\n";
        echo "          'moduleType' => 6,\n";
        echo "      ]);\n\n";
    }

    echo "=== 查询完成 ===\n";

} catch (Exception $e) {
    echo "❌ 发生错误: " . $e->getMessage() . "\n";
    exit(1);
}
