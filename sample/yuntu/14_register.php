<?php

use Sxqibo\Logistics\Yuntu;

require_once '../vendor/autoload.php';
require_once '../config.php';

$code      = $config['yunTu']['code'];
$apiSecret = $config['yunTu']['apiSecret'];
$data      = new Yuntu($code, $apiSecret);


//用户注册所需信息(必填)
$params = [

    "UserName"  => "test126",                //用户名
    "PassWord"  => "123456",                 //密码
    "Contact"   => "老张",                   //联系人
    "Telephone" => "123456",                //联系电话
    "Mobile"    => "19988888888",           //联系电话
    "Name"      => "老张牛",                 //客户名称/公司名称
    "Email"     => "12345@qq.com",          //邮箱
    "Address"   => "杨美地铁站东海王",        //详细地址
    "Platform"  => 2                        //平台 ID(通途平台--2)
];


$result = $data->register($params);


print_r($result);


