<?php

/**
 * 燕文开放平台 - 运价试算 calc.list
 * 文档：https://opendocs.yw56.com.cn/webfile/7417494175517511680/
 *
 * 说明：此接口需联系燕文销售开通权限。
 * ERP 侧实现见 app/common/services/logistics/YanwenClient::calcList
 */

echo "燕文运价试算 method=calc.list\n";
echo "文档：https://opendocs.yw56.com.cn/webfile/7417494175517511680/\n";
echo "请在 saas-erp 的 YanwenClient / LogisticsFreightCalcService 中调用。\n";
echo "示例请求体：\n";
echo json_encode([
    'cityId'            => '01',
    'countryId'         => '163',
    'productAttributes' => '1',
    'productTypeList'   => ['3', '2', '1'],
    'weight'            => 100,
    'high'              => 100,
    'length'            => 100,
    'width'             => 100,
    'postCode'          => '98842',
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), "\n";
