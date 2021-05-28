<?php
require __DIR__ . '/vendor/autoload.php';

try {
    $appKey = 'D6140AA383FD8515B09028C586493DDB';
    $userId = '100000';

    $client  = new \Sxqibo\Logistics\Yanwen($appKey, $userId);
    $goods[] =

    $data = [
        // 必填项
        'channelCode' => 140,     // 发货方式
        'orderNo'     => time(),    // 客户订单号
        'sendDate'    => '2014-07-09T00:00:00', // 发货日期，datetime类型

        'receiverName'        => 'tang', // 收货人-姓名
        'receiverPostCode'    => '25340-1221', // 邮编
        'rProvince'           => 'FL', // 收货人-州
        'receiverCity'        => 'City',
        'receiverAddress'     => 'content1content1content1',
        'receiverCountryCode' => 'US', // 收货人-国家

        // 收货人-座机，手机。美国专线至少填一项
        'phone'               => '1236548',
        'mobile'              => '',
        // 选填
        'email'               => 'jpcn@mpc.com.br', // 收货人-邮箱
        // 商品信息
        'goods'               => [[
            'Userid'              => $userId, // 客户号
            'goods_cn_name'       => '多媒体播放器', // 商品中文品名
            'goods_en_name'       => 'MedialPlayer',
            'goods_single_weight' => '213',
            'goods_single_worth'  => '125', // 申报价值
            'currency'            => 'USD', // 申报币种
            'product_brand'       => '', // 产品品牌，中俄SPSR专线此项必填
            'product_size'        => '', // 产品尺寸，中俄SPSR专线此项必填
            'product_color'       => '', // 产品颜色，中俄SPSR专线此项必填
            'product_material'    => '', // 产品材质，中俄SPSR专线此项必填
            'extra'               => 'MedialPlayer', // 多品名 会出现在拣货单上
            'hs_code'             => '', // 商品海关编码（当Channel为【香港FedEx经济，中邮广州挂号小包，中邮广州平邮小包(专用)】时，该属性HsCode必填）
        ]]
    ];

    $result = $client->createOrder($data, true);

    print_r($result);
} catch (\Exception $e) {
    print_r($e->getMessage());
    exit;
}


