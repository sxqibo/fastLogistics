<?php
//命名空间
use Sxqibo\Logistics\Santai;

require_once '../vendor/autoload.php';
require 'config.php';

$data   = new Santai($appKey, $token, $userId);

/**
 * 02、获取费率列表 （说明：目前在用，栏目：物流公司-物流优选）
 * @doc https://www.sfcservice.com/api-doc
 */
$param = [
    'weight'     => '0.5',   //请求参数3：重量
    'state'      => 'US',
    'country'    => 'US',    //请求参数1：配送国家
    'length'     => '10',
    'width'      => '10',
    'height'     => '10',
    'priceType'  => '',     //价格类型 1默认 用户折扣价格 2公布价
    'divisionId' => '1',
    'zip_code'   => '12345' //请求参数2：配送邮编
];
$result          = $data->getRates($param);
print_r($result);
