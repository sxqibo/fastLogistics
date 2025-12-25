<?php
/**
 * 递四方 - 批量查询SKU
 * 查询SKU信息集合，包括SKU基本信息、尺寸重量、电池信息、申报信息等
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

    echo "=== 递四方 - 批量查询SKU ===\n\n";

    // 示例1：查询单个SKU
    echo "========================================\n";
    echo "=== 示例1：查询单个SKU ===\n";
    echo "========================================\n\n";

    // 设置查询参数
    $skuCode = 'M-HUTAO-TOU';          // SKU编号
    
    // 尝试不同的查询方式
    // 方式1：不传customer_code，查询所有操作账号下的SKU
    echo "查询参数:\n";
    echo "  SKU编号: {$skuCode}\n";
    echo "  客户操作账号: 未指定（查询所有账号）\n\n";

    echo "正在调用API查询SKU信息（不指定客户账号）...\n";
    $result = $fpx->getSkuList(
        $skuCode,          // SKU编号
        ''                  // 不传客户操作账号，查询所有账号
    );

    echo "API调用成功！\n\n";

    // 显示原始响应数据
    echo "=== 原始响应数据 ===\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 解析并显示SKU信息
    if (isset($result['result']) && ($result['result'] === '1' || $result['result'] === 'success') && isset($result['data'])) {
        echo "=== SKU信息解析 ===\n";
        
        $skuData = $result['data'];
        $skuList = isset($skuData['skulist']) ? $skuData['skulist'] : [];
        
        // 如果查询不到结果，尝试使用指定的客户账号
        if (empty($skuList)) {
            echo "未找到SKU信息（不指定客户账号）\n";
            echo "尝试使用客户操作账号 '900278' 查询...\n\n";
            
            $result = $fpx->getSkuList(
                $skuCode,          // SKU编号
                '900278'            // 尝试使用客户操作账号
            );
            
            echo "=== 使用客户账号查询的原始响应数据 ===\n";
            echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
            
            if (isset($result['result']) && ($result['result'] === '1' || $result['result'] === 'success') && isset($result['data'])) {
                $skuData = $result['data'];
                $skuList = isset($skuData['skulist']) ? $skuData['skulist'] : [];
            }
        }
        
        if (!empty($skuList)) {
            echo "查询到 " . count($skuList) . " 个SKU\n\n";
            
            foreach ($skuList as $index => $sku) {
                echo "SKU " . ($index + 1) . ":\n";
                echo str_repeat("-", 60) . "\n";
                
                // 基本信息
                echo "--- 基本信息 ---\n";
                if (isset($sku['sku_id'])) {
                    echo "SKU ID（数字条码）: " . $sku['sku_id'] . "\n";
                }
                if (isset($sku['sku_code'])) {
                    echo "SKU编码: " . $sku['sku_code'] . "\n";
                }
                if (isset($sku['product_code'])) {
                    echo "商品条码（UPC/EAN/JAN）: " . ($sku['product_code'] ?: '无') . "\n";
                }
                if (isset($sku['declare_product_code'])) {
                    echo "申报产品代码: " . ($sku['declare_product_code'] ?: '无') . "\n";
                }
                if (isset($sku['sku_name'])) {
                    echo "SKU名称: " . $sku['sku_name'] . "\n";
                }
                if (isset($sku['chinese_name'])) {
                    echo "中文名称: " . ($sku['chinese_name'] ?: '无') . "\n";
                }
                if (isset($sku['specification'])) {
                    echo "规格型号: " . ($sku['specification'] ?: '无') . "\n";
                }
                if (isset($sku['sku_status'])) {
                    $statusMap = ['X' => '已作废', 'S' => '已发布', 'N' => '草稿'];
                    $statusText = $statusMap[$sku['sku_status']] ?? $sku['sku_status'];
                    echo "SKU状态: " . $statusText . " ({$sku['sku_status']})\n";
                }
                if (isset($sku['customer_code'])) {
                    echo "客户操作账号: " . $sku['customer_code'] . "\n";
                }
                
                // 尺寸和重量
                echo "\n--- 尺寸和重量 ---\n";
                if (isset($sku['weight'])) {
                    echo "重量: " . $sku['weight'] . " g（克）\n";
                }
                if (isset($sku['length'])) {
                    echo "长/直径: " . $sku['length'] . " cm（厘米）\n";
                }
                if (isset($sku['width'])) {
                    echo "宽: " . $sku['width'] . " cm（厘米）\n";
                }
                if (isset($sku['height'])) {
                    echo "高: " . $sku['height'] . " cm（厘米）\n";
                }
                if (isset($sku['uom'])) {
                    echo "计量单位: " . $sku['uom'] . "\n";
                }
                
                // 包装信息
                echo "\n--- 包装信息 ---\n";
                if (isset($sku['wrapping'])) {
                    $wrappingMap = ['H' => '硬包装', 'S' => '软包装'];
                    $wrappingText = $wrappingMap[$sku['wrapping']] ?? $sku['wrapping'];
                    echo "商品包装: " . $wrappingText . " ({$sku['wrapping']})\n";
                }
                if (isset($sku['appearance'])) {
                    $appearanceMap = [
                        'SS' => '正方体', 'RS' => '长方体', 'CS' => '圆锥体',
                        'TS' => '三角形', 'LS' => 'L形', 'OS' => '其它', 'DS' => '圆柱体'
                    ];
                    $appearanceText = $appearanceMap[$sku['appearance']] ?? $sku['appearance'];
                    echo "商品外观: " . $appearanceText . " ({$sku['appearance']})\n";
                }
                if (isset($sku['logistics_package'])) {
                    $logisticsPackageMap = ['Y' => '是', 'N' => '否'];
                    $logisticsPackageText = $logisticsPackageMap[$sku['logistics_package']] ?? $sku['logistics_package'];
                    echo "自带物流包装: " . $logisticsPackageText . " ({$sku['logistics_package']})\n";
                }
                if (isset($sku['package_material'])) {
                    $materialMap = [
                        'WO' => '木质', 'PA' => '纸质', 'PL' => '塑料',
                        'ME' => '金属', 'OT' => '其他'
                    ];
                    $materialText = $materialMap[$sku['package_material']] ?? $sku['package_material'];
                    echo "包装材质: " . $materialText . " ({$sku['package_material']})\n";
                }
                
                // 商品特性
                if (isset($sku['characteristic']) && is_array($sku['characteristic']) && !empty($sku['characteristic'])) {
                    echo "\n--- 商品特性 ---\n";
                    $characteristicMap = [
                        '01' => '带插座', '02' => '带液体', '03' => '带光盘',
                        '04' => '易碎品', '05' => '带粉末', '06' => '膏状',
                        '07' => '贵重货品', '08' => '恒温保存', '09' => '危险货品'
                    ];
                    $characteristics = [];
                    foreach ($sku['characteristic'] as $char) {
                        if (!empty($char)) {
                            $charText = $characteristicMap[$char] ?? $char;
                            $characteristics[] = $charText . " ({$char})";
                        }
                    }
                    echo !empty($characteristics) ? implode(', ', $characteristics) : '无' . "\n";
                }
                
                // 批次和有效期管理
                echo "\n--- 管理信息 ---\n";
                if (isset($sku['include_batch'])) {
                    $batchMap = ['Y' => '是', 'N' => '否'];
                    $batchText = $batchMap[$sku['include_batch']] ?? $sku['include_batch'];
                    echo "是否批次管理: " . $batchText . " ({$sku['include_batch']})\n";
                }
                if (isset($sku['expired_date'])) {
                    $expiredMap = ['Y' => '是', 'N' => '否'];
                    $expiredText = $expiredMap[$sku['expired_date']] ?? $sku['expired_date'];
                    echo "是否有效期管理: " . $expiredText . " ({$sku['expired_date']})\n";
                }
                if (isset($sku['sn_rule_code'])) {
                    $snRuleMap = [
                        '01' => '15位数字', '02' => '18位数字', '03' => '12位字符'
                    ];
                    $snRuleText = $snRuleMap[$sku['sn_rule_code']] ?? ($sku['sn_rule_code'] ?: '无');
                    echo "SN码规则: " . $snRuleText . "\n";
                }
                
                // 电池信息
                if (isset($sku['include_battery']) && $sku['include_battery'] === 'Y') {
                    echo "\n--- 电池信息 ---\n";
                    echo "是否带电: 是\n";
                    if (isset($sku['battery_config'])) {
                        $batteryConfigMap = [
                            'EB' => '配套', 'PB' => '纯电', 'IB' => '内置', 'NB' => '其他'
                        ];
                        $batteryConfigText = $batteryConfigMap[$sku['battery_config']] ?? $sku['battery_config'];
                        echo "电池配置: " . $batteryConfigText . " ({$sku['battery_config']})\n";
                    }
                    if (isset($sku['battery_type'])) {
                        $batteryTypeMap = [
                            'LI' => '锂电池', 'NI' => '镍氢电池',
                            'DR' => '干电池', 'BU' => '纽扣电池', 'OT' => '其他'
                        ];
                        $batteryTypeText = $batteryTypeMap[$sku['battery_type']] ?? $sku['battery_type'];
                        echo "电池类型: " . $batteryTypeText . " ({$sku['battery_type']})\n";
                    }
                    if (isset($sku['battery_power'])) {
                        echo "电池功率: " . ($sku['battery_power'] ?: '无') . "\n";
                    }
                    if (isset($sku['battery_number'])) {
                        echo "电池数量: " . ($sku['battery_number'] ?: '0') . "\n";
                    }
                    if (isset($sku['battery_resource'])) {
                        echo "电池资料: " . ($sku['battery_resource'] ?: '无') . "\n";
                    }
                } else {
                    echo "\n--- 电池信息 ---\n";
                    echo "是否带电: 否\n";
                }
                
                // 品牌信息
                echo "\n--- 品牌信息 ---\n";
                if (isset($sku['is_brand'])) {
                    $isBrandMap = ['Y' => '有', 'N' => '无'];
                    $isBrandText = $isBrandMap[$sku['is_brand']] ?? $sku['is_brand'];
                    echo "有无品牌: " . $isBrandText . " ({$sku['is_brand']})\n";
                }
                if (isset($sku['brand_name'])) {
                    echo "品牌名称: " . ($sku['brand_name'] ?: '无') . "\n";
                }
                if (isset($sku['origin_country'])) {
                    echo "原产国: " . $sku['origin_country'] . "\n";
                }
                
                // 图片链接
                if (isset($sku['picture_url']) && is_array($sku['picture_url']) && !empty($sku['picture_url'])) {
                    echo "\n--- 图片链接 ---\n";
                    foreach ($sku['picture_url'] as $picIndex => $picUrl) {
                        echo "图片 " . ($picIndex + 1) . ": " . $picUrl . "\n";
                    }
                }
                
                // 申报信息
                if (isset($sku['declare_country_list']) && is_array($sku['declare_country_list']) && !empty($sku['declare_country_list'])) {
                    echo "\n--- 申报信息 ---\n";
                    foreach ($sku['declare_country_list'] as $declareIndex => $declare) {
                        echo "申报信息 " . ($declareIndex + 1) . ":\n";
                        if (isset($declare['export_country'])) {
                            echo "  出口国: " . $declare['export_country'] . "\n";
                        }
                        if (isset($declare['country'])) {
                            echo "  进口国: " . $declare['country'] . "\n";
                        }
                        if (isset($declare['hs_code'])) {
                            echo "  HS编码: " . $declare['hs_code'] . "\n";
                        }
                        if (isset($declare['declare_value'])) {
                            echo "  申报价值: " . $declare['declare_value'] . "\n";
                        }
                        if (isset($declare['currency'])) {
                            echo "  币种: " . $declare['currency'] . "\n";
                        }
                        if (isset($declare['export_declare'])) {
                            echo "  出口申报品名: " . ($declare['export_declare'] ?: '无') . "\n";
                        }
                        if (isset($declare['import_declare'])) {
                            echo "  进口申报品名: " . ($declare['import_declare'] ?: '无') . "\n";
                        }
                    }
                }
                
                // 其他信息
                echo "\n--- 其他信息 ---\n";
                if (isset($sku['sales_link'])) {
                    echo "销售链接: " . ($sku['sales_link'] ?: '无') . "\n";
                }
                if (isset($sku['remark'])) {
                    echo "商品备注: " . ($sku['remark'] ?: '无') . "\n";
                }
                
                echo "\n";
            }
        } else {
            echo "未找到SKU信息\n";
            echo "提示: SKU '{$skuCode}' 在当前条件下没有数据\n";
        }
    } else {
        echo "=== 错误信息 ===\n";
        echo "获取SKU信息失败或返回数据格式异常\n";
        if (isset($result['result'])) {
            $resultStatus = $result['result'];
            echo "结果状态: " . $resultStatus . "\n";
            if ($resultStatus === '0') {
                echo "API调用失败\n";
            }
        }
        if (isset($result['msg'])) {
            echo "消息: " . $result['msg'] . "\n";
        }
        if (isset($result['errors']) && is_array($result['errors'])) {
            foreach ($result['errors'] as $error) {
                echo "错误码: " . ($error['error_code'] ?? 'N/A') . "\n";
                echo "错误消息: " . ($error['error_msg'] ?? 'N/A') . "\n";
            }
        }
    }

    // ========================================
    // 示例2：批量查询多个SKU
    // ========================================
    echo "\n\n";
    echo "========================================\n";
    echo "=== 示例2：批量查询多个SKU ===\n";
    echo "========================================\n\n";

    // 设置查询参数
    $skuCodesArray = ['M-HUTAO-TOU', 'TEST01'];  // 多个SKU编号（最多100个）
    $customerCode2 = '900278';                   // 客户操作账号

    echo "查询参数:\n";
    echo "  SKU编号: " . implode(', ', $skuCodesArray) . "\n";
    echo "  客户操作账号: " . ($customerCode2 ?: '未指定（查询所有账号）') . "\n\n";

    // 批量查询SKU信息
    echo "正在调用API批量查询SKU信息...\n";
    $result2 = $fpx->getSkuList(
        $skuCodesArray,     // SKU编号数组
        $customerCode2      // 客户操作账号（可选）
    );

    echo "API调用成功！\n\n";

    // 解析并显示结果
    if (isset($result2['result']) && ($result2['result'] === '1' || $result2['result'] === 'success') && isset($result2['data'])) {
        $skuData2 = $result2['data'];
        $skuList2 = isset($skuData2['skulist']) ? $skuData2['skulist'] : [];
        
        echo "=== 批量查询结果 ===\n";
        echo "查询到 " . count($skuList2) . " 个SKU\n\n";
        
        if (!empty($skuList2)) {
            // 简化显示，只显示关键信息
            echo str_pad("序号", 6) . 
                 str_pad("SKU编码", 25) . 
                 str_pad("SKU名称", 30) . 
                 str_pad("状态", 10) . 
                 str_pad("重量(g)", 12) . 
                 "尺寸(cm)\n";
            echo str_repeat("-", 100) . "\n";
            
            foreach ($skuList2 as $index => $sku) {
                $seq = $index + 1;
                $code = $sku['sku_code'] ?? 'N/A';
                $name = mb_substr($sku['sku_name'] ?? 'N/A', 0, 28);
                $status = $sku['sku_status'] ?? 'N/A';
                $weight = $sku['weight'] ?? 'N/A';
                $size = '';
                if (isset($sku['length']) && isset($sku['width']) && isset($sku['height'])) {
                    $size = $sku['length'] . '×' . $sku['width'] . '×' . $sku['height'];
                } else {
                    $size = 'N/A';
                }
                
                echo str_pad($seq, 6) . 
                     str_pad($code, 25) . 
                     str_pad($name, 30) . 
                     str_pad($status, 10) . 
                     str_pad($weight, 12) . 
                     $size . "\n";
            }
            echo str_repeat("-", 100) . "\n";
        } else {
            echo "未找到SKU信息\n";
        }
    } else {
        echo "=== 错误信息 ===\n";
        echo "批量查询SKU信息失败\n";
        if (isset($result2['result'])) {
            echo "结果状态: " . $result2['result'] . "\n";
        }
        if (isset($result2['msg'])) {
            echo "消息: " . $result2['msg'] . "\n";
        }
    }

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行数: " . $e->getLine() . "\n";
}
