<?php
/**
 * 递四方 - 查询仓库信息
 * 调用此接口可查询4PX所有仓库信息
 */

use Sxqibo\Logistics\Fpx;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/config.php';

try {
    // 初始化递四方客户端
    $fpx = new Fpx(
        $fpxConfig['app_key'],
        $fpxConfig['app_secret'],
        $fpxConfig['environment']
    );

    // 设置访问令牌（如果需要）
    if (!empty($fpxConfig['access_token'])) {
        $fpx->setAccessToken($fpxConfig['access_token']);
    }

    // 设置响应语言为中文（默认已经是中文）
    $fpx->setLanguage('cn');

    echo "=== 递四方 - 查询仓库信息 ===\n\n";

    // 示例1：查询所有仓库信息
    echo "========================================\n";
    echo "=== 示例1：查询所有仓库信息 ===\n";
    echo "========================================\n\n";

    echo "正在调用API查询所有仓库信息...\n";
    $result = $fpx->getWarehouseList();

    echo "API调用成功！\n\n";

    // 检查响应结果
    if (!isset($result['result']) || $result['result'] !== '1') {
        echo "API调用失败！\n";
        echo "错误信息: " . (isset($result['msg']) ? $result['msg'] : '未知错误') . "\n";
        if (isset($result['errors']) && !empty($result['errors'])) {
            echo "错误详情: " . json_encode($result['errors'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        }
        exit(1);
    }

    // 显示响应消息
    if (isset($result['msg'])) {
        echo "响应消息: {$result['msg']}\n\n";
    }

    // 获取仓库列表
    $warehouseList = isset($result['data']) && is_array($result['data']) ? $result['data'] : [];

    if (empty($warehouseList)) {
        echo "未找到仓库信息。\n";
        exit(0);
    }

    // 统计信息
    $totalCount = count($warehouseList);
    echo "=== 统计信息 ===\n";
    echo "仓库总数: {$totalCount}\n\n";

    // 按国家统计
    $countryStats = [];
    foreach ($warehouseList as $warehouse) {
        $country = isset($warehouse['country']) ? $warehouse['country'] : '未知';
        if (!isset($countryStats[$country])) {
            $countryStats[$country] = 0;
        }
        $countryStats[$country]++;
    }

    echo "=== 按国家统计 ===\n";
    foreach ($countryStats as $country => $count) {
        echo "  {$country}: {$count} 个仓库\n";
    }
    echo "\n";

    // 按业务类型统计
    $serviceStats = [];
    foreach ($warehouseList as $warehouse) {
        $serviceCode = isset($warehouse['service_code']) ? $warehouse['service_code'] : '未知';
        // service_code可能是多个，用逗号分隔
        $services = explode(',', $serviceCode);
        foreach ($services as $service) {
            $service = trim($service);
            if (!isset($serviceStats[$service])) {
                $serviceStats[$service] = 0;
            }
            $serviceStats[$service]++;
        }
    }

    echo "=== 按业务类型统计 ===\n";
    $serviceNames = [
        'F' => '订单履约',
        'S' => '自发服务',
        'T' => '转运服务',
        'R' => '退件服务'
    ];
    foreach ($serviceStats as $service => $count) {
        $serviceName = isset($serviceNames[$service]) ? $serviceNames[$service] : $service;
        echo "  {$service} ({$serviceName}): {$count} 个仓库\n";
    }
    echo "\n";

    // 显示仓库明细
    echo "=== 仓库明细 ===\n";
    echo str_pad('序号', 6, ' ', STR_PAD_RIGHT) .
         str_pad('仓库代码', 15, ' ', STR_PAD_RIGHT) .
         str_pad('中文名称', 30, ' ', STR_PAD_RIGHT) .
         str_pad('英文名称', 35, ' ', STR_PAD_RIGHT) .
         str_pad('国家', 8, ' ', STR_PAD_RIGHT) .
         str_pad('业务类型', 20, ' ', STR_PAD_RIGHT) . "\n";
    echo str_repeat('-', 120) . "\n";

    $index = 1;
    foreach ($warehouseList as $warehouse) {
        $warehouseCode = isset($warehouse['warehouse_code']) ? $warehouse['warehouse_code'] : '';
        $warehouseNameCn = isset($warehouse['warehouse_name_cn']) ? $warehouse['warehouse_name_cn'] : '';
        $warehouseNameEn = isset($warehouse['warehouse_name_en']) ? $warehouse['warehouse_name_en'] : '';
        $country = isset($warehouse['country']) ? $warehouse['country'] : '';
        $serviceCode = isset($warehouse['service_code']) ? $warehouse['service_code'] : '';

        // 格式化业务类型显示
        $serviceDisplay = '';
        if (!empty($serviceCode)) {
            $services = explode(',', $serviceCode);
            $serviceNamesList = [];
            foreach ($services as $service) {
                $service = trim($service);
                if (isset($serviceNames[$service])) {
                    $serviceNamesList[] = $service . '(' . $serviceNames[$service] . ')';
                } else {
                    $serviceNamesList[] = $service;
                }
            }
            $serviceDisplay = implode(', ', $serviceNamesList);
        }

        echo str_pad($index, 6, ' ', STR_PAD_RIGHT) .
             str_pad($warehouseCode, 15, ' ', STR_PAD_RIGHT) .
             str_pad(mb_substr($warehouseNameCn, 0, 28), 30, ' ', STR_PAD_RIGHT) .
             str_pad(mb_substr($warehouseNameEn, 0, 33), 35, ' ', STR_PAD_RIGHT) .
             str_pad($country, 8, ' ', STR_PAD_RIGHT) .
             str_pad(mb_substr($serviceDisplay, 0, 18), 20, ' ', STR_PAD_RIGHT) . "\n";

        $index++;
    }

    echo "\n";

    // 示例2：按业务类型查询
    echo "========================================\n";
    echo "=== 示例2：按业务类型查询（订单履约） ===\n";
    echo "========================================\n\n";

    echo "正在调用API查询订单履约仓库...\n";
    $result2 = $fpx->getWarehouseList('F'); // F = 订单履约

    if (isset($result2['result']) && $result2['result'] === '1') {
        $warehouseList2 = isset($result2['data']) && is_array($result2['data']) ? $result2['data'] : [];
        echo "找到 " . count($warehouseList2) . " 个订单履约仓库\n\n";
    } else {
        echo "查询失败: " . (isset($result2['msg']) ? $result2['msg'] : '未知错误') . "\n\n";
    }

    // 示例3：按国家查询
    echo "========================================\n";
    echo "=== 示例3：按国家查询（中国） ===\n";
    echo "========================================\n\n";

    echo "正在调用API查询中国仓库...\n";
    $result3 = $fpx->getWarehouseList('', 'CN'); // 查询中国的所有仓库

    if (isset($result3['result']) && $result3['result'] === '1') {
        $warehouseList3 = isset($result3['data']) && is_array($result3['data']) ? $result3['data'] : [];
        echo "找到 " . count($warehouseList3) . " 个中国仓库\n\n";
    } else {
        echo "查询失败: " . (isset($result3['msg']) ? $result3['msg'] : '未知错误') . "\n\n";
    }

    // 示例4：组合查询（业务类型 + 国家）
    echo "========================================\n";
    echo "=== 示例4：组合查询（退件服务 + 中国） ===\n";
    echo "========================================\n\n";

    echo "正在调用API查询中国的退件服务仓库...\n";
    $result4 = $fpx->getWarehouseList('R', 'CN'); // R = 退件服务，CN = 中国

    if (isset($result4['result']) && $result4['result'] === '1') {
        $warehouseList4 = isset($result4['data']) && is_array($result4['data']) ? $result4['data'] : [];
        echo "找到 " . count($warehouseList4) . " 个中国的退件服务仓库\n\n";
        
        if (!empty($warehouseList4)) {
            echo "仓库列表:\n";
            foreach ($warehouseList4 as $warehouse) {
                $code = isset($warehouse['warehouse_code']) ? $warehouse['warehouse_code'] : '';
                $nameCn = isset($warehouse['warehouse_name_cn']) ? $warehouse['warehouse_name_cn'] : '';
                echo "  - {$code}: {$nameCn}\n";
            }
        }
    } else {
        echo "查询失败: " . (isset($result4['msg']) ? $result4['msg'] : '未知错误') . "\n\n";
    }

    echo "\n=== 查询完成 ===\n";

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "文件: " . $e->getFile() . "\n";
    echo "行号: " . $e->getLine() . "\n";
    exit(1);
}
