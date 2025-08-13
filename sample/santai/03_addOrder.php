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
 * 03、添加订单
 * @doc https://www.sfcservice.com/api-doc
 */
//step1:订单
$orderNo     = time();   //客户订单号
$channelCode = 'USPE';  //运输方式代码(三种物流的方式是不一样的，一定要填对应的物流方式，否则会出错)
$totalValue  = 50;      //云途不用填,填上也没关系

//step2:收件人
$rCountryCode   = 'US';           //收件人所在国家
$rName          = 'tom';          //收件人姓
$rAddress       = '02638-1915';   //收件人详细地址
$rCity          = 'DENNIS';       //收件人所在城市
$rProvince      = 'MA';           //收件人所在省
$rCode          = '04222';        //发件人邮编,必填项,5位数字
$recipientEmail = 'email@qq.com';        //发件人郵箱,選填项,有的渠道需要
$rMobile        = '415-851-9136'; //发件人手机
$remarks      = ''; //订单备注，用于打印配货单

//step3:商品
$goods = [
    [
        'goods_cn_name'       => '商品1',
        'goods_en_name'       => 'shangpin1',
        'goods_number'        => 2,    //申报数量,商品数量
        'goods_single_worth'  => 3,    //单个产品的申报价值
        'goods_single_weight' => 1,    //运单包裹的件数
    ],
    [
        'goods_cn_name'       => '商品2',
        'goods_en_name'       => 'shangpin2',
        'goods_number'        => 3,    //申报数量,商品数量
        'goods_single_worth'  => 3,    //单个产品的申报价值
        'goods_single_weight' => 1,    //运单包裹的件数
    ],
];

$iossNumber = 'IM4420001201';

$result = $data->createOrder(
    $orderNo, $channelCode,
    $rCountryCode, $rName, $rAddress, $rCity, $rProvince, $rCode, $recipientEmail, $rMobile,
    $goods, '', $iossNumber, $remarks);

print_r($result);
