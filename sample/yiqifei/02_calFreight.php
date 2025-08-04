<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Yiqifei;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Yiqifei($config);

// 查询运费参数
$params = [
    'departureCode'   => 'SZ',         // 出发地（深圳代码）
    'destinationCode' => 'US',         // 目的国二字码
    'weight'          => 1.5,          // 重量(KG)
    'length'          => 30,           // 长(CM)
    'width'           => 20,           // 宽(CM)
    'height'          => 10,           // 高(CM)
    'productCode'     => 'USE13-MPKY', // 产品代码（美国空运免泡-普货）
];

// 查询运费, 说明：这个查询不出结果
$result = $app->calFreight($params);


print_r($result);

echo "=== 查询运费结果 ===\n";
if ($result['flag']) {
    echo "查询成功！\n";
    if (isset($result['rows']) && !empty($result['rows'])) {
        echo "运费信息：\n";
        foreach ($result['rows'] as $freight) {
            echo "----------------------------------------\n";
            echo sprintf("产品代码: %s\n", $freight['productCode']);
            echo sprintf("产品名称: %s\n", $freight['productName']);
            
            if ($freight['calFreightResult'] === 'success') {
                echo sprintf("基础运费: %.2f\n", $freight['basicFee']);
                echo sprintf("处理费: %.2f\n", $freight['handlingFee']);
                echo sprintf("其他费用: %.2f\n", $freight['otherFee']);
                echo sprintf("总费用: %.2f\n", $freight['totalPrice']);
                
                if (!empty($freight['otherFeeDetails'])) {
                    echo "附加费用明细:\n";
                    foreach ($freight['otherFeeDetails'] as $fee) {
                        echo sprintf("  - %s: %.2f\n", 
                            $fee['surchargeName'], 
                            $fee['surchargePrice']
                        );
                    }
                }
            } else {
                echo "计算失败原因: " . ($freight['failReson'] ?? '未知') . "\n";
            }
            echo "----------------------------------------\n";
        }
    }
} else {
    echo "查询失败！\n";
    echo "错误信息: " . ($result['msg'] ?? $result['obj'] ?? '未知错误') . "\n";
} 