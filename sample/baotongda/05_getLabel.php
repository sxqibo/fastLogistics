<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Baotongda;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Baotongda($config);

// 读取之前保存的订单号
$referenceNo = @file_get_contents(__DIR__ . '/last_reference_no.txt');
if (!$referenceNo) {
    die("请先创建订单并获取订单号！\n");
}

// 获取订单标签参数
$params = [
    // 配置信息
    'configInfo' => [
        'lable_file_type'  => '2',           // 标签文件类型：2-PDF文件
        'lable_paper_type' => '1',           // 纸张类型：1-标签纸
        'lable_content_type' => '1',         // 标签内容类型：1-标签
        'additional_info'   => [
            'lable_print_invoiceinfo' => 'Y',               // 打印配货信息
            'lable_print_buyerid'     => 'N',               // 不打印买家ID
            'lable_print_datetime'    => 'Y',               // 打印日期
            'customsdeclaration_print_actualweight' => 'N'  // 不打印实际重量
        ]
    ],
    // 订单列表
    'listorder' => [
        [
            'reference_no' => $referenceNo    // 客户参考号
        ]
    ]
];

// 获取订单标签
$result = $app->getLabel($params);
print_r($result);


/**
 * Array
 * (
 * [apiservice_code] =>
 * [data] => Array
 * (
 * [0] => Array
 * (
 * [lable_file_type] => 2
 * [lable_file] => http://121.15.2.131:6005/api-lable/pdf/20250729/b00dbb6b-c497-4281-8655-05ac2a532aa8.pdf
 * [lable_content_type] => 1
 * )
 *
 * )
 *
 * [success] => 1
 * [cnmessage] => 获取订单标签成功
 * [enmessage] => 获取订单标签成功
 * [order_id] => 0
 * )
 */