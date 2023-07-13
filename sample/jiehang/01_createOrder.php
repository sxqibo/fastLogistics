<?php
//命名空间
use Sxqibo\Logistics\Jiehang;

require_once '../../vendor/autoload.php';
require_once '../config.php';

$clientId = $config['jieHang']['clientId'];
$token    = $config['jieHang']['token'];
$data     = new Jiehang($clientId, $token);

/**
 * 01、 创建快件订单,仓储订单,快递制单
 */

//step1:订单
$orderNo     = time();  //客户订单号
$channelCode = 'GNPS';  //运输方式代码(三种物流的方式是不一样的，一定要填对应的物流方式，否则会出错)

//step2:收件人
$rCountryCode = 'DE';           //收件人所在国家
$rName        = 'Juan';          //收件人姓
$rAddress1    = 'August-cueni-strasse 1';          //收件人详细地址1
$rAddress2    = 'August-cueni-strasse 2';          //收件人详细地址2
$rCity        = 'Zwingen';     //收件人所在城市
$rProvince    = 'Zwingen';             //收件人所在省
$rCode        = '04222';       //发件人邮编,必填项,5位数字
$rMobile      = '18803415820';  //发件人手机
$remarks      = '备注'; //订单备注，用于打印配货单

//step3:商品
$goods = [
    [
        'goods_cn_name'       => '商品1',
        'goods_en_name'       => 'shangpin1',
        'goods_number'        => 2,          //申报数量,商品数量
        'goods_single_worth'  => 3,          //单个产品的申报价值
        'goods_single_weight' => 1,          //运单包裹的件数
    ],
    [
        'goods_cn_name'       => '商品2',
        'goods_en_name'       => 'shangpin2',
        'goods_number'        => 3,          //申报数量,商品数量
        'goods_single_worth'  => 3,          //单个产品的申报价值
        'goods_single_weight' => 1,          //运单包裹的件数
    ],
];

// step4:
$iossNumber = 'IM4420001201';

$result = $data->createOrder(
    $orderNo, $channelCode,
    $rCountryCode, $rName, $rAddress1, $rAddress2, $rCity, $rProvince, $rCode, $rMobile,
    $goods, $iossNumber, $remarks, 1);


print_r($result);

