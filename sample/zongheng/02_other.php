<?php

require __DIR__ . '/vendor/autoload.php';

try {
    $appKey   = '';
    $appToken = '';
    $client   = new \Sxqibo\Logistics\Zongheng($appToken, $appKey);

    // 1.修改订单
    // $orderNo = 'TE1622537565';
    // $result  = $client->updateOrder($orderNo, 1.0);

    // 2.删除订单
    // $orderNo = 'TE1622537565';
    // $result  = $client->deleteOrder($orderNo);

    // 3. 获取订单标签
    // $result = $client->getOrderLabel(['TE1622537565']);
    // dd($result);

    // 4. 获取订单跟踪单号
    // $result = $client->getTrackingNumber('TE1622537565');

    // 5. 获取订单跟踪记录
    // $result = $client->getTrack('TE1622537565');


    // 6. 获取订单费用(按费用种类分组合计费用)
    // $result = $client->getShippingFee('TE1622537565');

    // 7. 获取订单费用明细(业务的每笔费用变动数据)
    // $result = $client->getShippingFeeDetail('TE1622537565');

    // 8. 获取订单重量
    // $result = $client->getOrderWeight('xxx');

    // 9. 费用试算
    // $params = [
    //     'country_code' => 'US',
    //     'weight'       => '1.5'
    // ];
    // $result = $client->feeTrail($params);

    // 10. 获取运输方式
    // $result = $client->getShipTypes();

    // 11. 获取国家
    $result = $client->getCountry();

    dd($result);

} catch (\Exception $e) {
    dd($e->getMessage());
}


