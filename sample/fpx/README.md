# 递四方物流API - 库存查询

本目录包含了递四方物流API的库存查询功能示例代码。

## 功能说明

根据[递四方开放平台API文档](https://open.4px.com/v2/doc/detail?ids=54,67,114)，本示例实现了库存查询接口（fu.wms.inventory.get），支持：

- 根据SKU编号查询库存
- 根据批次号查询库存  
- 查询所有库存信息
- 支持分页查询
- 支持多仓库查询

## 配置说明

1. 在 `config.php` 文件中配置您的递四方API凭据：
   ```php
   $fpxConfig = [
       'app_key' => 'your_app_key',        // 应用接入申请的AppKey
       'app_secret' => 'your_app_secret',  // App Secret
       'environment' => 'production',      // 环境：sandbox(测试) 或 production(生产)
       'access_token' => ''                // 访问令牌（可选，平台服务商、第三方软件商必须传入）
   ];
   ```

2. 确保已安装必要的依赖包：
   ```bash
   composer install
   ```

## API接口说明

### 请求地址
- **正式环境**: `https://open.4px.com/router/api/service`
- **测试环境**: `https://open-test.4px.com/router/api/service`

### 公共参数
| 字段 | 类型 | 是否必传 | 描述 |
|------|------|----------|------|
| method | String | 是 | API接口名称：`fu.wms.inventory.get` |
| app_key | String | 是 | 应用接入申请的AppKey |
| v | String | 是 | API协议版本，固定值：`1.0.0` |
| timestamp | String | 是 | 时间戳，取当前时间的毫秒数 |
| format | String | 是 | 数据格式，固定值：`json` |
| sign | String | 是 | API输入参数签名结果，使用MD5加密算法 |
| access_token | String | 否 | 通过OAuth授权方式获得，平台服务商、第三方软件商必须传入 |
| language | String | 否 | 响应信息的语言，支持cn（中文），en（英文） |

### 请求参数
| 字段 | 类型 | 最大长度 | 是否必传 | 描述 |
|------|------|----------|----------|------|
| customer_code | String | 12 | 否 | 客户操作账号 |
| lstsku | List | - | 否 | SKU编号列表，单次最大支持100种SKU种类查询 |
| lstbatch_no | List | 20 | 否 | 批次号列表，单次最大支持100个批次号查询 |
| warehouse_code | String | 10 | 否 | 仓库代码，需要查询库存的仓库代码 |
| page_no | Number | 9 | 否 | 页码，默认为第1页 |
| page_size | Number | 3 | 否 | 每页记录数，默认50，单次查询最大支持500条 |

### 响应参数
| 字段 | 类型 | 描述 |
|------|------|------|
| result | String | 处理结果：1-成功，0-失败 |
| msg | String | 处理消息 |
| data | Object | 库存信息集合 |
| data.page_no | String | 页码 |
| data.page_size | String | 每页记录数 |
| data.total | String | 总记录数 |
| data.data | Array | 库存信息列表 |

### 库存信息字段
| 字段 | 类型 | 描述 |
|------|------|------|
| customer_code | String | 客户操作账号 |
| sku_code | String | SKU编号 |
| warehouse_code | String | 仓库代码 |
| sku_id | String | SKU ID |
| batch_no | String | 批次号 |
| stock_quality | String | 库存质量：G-良品，E-次品 |
| available_stock | String | 可用库存 |
| pending_stock | String | 待出库库存 |
| onway_stock | String | 在途库存 |

## 示例文件

- `01_getInventory.php` - 库存查询完整示例，包含6种不同的查询方式

## 使用方法

1. **配置API凭据**：在 `config.php` 中填入您的AppKey和AppSecret
2. **运行示例**：执行 `php 01_getInventory.php` 查看各种查询方式
3. **集成到项目**：参考示例代码集成到您的项目中

## 示例代码说明

### 1. 根据SKU查询库存
```php
$result = $fpx->getInventoryBySku(
    'CES-ST0',           // SKU编号
    '900278',            // 客户操作账号
    'CNDGMA',            // 仓库代码
    1,                   // 页码
    10                   // 每页记录数
);
```

### 2. 批量查询多个SKU
```php
$result = $fpx->getInventoryBySku(
    ['CES-ST0', 'CES-ST1', 'CES-ST2'],  // SKU编号数组
    '900278',                            // 客户操作账号
    '',                                  // 不指定仓库代码
    1,                                   // 页码
    50                                   // 每页记录数
);
```

### 3. 根据批次号查询
```php
$result = $fpx->getInventoryByBatch(
    'BATCH001',          // 批次号
    '900278',            // 客户操作账号
    'GBGBRB',            // 仓库代码
    1,                   // 页码
    10                   // 每页记录数
);
```

### 4. 查询所有库存
```php
$result = $fpx->getAllInventory(
    '900278',            // 客户操作账号
    '',                  // 不指定仓库代码
    1,                   // 页码
    20                   // 每页记录数
);
```

### 5. 使用原始API方法
```php
$params = [
    'customer_code' => '900278',
    'warehouse_code' => 'CNDGMA',
    'page_no' => 1,
    'page_size' => 10
];
$result = $fpx->getInventory($params);
```

### 6. 格式化库存数据
```php
$formattedData = $fpx->formatInventoryData($result);
```

## 注意事项

1. **签名算法**：使用MD5加密算法对参数进行签名
2. **参数限制**：
   - 单次最大支持100种SKU种类查询
   - 单次最大支持100个批次号查询
   - 单次查询最大支持500条记录
3. **访问令牌**：平台服务商、第三方软件商必须传入access_token
4. **环境选择**：测试时使用sandbox环境，生产时使用production环境

## 错误处理

所有示例都包含完整的错误处理机制，常见错误包括：
- 认证失败：检查app_key和app_secret是否正确
- 参数错误：检查必填参数是否完整
- 网络错误：检查网络连接和API地址
- 签名错误：检查签名算法是否正确

## 技术支持

如有问题，请参考：
- [递四方开放平台文档](https://open.4px.com/v2/doc/detail?ids=54,67,114)
- [开发者工具](https://open.4px.com/v2/devops/tool?ids=sdk-tool)
- [商家接入指引](https://open.4px.com/v2/help/point?ids=help-point,business)