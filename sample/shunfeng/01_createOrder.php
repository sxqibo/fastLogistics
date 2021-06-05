<?php

require __DIR__ . '/vendor/autoload.php';

// 创建顺丰订单
try {
    $accessCode = '';
    $checkWord  = '';
    $client     = new \Sxqibo\Logistics\Shunfeng($accessCode, $checkWord);

    $data = [
        // 必填项
        'channelCode'         => '9',     // 发货方式 国际小包平邮
        'orderNo'             => 'TE' . time(),    // 客户订单号

        // 发件人信息
        'senderMobile'        => '13865325698',
        'senderCompany'       => 'test', // 寄方公司
        'senderContact'       => 'test', // 寄方联系人
        'senderProvince'      => 'guangdong', // 寄方所在省份
        'senderCity'          => 'shenzhen', // 寄方所在城市
        'senderAddress'       => 'detail address', // 寄方详细地址
        'senderCountry'       => 'CN', // 始发地
        'senderPostCode'      => '517057', // 寄方邮编

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
    // "<Response service="OrderService"><Head>OK</Head><Body><OrderResponse orderid="TE1622875810" mailno="SF6041146470069" agent_mailno="UD343057297NL" direction_code="1P-US-JFK"/></Body></Response>"
} catch (\Exception $e) {
    file_put_contents('aa.txt', $e->getMessage());
    dd($e->getMessage());
}


