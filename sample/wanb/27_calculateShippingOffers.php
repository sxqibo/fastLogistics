<?php

/**
 * 万邦计价查询 / 运费试算
 * POST api/services/calculateShippingOffers
 */
use Sxqibo\Logistics\Wanb;

require_once __DIR__ . '/../../vendor/autoload.php';
$config = require_once __DIR__ . '/config.php';

$app = new Wanb([
    'accountNo' => $config['wanb']['accountNo'] ?? '',
    'token'     => $config['wanb']['token'] ?? '',
]);

$result = $app->calculateShippingOffers([
    'CountryCode'   => 'US',
    'WarehouseCode' => 'SZ',
    'WeightInKg'    => 1,
    'TotalVolume'   => [
        'Length' => 10,
        'Width'  => 10,
        'Height' => 10,
        'Unit'   => 'CM',
    ],
    'PostCode' => '10001',
]);

print_r($result);
