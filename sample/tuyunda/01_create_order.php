<?php

require '../../vendor/autoload.php';

// 创建纵横订单

try {
    $appKey   = 'b6d2fed6f95f83579ac753001d9b9297a3351f1f42089c5f49a81b0e9ca4b2e1';
    $appToken = 'b6d2fed6f95f83579ac753001d9b9297';
    $client   = new \Sxqibo\Logistics\TuYunDa($appToken, $appKey);

    $goods[] =
    $data = [
        // 必填项
        'channelCode'          => 'PK0073',     // 发货方式
        'orderNo'              => 'TE' . time(),    // 客户订单号
        'country_code'         => 'US',     // 收件人国家二字码


        // 收件人
        'receiverName'         => 'tang', // 收货人-姓名
        'receiverPostCode'     => '25340-1221', // 邮编
        'rProvince'            => 'FL', // 收货人-州
        'receiverCity'         => 'City',
        'receiverAddress'      => 'content1content1content1',
        'receiverCountryCode'  => 'US', // 收货人-国家

        // 收货人-座机，手机。美国专线至少填一项
        'phone'                => '1236548',
        'mobile'               => '',
        // 选填
        'email'                => 'jpcn@mpc.com.br', // 收货人-邮箱
        // 商品信息
        'goods'                => [[
            'goods_cn_name'       => '多媒体播放器', // 商品中文品名
            'goods_en_name'       => 'MedialPlayer',
            'goods_number'        => 1,
            'goods_single_weight' => 0.5,
            'goods_single_worth'  => 15.0, // 申报价值
            'currency'            => 'USD', // 申报币种
            'hs_code'             => '', // 商品海关编码（当Channel为【香港FedEx经济，中邮广州挂号小包，中邮广州平邮小包(专用)】时，该属性HsCode必填）
        ]]
    ];

    dd($client->createOrder($data));
} catch (\Exception $e) {
    dd(111, $e->getMessage());
}


