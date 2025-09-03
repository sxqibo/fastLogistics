<?php
/**
 * 递四方 - 库存查询
 * 查询指定SKU或批次的库存信息
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

    // 示例1：根据SKU查询库存
    echo "=== 示例1：根据SKU查询库存 ===\n";
    $result1 = $fpx->getInventoryBySku(
        'CES-ST0',           // SKU编号
        '900278',            // 客户操作账号
        'CNDGMA',            // 仓库代码
        1,                   // 页码
        10                   // 每页记录数
    );
    
    echo "根据SKU查询库存结果：\n";
    print_r($result1);

    // 示例2：批量查询多个SKU的库存
    echo "\n=== 示例2：批量查询多个SKU的库存 ===\n";
    $result2 = $fpx->getInventoryBySku(
        ['CES-ST0', 'CES-ST1', 'CES-ST2'],  // SKU编号数组
        '900278',                            // 客户操作账号
        '',                                  // 不指定仓库代码，查询所有仓库
        1,                                   // 页码
        50                                   // 每页记录数
    );
    
    echo "批量查询SKU库存结果：\n";
    print_r($result2);

    // 示例3：根据批次号查询库存
    echo "\n=== 示例3：根据批次号查询库存 ===\n";
    $result3 = $fpx->getInventoryByBatch(
        'BATCH001',          // 批次号
        '900278',            // 客户操作账号
        'GBGBRB',            // 仓库代码
        1,                   // 页码
        10                   // 每页记录数
    );
    
    echo "根据批次号查询库存结果：\n";
    print_r($result3);

    // 示例4：查询所有库存
    echo "\n=== 示例4：查询所有库存 ===\n";
    $result4 = $fpx->getAllInventory(
        '900278',            // 客户操作账号
        '',                  // 不指定仓库代码，查询所有仓库
        1,                   // 页码
        20                   // 每页记录数
    );
    
    echo "查询所有库存结果：\n";
    print_r($result4);

    // 示例5：使用原始API方法查询库存
    echo "\n=== 示例5：使用原始API方法查询库存 ===\n";
    $params = [
        'customer_code' => '900278',
        'warehouse_code' => 'CNDGMA',
        'page_no' => 1,
        'page_size' => 10
    ];
    
    $result5 = $fpx->getInventory($params);
    
    echo "原始API方法查询库存结果：\n";
    print_r($result5);

    // 示例6：格式化库存数据
    echo "\n=== 示例6：格式化库存数据 ===\n";
    if (isset($result1['data']['data']) && !empty($result1['data']['data'])) {
        $formattedData = $fpx->formatInventoryData($result1);
        echo "格式化后的库存数据：\n";
        print_r($formattedData);
    }

} catch (Exception $e) {
    echo "库存查询失败：" . $e->getMessage() . "\n";
}
