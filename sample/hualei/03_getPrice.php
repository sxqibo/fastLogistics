<?php

/**
 * 华磊运费试算 / 查价
 * GET defaultPriceSearchJson.htm
 */
use Sxqibo\Logistics\Hualei;

require_once __DIR__ . '/../../vendor/autoload.php';
$config = require_once __DIR__ . '/config.php';

$app = new Hualei([
    'username' => $config['hualei']['username'] ?? '',
    'password' => $config['hualei']['password'] ?? '',
    'apiBase'  => $config['hualei']['apiBase'] ?? 'http://175.178.75.178:8082',
]);

$result = $app->getPrice('US', 1.5, [
    'length'   => 10,
    'width'    => 10,
    'height'   => 10,
    'postcode' => '10001',
]);

print_r($result);
