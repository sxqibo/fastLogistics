<?php
//命名空间
use Sxqibo\Logistics\Santai;

require_once '../vendor/autoload.php';
require 'config.php';

$data = new Santai($appKey, $token, $userId);

/**
 * 03、添加订单
 * @doc https://www.sfcservice.com/api-doc
 */
$param  = [

    //step1（2）
    'opDivision'        => 1,               // required, 操作分拨中心 int, 详细请看：https://www.sfcservice.com/api-doc/right/lang/cn#division_list
    'orderStatus'       => 'preprocess',    // required, 订单状态:confirmed(已确认)、preprocess(预处理)、sumbmitted(已交寄)

    //step2:收件人信息（8）
    'recipientName'     => 'tom',           // required, （传参）收件人
    'recipientCountry'  => 'US',            // required, （传参）国家
    'shippingMethod'    => 'USPE',          // required, （传参）运输方式
    'recipientState'    => 'MA',            // required, （传参）收件州省
    'recipientCity'     => 'DENNIS',        // required, （传参）收件城市
    'recipientAddress'  => '203 MAIN ST',   // required, （传参）收件地址
    'recipientZipCode'  => '02638-1915',    // required, （传参）收件地址
    'recipientPhone'    => '415-851-9136',  // required, （传参）收件电话

    //step3:各种配置信息（1）
    'goodsDeclareWorth' => 8,               // required, （目前不知道）总申报价值 float，备注：这个是否是订单价格.注：这个是订单总价格

    //step4:商品信息（4）
    'goodsDetails'      => [                //required
        [
            'detailDescription'   => 'en desc',   // required, （传参）物品描述/英文描述
            'detailDescriptionCN' => '中文描述',   // required, （传参）物品描述/中文描述
            'detailQuantity'      => 2,           // required, （传参）数量 int
            'detailWorth'         => 4,           // required, （传参）单个物品申报价
        ]
    ]
];
$result = $data->addOrder($param);
print_r($result);

