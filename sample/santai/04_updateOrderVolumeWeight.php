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
 * 04、修改订单重量和长宽高
 * @doc https://www.sfcservice.com/api-doc
 */
$param = [
    'orderCode'        => 'SFC2WW4159011070015',  //要修改的订单号(必填)
    'status'           => 3,  //状态(固定为3)(必填)
    'volumeWeightList' => [   //要修改订单的重量和长宽高,看下面的volumeWeightList，(必填)
        'weight' => '11',  //修改物品重量 KG（非必填）
        'length' => '22',  //修改物品长度 CM（非必填）
        'width'  => '33',   //修改物品宽度 CM（非必填）
        'height' => '44'   //修改物品高度 CM（非必填）
    ]
];
$result = $data->updateOrderVolumeWeight($param);
print_r($result);

