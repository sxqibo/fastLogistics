<?php
//命名空间
use Sxqibo\Logistics\Santai;

require_once '../vendor/autoload.php';
require 'config.php';

$data = new Santai($appKey, $token, $userId);

/**
 * 08、地址标签打印
 *
 * @doc https://www.sfcservice.com/api-doc
 */
$orderNo    = 'SFC2WW4159011070015';
$printType  = '';
$printType2 = '';
$printSize  = '';

$result     = $data->addressPrint($orderNo, $printType, $printType2, $printSize);

print_r($result);

