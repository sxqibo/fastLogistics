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
 * 14、新建国内快递交货单
 *
 * @doc https://www.sfcservice.com/api-doc
 */
$companyName = '';
$packageId   = '';
$sfcNumber   = 'SFC2WW4159011070015';

$result = $data->createExpressWaybill($companyName, $packageId, $sfcNumber);
print_r($result);

