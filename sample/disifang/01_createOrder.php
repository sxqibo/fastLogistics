<?php

require __DIR__ . '/vendor/autoload.php';

// 创建纵横订单

try {
    $appKey    = '';
    $appSecret = '';
    $client    = new \Sxqibo\Logistics\DiSIFang($appKey, $appSecret);

    $data = [
        // 必填项
        'channelCode'         => 'F3',     // 发货方式
        'orderNo'             => 'TE' . time(),    // 客户订单号

        // 收件人
        'receiverName'        => 'tang', // 收货人-姓名
        'receiverPostCode'    => '25340-1221', // 邮编
        'rProvince'           => 'FL', // 收货人-州
        'receiverCity'        => 'City',
        'receiverAddress'     => 'content1content1content1',
        'receiverCountryCode' => 'US', // 收货人-国家

        // 收货人-座机，手机。美国专线至少填一项
        'receiverPhone'       => '1236548',
        'receiverMobile'      => '1236548',
        // 选填
        'email'               => 'jpcn@mpc.com.br', // 收货人-邮箱
        // 商品信息
        'goods'               => [[
            'goods_cn_name'       => '多媒体播放器', // 商品中文品名
            'goods_en_name'       => 'MedialPlayer',
            'goods_number'        => 1,
            'goods_single_weight' => 1,
            'goods_single_worth'  => 15.0, // 申报价值
            'currency'            => 'USD', // 申报币种
        ]]
    ];

    dd($client->createOrder($data));
} catch (\Exception $e) {
    dd($e->getMessage());
}


