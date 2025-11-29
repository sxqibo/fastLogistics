<?php

/**
 * GoodCang API 示例 - 根据SKU获取商品长宽高
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

    echo "=== GoodCang API 根据SKU获取商品长宽高 ===\n";
    echo "App Token: " . $appToken . "\n";
    echo "App Key: " . $appKey . "\n\n";

    // 要查询的SKU
    $sku = 'XXX';

    // 设置查询参数（必填：page, pageSize；精确SKU）
    $params = [
        'page' => 1,
        'pageSize' => 20,
        'product_sku' => $sku,
        // 其他可选参数按需补充：
        // 'product_sku_arr' => [],
        // 'product_update_time_from' => '2020-07-15 09:00:00',
        // 'product_update_time_to'   => '2020-07-15 09:00:00',
    ];

    echo "请求参数:\n";
    echo json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 调用API获取商品列表
    echo "正在调用API获取商品列表...\n";
    $result = $goodCang->getProductSkuList($params);

    echo "API调用成功！\n\n";

    // 打印原始响应
    echo "=== 原始响应数据 ===\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 校验并解析数据（V1：ask / message / count / data）
    if (!isset($result['ask'])) {
        echo "警告：响应中缺少 ask 字段\n";
        exit(1);
    }

    echo "=== 响应状态 ===\n";
    echo "状态: " . ($result['ask'] ?? 'N/A') . "\n";
    echo "消息: " . ($result['message'] ?? 'N/A') . "\n";
    if (isset($result['count'])) {
        echo "总数量: " . $result['count'] . "\n";
    }
    echo "\n";

    if ($result['ask'] !== 'Success') {
        echo "获取商品列表失败\n";
        exit(1);
    }

    if (!isset($result['data']) || !is_array($result['data'])) {
        echo "响应中 data 字段不存在或格式错误\n";
        exit(1);
    }

    $rows = $result['data'];
    echo "返回记录数: " . count($rows) . "\n\n";

    // 查找匹配SKU，并输出长宽高
    $found = false;
    foreach ($rows as $index => $row) {
        $rowSku = $row['product_sku'] ?? '';
        if ($rowSku !== $sku) {
            continue;
        }

        $found = true;
        echo "=== 匹配到的商品（第 " . ($index + 1) . " 条）===\n";
        echo "SKU: " . $rowSku . "\n";

        // 首选实收尺寸（Product_real_*），没有则回退到 product_*
        $realLength = $row['Product_real_length'] ?? null;
        $realWidth  = $row['Product_real_width'] ?? null;
        $realHeight = $row['Product_real_height'] ?? null;

        $stdLength  = $row['product_length'] ?? null;
        $stdWidth   = $row['product_width'] ?? null;
        $stdHeight  = $row['product_height'] ?? null;

        echo "--- 实收尺寸（单位CM，如存在）---\n";
        echo "实收长(Product_real_length): " . ($realLength !== null && $realLength !== '' ? $realLength : '无') . "\n";
        echo "实收宽(Product_real_width): " . ($realWidth  !== null && $realWidth  !== '' ? $realWidth  : '无') . "\n";
        echo "实收高(Product_real_height): " . ($realHeight !== null && $realHeight !== '' ? $realHeight : '无') . "\n\n";

        echo "--- 标准尺寸（单位CM）---\n";
        echo "长(product_length): " . ($stdLength !== null && $stdLength !== '' ? $stdLength : '无') . "\n";
        echo "宽(product_width): " . ($stdWidth  !== null && $stdWidth  !== '' ? $stdWidth  : '无') . "\n";
        echo "高(product_height): " . ($stdHeight !== null && $stdHeight !== '' ? $stdHeight : '无') . "\n\n";

        // 如果需要“最终使用尺寸”，可按优先级选择
        $finalLength = $realLength !== null && $realLength !== '' ? $realLength : $stdLength;
        $finalWidth  = $realWidth  !== null && $realWidth  !== '' ? $realWidth  : $stdWidth;
        $finalHeight = $realHeight !== null && $realHeight !== '' ? $realHeight : $stdHeight;

        echo "=== 最终使用的长宽高（优先使用实收尺寸，其次标准尺寸）===\n";
        echo "长: " . ($finalLength !== null && $finalLength !== '' ? $finalLength : '无') . " CM\n";
        echo "宽: " . ($finalWidth  !== null && $finalWidth  !== '' ? $finalWidth  : '无') . " CM\n";
        echo "高: " . ($finalHeight !== null && $finalHeight !== '' ? $finalHeight : '无') . " CM\n";
        echo "==============================================\n\n";
    }

    if (!$found) {
        echo "未在返回结果中找到 SKU = {$sku} 的商品记录\n";
    }

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行数: " . $e->getLine() . "\n";
}
