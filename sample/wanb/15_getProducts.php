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
 *     [Code] => 0
 *     [Message] => success
 *     [Data] => Array
 *         (
 *             [0] => Array
 *                 (
 *                     [Code] => USPS
 *                     [Name] => USPS专线
 *                     [NameEn] => USPS Express
 *                     [Description] => 美国专线，时效5-7天
 *                     [Status] => 1
 *                     [MinWeight] => 0.1
 *                     [MaxWeight] => 20.0
 *                     [WeightStep] => 0.1
 *                     [VolumeRatio] => 6000
 *                     [SupportBatteryType] => 0,1,2
 *                     [SupportInsurance] => 1
 *                     [MaxInsuranceAmount] => 1000.00
 *                     [InsuranceRate] => 0.02
 *                     [Remark] => 普通包裹，不含电池
 *                 )
 *         )
 * )
 */ 