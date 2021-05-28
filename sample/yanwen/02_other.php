<?php
require __DIR__ . '/vendor/autoload.php';

try {
    // 测试key
    $appKey = '';
    $userId = '';
    $client = new \Sxqibo\Logistics\Yanwen($appKey, $userId);

    // 1. 获取订单信息
    $params = ['page' => 1, 'code' => 'UG544656195YP'];
    $result = $client->getOrder($params, true);

    // 单标签打印
    // $data = $client->labelPrint('UG544656195YP', 'A4L', true);

    // 多标签打印
    // $strs = 'YW862913494CN,RQ150332025SG';
    // $data = $client->multipleLabelPrint($strs, 'A4L', true);

    // 调整快件状态
    // $status = 0;
    // $epCode = 'YW862913494CN';
    // $result = $client->changeStatus($epCode, $status, true);


    // 获取产品可达国家
    // $result = $client->getCountry(485, true);

    // 获取线上发货渠道
    // $result = $client->getOnlineChannels(true);

} catch (\Exception $e) {
    print_r($e->getMessage());
    exit;
}





