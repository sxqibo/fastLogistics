<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Wanb;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Wanb($config);

// 获取产品服务
$result = $app->getProducts();
print_r($result);

/**
 * 成功返回示例：
 * Array
 * (
 * [Data] => Array
 * (
 * [ShippingMethods] => Array
 * (
 * [0] => Array
 * (
 * [Code] => 3HPA
 * [Name] => 3HPA: Test Product - 3HPA
 * [IsTracking] => 1
 * [IsVolumeWeight] => 1
 * [MaxVolumeWeightInCm] => 0
 * [MaxWeightInKg] =>
 * [Region] =>
 * [Description] =>
 * )
 *
 * [1] => Array
 * (
 * [Code] => TEST_PRODUCT
 * [Name] => TEST_PRODUCT: test
 * [IsTracking] => 1
 * [IsVolumeWeight] => 1
 * [MaxVolumeWeightInCm] => 0
 * [MaxWeightInKg] =>
 * [Region] =>
 * [Description] =>
 * )
 *
 * [2] => Array
 * (
 * [Code] => TTUSEXR1
 * [Name] => TTUSEXR1: 美国快线含电
 * [IsTracking] => 1
 * [IsVolumeWeight] => 1
 * [MaxVolumeWeightInCm] => 0
 * [MaxWeightInKg] =>
 * [Region] =>
 * [Description] =>
 * )
 *
 * [3] => Array
 * (
 * [Code] => TTUSEXRPH1
 * [Name] => TTUSEXRPH1: 美国快线普货
 * [IsTracking] =>
 * [IsVolumeWeight] =>
 * [MaxVolumeWeightInCm] => 0
 * [MaxWeightInKg] =>
 * [Region] =>
 * [Description] =>
 * )
 *
 * [4] => Array
 * (
 * [Code] => ECSLR2
 * [Name] => ECSLR2: 美国敏感专线
 * [IsTracking] =>
 * [IsVolumeWeight] =>
 * [MaxVolumeWeightInCm] => 0
 * [MaxWeightInKg] =>
 * [Region] =>
 * [Description] =>
 * )
 *
 * [5] => Array
 * (
 * [Code] => EUSLR
 * [Name] => EUSLR: 欧洲测试
 * [IsTracking] => 1
 * [IsVolumeWeight] => 1
 * [MaxVolumeWeightInCm] => 0
 * [MaxWeightInKg] =>
 * [Region] =>
 * [Description] =>
 * )
 *
 * [6] => Array
 * (
 * [Code] => EUEXR
 * [Name] => EUEXR: 欧洲测试2
 * [IsTracking] => 1
 * [IsVolumeWeight] => 1
 * [MaxVolumeWeightInCm] => 0
 * [MaxWeightInKg] =>
 * [Region] =>
 * [Description] =>
 * )
 *
 * [7] => Array
 * (
 * [Code] => 3H
 * [Name] => 3H: Test Product - 3H
 * [IsTracking] => 1
 * [IsVolumeWeight] => 1
 * [MaxVolumeWeightInCm] => 0
 * [MaxWeightInKg] =>
 * [Region] =>
 * [Description] =>
 * )
 *
 * [8] => Array
 * (
 * [Code] => EUSLPHR
 * [Name] => EUSLPHR: EUSLPHR
 * [IsTracking] =>
 * [IsVolumeWeight] =>
 * [MaxVolumeWeightInCm] => 0
 * [MaxWeightInKg] =>
 * [Region] =>
 * [Description] =>
 * )
 *
 * )
 *
 * )
 *
 * [Succeeded] => 1
 * [Error] =>
 * )
 */ 