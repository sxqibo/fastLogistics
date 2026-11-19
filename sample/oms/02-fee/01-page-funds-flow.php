<?php

/**
 * OMS 仓租 SKU 明细查询示例（2026-06-29）
 *
 * 字段来源说明：
 * - pageStockAge   提供 SKU/产品/库龄/尺寸/体积等（与 OMS 库龄查询一致）
 * - pageFundsFlow  提供当日汇总费用、计费时间、币种
 * - 仓租单价/仓租金额/最小计费体积/体积进位：当前 OMS 公开 OpenAPI 未返回，仅 OMP 导出可见
 *
 * pageStockAge: https://apidoc-oms.xlwms.com/reference/pagestockageusingpost
 * pageFundsFlow: https://apidoc-oms.xlwms.com/reference/pagefundsflowusingpost
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use Sxqibo\Logistics\Oms;

$config = require __DIR__ . '/../config.php';
$appKey = $config['appKey'] ?? '';
$appSecret = $config['appSecret'] ?? '';

$targetDate = '2026-06-29';
$pageSize = 50;

$stockTypeMap = [0 => '正品', 1 => '次品'];

/**
 * 计算原始体积 CBM（长*宽*高 cm * 数量）
 */
function calcOriginVolume(float $length, float $width, float $height, int $qty): float
{
    return round($length * $width * $height / 1000000 * $qty, 6);
}

/**
 * 计算计费体积（与 OMP 规则一致：单件体积与最小计费体积取大后 * 数量）
 */
function calcBilledVolume(float $originVolume, float $minBillVolume, int $qty): float
{
    $minTotal = round($minBillVolume * $qty, 6);
    return max($originVolume, $minTotal);
}

try {
    $oms = new Oms($appKey, $appSecret);

    echo "=== OMS - 查询 {$targetDate} 仓租 SKU 明细 ===\n\n";

    $summaryResponse = $oms->pageFundsFlow([
        'moduleTypeList' => [6],
        'timeType' => 'statisticDate',
        'startTime' => "{$targetDate} 00:00:00",
        'endTime' => "{$targetDate} 23:59:59",
        'page' => 1,
        'pageSize' => 10,
    ]);

    $chargeTime = '-';
    $currency = 'EUR';
    $dayTotalCost = 0;
    if (($summaryResponse['code'] ?? 0) === 200) {
        foreach ($summaryResponse['data']['records'] ?? [] as $summary) {
            $dayTotalCost += floatval($summary['costTotal'] ?? 0);
            $chargeTime = $summary['costTime'] ?? $chargeTime;
            $currency = $summary['currencyCode'] ?? $currency;
        }
    }

    $baseParams = [
        'stockItemType' => 0,
        'timeType' => 'statisticDate',
        'startTime' => $targetDate,
        'endTime' => $targetDate,
        'pageSize' => $pageSize,
    ];

    $records = [];
    $page = 1;
    $total = 0;

    do {
        $response = $oms->pageStockAge(array_merge($baseParams, ['page' => $page]));
        if (($response['code'] ?? 0) !== 200) {
            echo "❌ pageStockAge 查询失败: " . ($response['msg'] ?? '未知错误') . "\n";
            exit(1);
        }
        $data = $response['data'] ?? [];
        $total = intval($data['total'] ?? 0);
        $records = array_merge($records, $data['records'] ?? []);
        $page++;
    } while (count($records) < $total);

    if (empty($records)) {
        echo "⚠️  未找到 {$targetDate} 的仓租 SKU 明细\n";
        exit(0);
    }

    echo "当日汇总费用: {$dayTotalCost} {$currency}  计费时间: {$chargeTime}\n";
    echo "SKU 明细条数: " . count($records) . "\n";
    echo "说明: 仓租单价/仓租金额/最小计费体积/体积进位 需 OMP 导出，OpenAPI 暂未提供\n\n";

    $headers = [
        'SKU', 'Product name/产品名称', 'Stock property/库存属性',
        'Put away date/上架日期', 'Storage Date/仓租日期', 'Charge Date/计费时间',
        'Stock age (day)/库龄（天）', 'Qty/数量', 'Dimension/尺寸（长*宽*高）',
        'Dimension unit/尺寸单位', 'Minimum Volume/最小计费体积', 'Origin Volume/原始体积',
        'Billed volume/计费体积', 'Volume Rounding/体积进位', 'Volume unit/体积单位',
        'Storage price/仓租单价', 'Storage fee/仓租金额', 'Currency/币种',
    ];

    echo implode("\t", $headers) . "\n";

    foreach ($records as $record) {
        $length = floatval($record['length'] ?? 0);
        $width = floatval($record['width'] ?? 0);
        $height = floatval($record['height'] ?? 0);
        $qty = intval($record['totalAmount'] ?? 0);
        $originVolume = calcOriginVolume($length, $width, $height, $qty);

        $row = [
            $record['sku'] ?? '',
            $record['productName'] ?? '',
            $stockTypeMap[intval($record['stockType'] ?? 0)] ?? '',
            $record['shelfDate'] ?? '',
            $record['statisticDate'] ?? '',
            $chargeTime,
            $record['stockAge'] ?? '',
            $qty,
            sprintf('%.3f*%.3f*%.3f', $length, $width, $height),
            'cm',
            '',  // 最小计费体积 - API 未提供
            number_format($originVolume, 6, '.', ''),
            number_format($originVolume, 6, '.', ''),  // 计费体积需最小体积规则，API 未提供 minVolume
            '',  // 体积进位 - API 未提供
            'CBM',
            '',  // 仓租单价 - API 未提供
            '',  // 仓租金额 - API 未提供
            $currency,
        ];

        echo implode("\t", $row) . "\n";
    }

    echo "\n=== 示例：XWR-QS-DianChi 完整字段 ===\n";
    foreach ($records as $record) {
        if (($record['sku'] ?? '') !== 'XWR-QS-DianChi') {
            continue;
        }
        $length = floatval($record['length'] ?? 0);
        $width = floatval($record['width'] ?? 0);
        $height = floatval($record['height'] ?? 0);
        $qty = intval($record['totalAmount'] ?? 0);
        $originVolume = calcOriginVolume($length, $width, $height, $qty);

        $detail = [
            'SKU' => $record['sku'],
            'Product name/产品名称' => $record['productName'],
            'Stock property/库存属性' => $stockTypeMap[intval($record['stockType'] ?? 0)] ?? '',
            'Put away date/上架日期' => $record['shelfDate'],
            'Storage Date/仓租日期' => $record['statisticDate'],
            'Charge Date/计费时间' => $chargeTime,
            'Stock age (day)/库龄（天）' => $record['stockAge'],
            'Qty/数量' => $qty,
            'Dimension/尺寸（长*宽*高）' => sprintf('%.3f*%.3f*%.3f', $length, $width, $height),
            'Dimension unit/尺寸单位' => 'cm',
            'Minimum Volume/最小计费体积' => '(API未提供，OMP导出为0.001000)',
            'Origin Volume/原始体积' => number_format($originVolume, 6, '.', ''),
            'Billed volume/计费体积' => number_format($originVolume, 6, '.', ''),
            'Volume Rounding/体积进位' => '(API未提供，OMP导出为不进位)',
            'Volume unit/体积单位' => 'CBM',
            'Storage price/仓租单价' => '(API未提供，OMP导出为2.5000)',
            'Storage fee/仓租金额' => '(API未提供，OMP导出为0.1053)',
            'Currency/币种' => $currency,
        ];
        foreach ($detail as $label => $value) {
            echo "  {$label}\t{$value}\n";
        }
        break;
    }

    echo "\n=== 查询完成 ===\n";

} catch (Exception $e) {
    echo "❌ 发生错误: " . $e->getMessage() . "\n";
    exit(1);
}
