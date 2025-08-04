<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sxqibo\Logistics\Yiqifei;

// 获取配置
$config = require_once __DIR__ . '/config.php';

// 初始化
$app = new Yiqifei($config);

// 获取可用的物流产品
$result = $app->getProducts();

print_r($result);

echo "=== 可用物流产品列表 ===\n";
if (isset($result['productInfos']) && is_array($result['productInfos'])) {
    foreach ($result['productInfos'] as $product) {
        echo sprintf(
            "产品名称: %s\n产品代码: %s\n支持国家: %s\n\n",
            $product['productName'],
            $product['productCode'],
            implode(', ', $product['countryCodes'])
        );
    }
} else {
    echo "获取产品列表失败：\n";
    print_r($result);
}


/**
 * /Applications/EServer/childApp/php/php-8.3/bin/php -c /Applications/EServer/etc/php-8.3/etc/php.ini /Users/mac/Documents/wwwroot/code-github-sxqibo/02-fast/fastLogistics/sample/yiqifei/01_getProducts.php
 * === 可用物流产品列表 ===
 * 产品名称: 欧洲空运6000-带电
 * 产品代码: EUE06-SZ-D
 * 支持国家: DE, FR, ES, BE, LU, NL, AT, CZ, SK, HU, PL, IT, SE, DK, FI, BG, EE, LT, LV, PT, RO, SI, HR
 *
 * 产品名称: 英国空运免泡-带电
 * 产品代码: UK-SZ-MP-D
 * 支持国家: GB, GB, GB, GB, GB, GB
 *
 * 产品名称: 欧洲空运免泡-普货
 * 产品代码: EUB-SZ-MP
 * 支持国家: CZ, SK, LU, DK, LT, EE, FR, PL, NL, BG, HU, LV, BE, SE, SI, AT, ES, IT, DE, FI, PT, RO, HR, PL, PL, PL, PL, PL, BE, BE, BE, BE, BE, DE, DE, DE, DE, DE, CZ, CZ, CZ, CZ, CZ, HU, HU, HU, HU, HU, DK, DK, DK, DK, DK, RO, RO, RO, RO, RO, PT, PT, PT, PT, PT, SE, SE, SE, SE, SE, IT, IT, IT, IT, IT, LV, LV, LV, LV, LV, AT, AT, AT, AT, AT, LT, LT, LT, LT, LT, BG, BG, BG, BG, BG, EE, EE, EE, EE, EE, FR, FR, FR, FR, FR, FI, FI, FI, FI, FI, SK, SK, SK, SK, SK, SI, SI, SI, SI, SI, ES, ES, ES, ES, ES, NL, NL, NL, NL, NL, LU, LU, LU, LU, LU, HR, HR, HR, HR, HR
 *
 * 产品名称: 美国空运免泡-带电
 * 产品代码: USE13-MPKY-02
 * 支持国家: US, US, US, US
 *
 * 产品名称: 欧洲空运免泡-带电
 * 产品代码: EUB-SZ-MP-D
 * 支持国家: PL, BE, DE, CZ, HU, DK, RO, PT, SE, IT, LV, AT, LT, BG, EE, FR, FI, SK, SI, ES, NL, LU, HR, PL, PL, PL, PL, PL, BE, BE, BE, BE, BE, DE, DE, DE, DE, DE, CZ, CZ, CZ, CZ, CZ, HU, HU, HU, HU, HU, DK, DK, DK, DK, DK, RO, RO, RO, RO, RO, PT, PT, PT, PT, PT, SE, SE, SE, SE, SE, IT, IT, IT, IT, IT, LV, LV, LV, LV, LV, AT, AT, AT, AT, AT, LT, LT, LT, LT, LT, BG, BG, BG, BG, BG, EE, EE, EE, EE, EE, FR, FR, FR, FR, FR, FI, FI, FI, FI, FI, SK, SK, SK, SK, SK, SI, SI, SI, SI, SI, ES, ES, ES, ES, ES, NL, NL, NL, NL, NL, LU, LU, LU, LU, LU, HR, HR, HR, HR, HR
 *
 * 产品名称: 欧洲空运10000-普货
 * 产品代码: EUB07-SZ-G-10
 * 支持国家: DK, AT, LT, SI, PL, FR, ES, FI, LU, EE, SE, HU, DE, IT, SK, NL, BG, BE, LV, CZ, RO, PT, HR
 *
 * 产品名称: 英国空运6000-普货
 * 产品代码: UK03-SZ
 * 支持国家: GB
 *
 * 产品名称: 英国空运6000-带电
 * 产品代码: UK03-SZ-D
 * 支持国家: GB
 *
 * 产品名称: 美国海运-免泡专线
 * 产品代码: AME02-SZ-TH-D
 * 支持国家: US, US, US, US
 *
 * 产品名称: 加拿大空运8000-普货
 * 产品代码: CA01-SZ-MPKY
 * 支持国家: CA
 *
 * 产品名称: 英国空运10000-带电
 * 产品代码: UK03-SZ-10-D
 * 支持国家: GB
 *
 * 产品名称: 英国空运免泡-普货
 * 产品代码: UK02-SZ-MP
 * 支持国家: GB, GB, GB, GB, GB, GB
 *
 * 产品名称: 欧洲空运免泡-普货特惠
 * 产品代码: EUB2-SZ-MP
 * 支持国家: DE, IE, EE, BG, LU, PT, LT, SK, RO, FR, HU, SE, AT, SI, DK, ES, FI, NL, IT, PL, CZ, LV, BE, HR, GR, PL, PL, PL, PL, PL, BE, BE, BE, BE, BE, DE, DE, DE, DE, DE, CZ, CZ, CZ, CZ, CZ, HU, HU, HU, HU, HU, DK, DK, DK, DK, DK, RO, RO, RO, RO, RO, PT, PT, PT, PT, PT, SE, SE, SE, SE, SE, IT, IT, IT, IT, IT, LV, LV, LV, LV, LV, AT, AT, AT, AT, AT, LT, LT, LT, LT, LT, BG, BG, BG, BG, BG, EE, EE, EE, EE, EE, FR, FR, FR, FR, FR, FI, FI, FI, FI, FI, SK, SK, SK, SK, SK, SI, SI, SI, SI, SI, ES, ES, ES, ES, ES, NL, NL, NL, NL, NL, LU, LU, LU, LU, LU, HR, HR, HR, HR, HR, IE, IE, IE, IE, IE, GR, GR, GR, GR, GR
 *
 * 产品名称: 美国空运6000-普货
 * 产品代码: USE13-SZ-06
 * 支持国家: US
 *
 * 产品名称: 美国空运10000-普货
 * 产品代码: USE13-SZ-10
 * 支持国家: US
 *
 * 产品名称: 美国空运10000-带电
 * 产品代码: USE13-MPKY-10
 * 支持国家: US
 *
 * 产品名称: 美国空运6000-带电
 * 产品代码: USE13-MPKY-06
 * 支持国家: US
 *
 * 产品名称: 欧洲空运6000-普货特惠
 * 产品代码: EUB206-SHSZ-MP-IOSS
 * 支持国家: EE, SK, LV, DK, ES, NL, HU, DE, BE, SI, IT, LT, FI, PL, FR, PT, SE, RO, LU, BG, IE, CZ, HR, AT, GR
 *
 * 产品名称: 欧洲空运10000-普货特惠
 * 产品代码: EUB210-SHSZ-MP-IOSS
 * 支持国家: DK, CZ, IE, BG, NL, IT, DE, HU, FI, SK, FR, LV, RO, SE, LU, EE, BE, PL, AT, SI, PT, HR, ES, LT, GR
 *
 * 产品名称: 美国海运-8000
 * 产品代码: AME02-SZ-TH-D-01
 * 支持国家: US
 *
 * 产品名称: 美国空运18000-普货
 * 产品代码: USE13-SZ-18
 * 支持国家: US
 *
 * 产品名称: 美国空运18000-带电
 * 产品代码: USE13-MPSZ-18
 * 支持国家: US
 *
 * 产品名称: 英国空运18000-普货
 * 产品代码: UK03-SZ-18
 * 支持国家: GB
 *
 * 产品名称: 英国空运18000-带电
 * 产品代码: UK03-SZ-18-D
 * 支持国家: GB
 *
 * 产品名称: 欧洲空运18000-普货
 * 产品代码: EUB07-SZ-G-18
 * 支持国家: PT, AT, PL, BE, ES, BG, DE, CZ, HU, LT, SE, LU, LV, NL, RO, DK, FI, SK, HR, FR, EE, SI, IT
 *
 * 产品名称: 欧洲空运18000-带电
 * 产品代码: EUB07-SZ-G-18-D
 * 支持国家: HU, RO, SI, BG, EE, FI, FR, AT, SE, IT, DK, LT, DE, LU, BE, NL, PL, SK, ES, CZ, HR, PT, LV
 *
 * 产品名称: 欧洲空运18000-普货特惠
 * 产品代码: EUB218-SHSZ-MP-IOSS
 * 支持国家: HU, ES, BG, SE, IT, RO, LU, BE, GR, SI, NL, DK, CZ, PT, AT, LV, SK, PL, LT, DE, FI, HR, EE, IE, FR
 *
 * 产品名称: 欧洲卡航特快专线6000
 * 产品代码: EU-RT-6-SZ
 * 支持国家: GR, HR, IT, NL, RO, HU, CZ, EE, PT, FR, DK, ES, DE, FI, LV, SI, BG, SE, LT, IE, BE, LU, SK, PL, AT
 *
 * 产品名称: 欧洲卡航特快专线10000
 * 产品代码: EU-RT-10-SZ
 * 支持国家: DK, PT, LT, FI, BG, FR, IE, EE, CZ, HU, LV, NL, LU, BE, SK, HR, GR, AT, PL, SE, DE, RO, IT, ES, SI
 *
 * 产品名称: 欧洲卡航特快专线18000
 * 产品代码: EU-RT-18-SZ
 * 支持国家: SE, SI, DK, ES, GR, BE, FR, LV, PL, BG, IT, FI, DE, EE, LU, PT, HU, NL, RO, AT, SK, HR, LT, IE, CZ
 *
 * 产品名称: 欧洲卡航标准专线6000
 * 产品代码: EU-RB-6-SZ
 * 支持国家: HU, DE, BG, FI, PT, SE, RO, CZ, AT, ES, FR, IE, LV, SK, IT, NL, LU, LT, BE, DK, PL, EE, SI, GR, HR
 *
 * 产品名称: 欧洲卡航标准专线10000
 * 产品代码: EU-RB-10-SZ
 * 支持国家: LT, CZ, PL, FI, NL, RO, SE, BG, BE, DE, PT, HU, LV, EE, FR, SI, AT, IT, GR, ES, LU, HR, SK, IE, DK
 *
 * 产品名称: 欧洲卡航标准专线18000
 * 产品代码: EU-RB-18-SZ
 * 支持国家: NL, GR, HR, LU, ES, LV, AT, LT, FR, EE, FI, BG, HU, SE, PL, SI, IE, PT, DE, IT, CZ, SK, BE, DK, RO
 *
 * 产品名称: 日本空运8000-普货
 * 产品代码: JP05-SZ
 * 支持国家: JP
 *
 * 产品名称: 日本空运12000-普货
 * 产品代码: JP06-SZ
 * 支持国家: JP
 *
 * 产品名称: 日本空运18000-普货
 * 产品代码: JP07-SZ
 * 支持国家: JP
 *
 * 产品名称: 美国空运6000-普货-T11
 * 产品代码: USE13-SZ-P-06-T11
 * 支持国家: US
 *
 * 产品名称: 美国空运10000-普货-T11
 * 产品代码: USE13-SZ-P-10-T11
 * 支持国家: US
 *
 * 产品名称: 美国空运18000-普货-T11
 * 产品代码: USE13-SZ-P-18-T11
 * 支持国家: US
 *
 * 产品名称: 美国空运6000-带电-T11
 * 产品代码: USE13-SZ-D-06-T11
 * 支持国家: US
 *
 * 产品名称: 美国空运10000-带电-T11
 * 产品代码: USE13-SZ-D-10-T11
 * 支持国家: US
 *
 * 产品名称: 美国空运18000-带电-T11
 * 产品代码: USE13-SZ-D-18-T11
 * 支持国家: US
 *
 * 产品名称: 欧洲空运6000-普货
 * 产品代码: EUE06-SZ
 * 支持国家: DE, FR, ES, BE, LU, NL, AT, CZ, SK, HU, PL, IT, SE, DK, FI, BG, EE, LT, LV, PT, RO, SI, HR
 *
 * 产品名称: 欧洲空运10000-带电
 * 产品代码: EUB07-SZ-G-10-D
 * 支持国家: NL, ES, CZ, SK, FI, LV, DE, LT, RO, FR, BE, HR, AT, SI, BG, IT, LU, EE, HU, SE, DK, PL, PT
 *
 * 产品名称: 美国空运免泡-普货-T11
 * 产品代码: USA-SZ-MP-T11
 * 支持国家: US, US, US, US
 *
 * 产品名称: 美国空运免泡-带电-T11
 * 产品代码: USA-SZ-MP-D-T11
 * 支持国家: US, US, US, US
 *
 * 产品名称: 美国空运免泡-普货
 * 产品代码: USE13-MPKY
 * 支持国家: US, US, US, US
 *
 * 产品名称: 英国空运10000-普货
 * 产品代码: UK03-SZ-10
 * 支持国家: GB
 *
 *
 * 进程已结束，退出代码为 0
 */