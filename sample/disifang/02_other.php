<?php

require __DIR__ . '/vendor/autoload.php';


try {

    $appKey    = '';
    $appSecret = '';

    $client = new \Sxqibo\Logistics\DiSIFang($appKey, $appSecret);
    $result = false;

    // 获取订单
    // $params = [
    //     'orderNo' => 'TE1622703238'
    // ];

    // $result = $client->getOrder($params);

    // 删除订单
    $result = $client->deleteOrder('TE1622703238', 'test');

    // 拦截订单
    // $result = $client->holdOrder('TE1622703238', 'Y', 'test');

    // 更新订单
    // $result = $client->updateOrder('TE1622703238', 10);

    // 根据条件查询快件信息
    // $params = [
    //     'request_no' => '',
    //     'start_time' => '',
    //     'end_time'   => '',
    //     'status'     => ''
    // ];
    // $result = $client->getOrder($params, true);


    // 预估费用查询/运费试算
    // $params = [
    //     'request_no'             => '',
    //     'country_code'           => 'US',
    //     'weight'                 => '1',
    //     'length'                 => '100',
    //     'width'                  => '999',
    //     'height'                 => '999',
    //     'cargo_type'             => 'P',
    //     'logistics_product_code' => '',
    // ];
    // $result = $client->getPrice($params);


    // 获取运输方式
    // $result = $client->getShipTypes(1, true);

    // $result = $client->getCourseList('R', 'CN', true);

    // 获取订单跟踪记录
    // $result = $client->getTrack('1Z8E26Y00366094077');


    // 查询计量单位
    // $result = $client->getMeasureUnit();

    // 查询申报产品种类
    // $result = $client->getCategory();

    dd($result);
} catch (\Exception $e) {
    dd($e->getMessage());
}


