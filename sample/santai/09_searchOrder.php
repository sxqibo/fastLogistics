<?php
//命名空间
use Sxqibo\Logistics\Santai;

require_once '../vendor/autoload.php';
require_once '../config.php';

$appKey = $config['sanTai']['appKey'];
$token  = $config['sanTai']['token'];
$userId = $config['sanTai']['userId'];
$data   = new Santai($appKey, $token, $userId);

/**
 * 09、获取订单信息
 *
 * @doc https://www.sfcservice.com/api-doc
 */
/**
 * 客户想要获取参数:
 *
 * 参数1：国际配送单号
 * 参数2：末端配送单号
 *
 * 参数3：计费重量
 * 参数4：计费金额
 * 参数5：实际重量
 */
$orderNo = 'SFC2WW4159011190015';
$result  = $data->getOrder($orderNo);
print_r($result);

