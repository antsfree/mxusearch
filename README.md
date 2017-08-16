## 基于讯搜服务封装的全文检索服务(for laravel)

### 服务说明
>全文检索服务是基于开源项目`xunsearch`封装的全文搜索引擎，服务仍在完善。

### 服务安装配置
1. 通过`composer` 安装搜索服务, 输入如下命令安装最新版本.

```
composer require antsfree/mxusearch dev-master
```

2. 在 config/app.php 的服务数组 providers 中添加以下服务.

```
Antsfree\Mxusearch\MxusearchProvider::class
```

3. 在  config/app.php 的门面数组  aliases 中添加以下门面. 

```
'Mxusearch' => Antsfree\Mxusearch\Mxusearch::class,
```

### 基本服务方法设定


| 序号 | 方法名称 | 中文释义 | 备注 | 
| :--: | :--: |:--: |:--: |
| 1 | addIndex | 创建索引| 单条支持即时同步，多条存在时间误差，具体在2~3分钟，视具体情况 |
| 2 | deleteIndex | 删除索引 | 单条多条立即生效，无延迟 |
| 4 | searchIndex | 查找索引 | 支持setFuzzy模糊查询，支持特定字段定向查询（column:key） |
| 5 | cleanIndex | 清空索引 | 立即生效，无延迟 |
| 6 | rebuildIndex | 重建索引 | 暂不支持 | 
| 7 | getIndexCount | 获取索引总数 | 暂不支持 |
| 8 | checkServer | 检测全文检索服务状态 | 直接输出当前状态及索引条数 |
| 9| flushIndex | 强制刷新搜索日志 |  |
| 10| getHotWords | 获取热门搜索词 | 暂不支持 |
| 11| getMatchNum | 获取索引匹配数量 | 暂不支持 |
| 12| getSearchRate | 获取搜索频次 | 暂不支持 |
| 13| flushIndex | 强制刷新索引 | 默认为异步创建索引，强制刷新实现索引的即加即搜 |


### Artisan命令服务


提供 artisan 的命令实现 :


| 序号 | artisan命令 | console释义 | 备注 | 
| :--: | :--: |:--: |:--: |
| 1 | search:add  | 创建索引 | 单条支持即时同步，多条存在时间误差，具体在2~3分钟，视具体情况 |
| 2 | search:delete | 删除索引 | 单条多条立即生效，无延迟 |
| 3 | search:search | 查找索引 | 终端交互，可选择匹配范围 |
| 4 | search:clear| 清空索引 |  立即生效，无延迟 |
| 5 | search:check-server | 检测全文检索服务状态 | 直接输出当前状态及索引条数 |
| 6| search:flush| 强制刷新索引及搜索日志 | 默认为异步创建索引，强制刷新实现索引的即加即搜 |


### 索引管理注意事项：

1. 索引更新：多条同时更新存在时间误差，具体时长和需要创建的索引数量有关系，具体在`2~4分钟`能实现索引的更新；
2. 索引创建：多条同时新增存在时间误差，具体时长和需要创建的索引数量有关系，具体在`2~4分钟`能实现索引的创建；


### 讯搜服务官方SDK参考
[讯搜 SDK 指南](http://www.xunsearch.com/doc/php/guide/start.overview)