<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Sxqibo\Logistics\Oms;

// 加载配置文件
$config = require __DIR__ . '/../config.php';
$appKey = $config['appKey'] ?? '';
$appSecret = $config['appSecret'] ?? '';

try {
    // 初始化OMS客户端
    $oms = new Oms($appKey, $appSecret);

    echo "=== OMS - 分页查询库龄 ===\n\n";

    // 查询参数
    $params = [
        // 'whCodeList' => ['WH001', 'WH002'],  // 仓库编码列表（可选）
        // 'stockType' => 0,  // 库存类型：0-正品，1-次品（可选）
        // 'stockSku' => 'SKU001',  // 商品sku，支持模糊搜索（可选）
        'stockItemType' => 0,  // 必填：库存颗粒度类型：0-产品库存；1-箱库存(暂不支持)；2-退货库存
        'timeType' => 'statisticDate',  // 统计时间类型：shelfDate-上架日期；statisticDate-库存统计日期（默认）
        // 'startTime' => '2025-01-01',  // 起始时间（yyyy-MM-dd），如果不填，默认前7天
        // 'endTime' => '2025-01-31',  // 结束时间（yyyy-MM-dd）
        'page' => 1,  // 页码，默认第一页
        'pageSize' => 50  // 每页条数，默认50条
    ];

    echo "========================================\n";
    echo "=== 查询库龄信息 ===\n";
    echo "========================================\n\n";

    echo "查询参数:\n";
    foreach ($params as $key => $value) {
        if (is_array($value)) {
            echo "  {$key}: " . json_encode($value, JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "  {$key}: {$value}\n";
        }
    }
    echo "\n";

    echo "正在调用API查询库龄信息...\n";
    $response = $oms->pageStockAge($params, true);  // 开启调试模式

    // 检查响应
    if (!isset($response['code'])) {
        echo "❌ 响应格式异常\n";
        echo "完整响应: " . json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        exit(1);
    }

    if ($response['code'] !== 200) {
        echo "❌ 查询失败\n";
        echo "错误码: {$response['code']}\n";
        echo "错误信息: " . ($response['msg'] ?? '未知错误') . "\n";
        echo "\n完整响应:\n";
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        exit(1);
    }

    echo "✓ 查询成功！\n\n";

    // 解析响应数据
    $data = $response['data'] ?? [];
    $total = $data['total'] ?? 0;
    $page = $data['page'] ?? 1;
    $pageSize = $data['pageSize'] ?? 50;
    $records = $data['records'] ?? [];

    echo "响应消息: " . ($response['msg'] ?? 'Success') . "\n\n";

    echo "=== 统计信息 ===\n";
    echo "总条数: {$total}\n";
    echo "当前页: {$page}\n";
    echo "每页条数: {$pageSize}\n";
    echo "当前页记录数: " . count($records) . "\n";
    echo "总页数: " . ceil($total / $pageSize) . "\n\n";

    if (empty($records)) {
        echo "⚠️  未找到库龄记录\n";
        exit(0);
    }

    // 统计信息
    $totalStockAmount = 0;
    $totalStockAge = 0;
    $warehouseStats = [];
    $stockTypeStats = [];

    foreach ($records as $record) {
        $stockAmount = isset($record['totalAmount']) ? intval($record['totalAmount']) : 0;
        $stockAge = isset($record['stockAge']) ? intval($record['stockAge']) : 0;
        
        $totalStockAmount += $stockAmount;
        $totalStockAge += $stockAge * $stockAmount;  // 加权平均

        $whCode = isset($record['whCode']) ? $record['whCode'] : '未知';
        if (!isset($warehouseStats[$whCode])) {
            $warehouseStats[$whCode] = [
                'name' => $record['whName'] ?? $whCode,
                'count' => 0,
                'amount' => 0
            ];
        }
        $warehouseStats[$whCode]['count']++;
        $warehouseStats[$whCode]['amount'] += $stockAmount;

        $stockType = isset($record['stockType']) ? intval($record['stockType']) : 0;
        $stockTypeText = $stockType === 0 ? '正品' : '次品';
        if (!isset($stockTypeStats[$stockTypeText])) {
            $stockTypeStats[$stockTypeText] = 0;
        }
        $stockTypeStats[$stockTypeText] += $stockAmount;
    }

    $avgStockAge = $totalStockAmount > 0 ? round($totalStockAge / $totalStockAmount, 2) : 0;

    echo "=== 库存统计 ===\n";
    echo "总库存数量: {$totalStockAmount}\n";
    echo "平均库龄: {$avgStockAge} 天\n\n";

    echo "=== 按仓库统计 ===\n";
    foreach ($warehouseStats as $whCode => $stats) {
        echo "  {$whCode} ({$stats['name']}):\n";
        echo "    SKU数量: {$stats['count']}\n";
        echo "    库存数量: {$stats['amount']}\n";
    }
    echo "\n";

    echo "=== 按库存类型统计 ===\n";
    foreach ($stockTypeStats as $type => $amount) {
        echo "  {$type}: {$amount}\n";
    }
    echo "\n";

    // 显示详细记录
    echo "=== 库龄明细 ===\n";
    echo str_pad("序号", 6) . 
         str_pad("仓库编码", 15) . 
         str_pad("仓库名称", 20) . 
         str_pad("SKU", 25) . 
         str_pad("产品名称", 30) . 
         str_pad("库存类型", 10) . 
         str_pad("库存数量", 12) . 
         str_pad("库龄(天)", 12) . 
         str_pad("上架日期", 15) . 
         str_pad("统计日期", 15) . "\n";
    echo str_repeat("-", 160) . "\n";

    foreach ($records as $index => $record) {
        $no = $index + 1;
        $whCode = isset($record['whCode']) ? $record['whCode'] : '-';
        $whName = isset($record['whName']) ? mb_substr($record['whName'], 0, 18) : '-';
        $sku = isset($record['sku']) ? mb_substr($record['sku'], 0, 23) : '-';
        $productName = isset($record['productName']) ? mb_substr($record['productName'], 0, 28) : '-';
        $stockType = isset($record['stockType']) ? ($record['stockType'] == 0 ? '正品' : '次品') : '-';
        $totalAmount = isset($record['totalAmount']) ? $record['totalAmount'] : 0;
        $stockAge = isset($record['stockAge']) ? $record['stockAge'] : 0;
        $shelfDate = isset($record['shelfDate']) ? $record['shelfDate'] : '-';
        $statisticDate = isset($record['statisticDate']) ? $record['statisticDate'] : '-';

        echo str_pad($no, 6) . 
             str_pad($whCode, 15) . 
             str_pad($whName, 20) . 
             str_pad($sku, 25) . 
             str_pad($productName, 30) . 
             str_pad($stockType, 10) . 
             str_pad($totalAmount, 12) . 
             str_pad($stockAge, 12) . 
             str_pad($shelfDate, 15) . 
             str_pad($statisticDate, 15) . "\n";
    }

    echo "\n=== 查询完成 ===\n";

} catch (Exception $e) {
    echo "❌ 发生错误: " . $e->getMessage() . "\n";
    echo "错误堆栈:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}