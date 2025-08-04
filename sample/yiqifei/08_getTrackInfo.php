<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Yiqifei;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Yiqifei($config);

// 订单号列表
$orderNumbers = ['FEISZ1708169603YQ'];  // 替换为实际的订单号

// 获取订单追踪信息
$result = $app->getTrackInfo($orderNumbers);

print_r($result);

/**
 * Array
 * (
 * [flag] => 1
 * [msg] =>
 * [trackingInformations] => Array
 * (
 * [0] => Array
 * (
 * [orderNumber] => FEISZ1708169603YQ
 * [stopTrack] =>
 * [trackingInfoDetails] => Array
 * (
 * [0] => Array
 * (
 * [code] =>
 * [createTime] => 2025-07-29 14:08:06
 * [deliveryCompany] => FEDEX-GROUND
 * [deliveryNumber] =>
 * [description] => Item information received.
 * [insideNumber] => FEISZ1708169603YQ
 * [location] =>
 * [statusDesc] =>
 * [trackingStatus] =>
 * )
 *
 * )
 *
 * )
 *
 * )
 *
 * )
 */