<?php
//命名空间
use Sxqibo\Logistics\Santai;

require_once __DIR__ . '/../../vendor/autoload.php';
$config = require_once __DIR__ . '/config.php';

$appKey = $config['sanTai']['appKey'];
$token  = $config['sanTai']['token'];
$userId = $config['sanTai']['userId'];
$data   = new Santai($appKey, $token, $userId);

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

