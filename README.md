# 跨境电商物流

本次开发，包含了 杰航物流、三态物流、云途物流 的接口的封装！

## 运行环境

PHP7.0+

## 代码贡献

如果您有发现有BUG，欢迎 Star，欢迎 PR ！

## 文档
### 配置文件
```$xslt
<?php
/**
 * 物流公司参数配置
 */
$config = [
    'jieHang' => [
        'clientId' => '',
        'token'    => '',
    ],
    'sanTai'  => [
        'appKey' => '',
        'token'  => '',
        'userId' => '',
    ],
    'yunTu'   => [
        'api_url'       => '',
        'customer_code' => '',
        'customer_key'  => ''
    ]
];

return $config;
```
### 杰航初始化
```
use Sxqibo\Logistics\Jiehang;

require_once '../vendor/autoload.php';
require_once '../config.php';

$clientId = $config['jieHang']['clientId'];
$token    = $config['jieHang']['token'];
$data     = new Jiehang($clientId, $token);

```

### 三态初始化
```
use Sxqibo\Logistics\Santai;

require_once '../vendor/autoload.php';
require_once '../config.php';

$appKey = $config['sanTai']['appKey'];
$token  = $config['sanTai']['token'];
$userId = $config['sanTai']['userId'];
$data   = new Santai($appKey, $token, $userId);
```

### 云途初始化
```
use Sxqibo\Logistics\Yuntu;

require_once '../vendor/autoload.php';
require_once '../config.php';

$code      = $config['yunTu']['code'];
$apiSecret = $config['yunTu']['apiSecret'];
$data      = new Yuntu($code, $apiSecret);
```

### 1.获取运输方式
```
$result = $data->getShipTypes();
```

### 2. 获取费率列表
>说明：用于物流优选

`三态` 这几个字段必传！
```
$param = [
    'weight'     => '0.5',   //请求参数3：重量
    'state'      => 'US',
    'country'    => 'US',    //请求参数1：配送国家
    'length'     => '10',
    'width'      => '10',
    'height'     => '10',
    'priceType'  => '',     //价格类型 1默认 用户折扣价格 2公布价
    'divisionId' => '1',
    'zip_code'   => '12345' //请求参数2：配送邮编
];
$result          = $data->getPrice($param);
print_r($result);
```

`杰航` 和 `云途` 只需要传两个字段
```
$countryCode = 'JP';
$weight      = 5;
$result = $data->getPrice($countryCode, $weight);  //在用，两个参数必传
print_r($result);
```

### 3. 添加订单
>用于“生成运单”
```
//step1:订单
$orderNo     = time();  //客户订单号
$channelCode = 'GNPS';  //运输方式代码(三种物流的方式是不一样的，一定要填对应的物流方式，否则会出错)
$totalValue  = 100;

//step2:收件人
$rCountryCode = 'DE';           //收件人所在国家
$rName        = 'Juan';          //收件人姓
$rAddress     = 'August-cueni-strasse 5';          //收件人详细地址
$rCity        = 'Zwingen';     //收件人所在城市
$rProvince    = 'Zwingen';             //收件人所在省
$rCode        = '04222';       //发件人邮编,必填项,5位数字
$rMobile      = '18803415820';  //发件人手机

//step3:商品
$goods  = [
    [
        'goods_cn_name'       => '商品1',
        'goods_en_name'       => 'shangpin1',
        'goods_number'        => 2,          //申报数量,商品数量
        'goods_single_weight' => 1,          //运单包裹的件数
        'goods_single_worth'  => 3,    //单个产品的申报价值
        'goods_currency_code' => 'USD',      //币种（云途需要）
        'goods_hsCode'        => '01041010', //海关（三态需要）
    ],
    [
        'goods_cn_name'       => '商品2',
        'goods_en_name'       => 'shangpin2',
        'goods_number'        => 3,          //申报数量,商品数量
        'goods_single_worth'  => 3,    //单个产品的申报价值
        'goods_single_weight' => 1,          //运单包裹的件数
        'goods_currency_code' => 'USD',      //币种（云途需要）
        'goods_hsCode'        => '01041010', //海关（三态需要）
    ],
];
$result = $data->createOrder(
    $orderNo, $channelCode,
    $rCountryCode, $rName, $rAddress, $rCity, $rProvince, $rCode, $rMobile,
    $goods);


print_r($result);
```

### 4. 获取订单信息
>说明：用于“更新信息”， `杰航`没有获取订单接口信息
```
$orderNo = 'SFC2WW4159011190015';
$result  = $data->getOrder($orderNo);
print_r($result);

```

### 5. 获取跟踪信息
>说明：用于“运单跟踪”， `云途`没有获取运单跟踪
```
$orderNo1 = 'SFC2WW4159011070015';
//$orderNo2 = '';
$param    = [$orderNo1];
$result   = $data->getTrack($param);
print_r($result);
```

## 商务合作

手机和微信: 18903467858

欢迎商务联系！合作共赢！