<?php

require '../../vendor/autoload.php';

try {
    $appKey   = '';
    $appToken = '';
    $client   = new \Sxqibo\Logistics\TuYunDa($appToken, $appKey);

    // 1.取消订单
    // $orderNo = 'TYDCX21121353YQ';
    // $result  = $client->cancelOrder($orderNo);

    // 2.作废订单
    // $params = [
    //     'orderNo' => 'TYDCX21121354YQ',
    //     'type'    => '1',
    //     'remark'  => '拦截原因',
    // ];
    // $result  = $client->interceptOrder($params);

    // 4.修改订单重量
    // $params = [
    //     'orderNo' => 'TE1639992841',
    //     'weight'  => 1.5,
    // ];
    // $result  = $client->updateOrder($params);

    // 5.查询订单明细
    // $orderNo = 'TE1639993934';
    // $result  = $client->getOrder($orderNo);

    // 6. 运费试算
    // $params = [
    //     'country_code' => 'US',
    //     'weight'       => '1.5'
    // ];
    // $result = $client->getPrice($params);

    // 7. 轨迹查询
    // $result = $client->getTrack('');

    // 8. 获取全部运输方式
    // $result = $client->getShipTypes();

    // 9. 获取订单标签
    // $orderNo = 'TYDCX21121355YQ';
    // $result = $client->getOrderLabel($orderNo);
    // dd($result);

    // 10. 获取订单跟踪单号
    // $orderNo = ['TE1639993143'];
    // $result = $client->getTrackingNumber($orderNo);

    // 11. 获取订单费用明细(业务的每笔费用变动数据)
    // $result = $client->getShippingFeeDetail('TE1639993143');

    // 12. 获取国家
    $result = $client->getCountry();

    // 13. 获取打印模板
    // $result = $client->getPrintTemplateName();

    // 14. 根据模板获取发票/配货单
    // $params = [
    //     'template_id' => '57',
    //     'codes'       => ["QGAUSE19041600000381"]
    // ];
    // $result = $client->getLabelByTemplate($params);


    dd($result);

} catch (\Exception $e) {
    dd($e->getMessage());
}


