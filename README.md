## 基于讯搜服务封装的全文检索服务(for laravel)

### 服务说明
>全文检索服务是基于开源项目`xunsearch`封装的全文搜索引擎，服务仍在完善。

### 服务安装配置
**1.** 通过`composer` 安装搜索服务, 输入如下命令安装最新版本.

```
composer require antsfree/mxusearch dev-master
OR
composer require antsfree/mxusearch '^0.1'
```

**2.** 在 config/app.php 的服务数组 providers 中添加以下服务.

```
Antsfree\Mxusearch\MxusearchProvider::class
```

**3.** 在  config/app.php 的门面数组  aliases 中添加以下门面. 

```
'Mxusearch' => Antsfree\Mxusearch\Mxusearch::class,
```


**4.** 执行如下命令, 配置`mxusearch.php`配置文件；

```
php artisan vendor:publish --provider="Antsfree\Mxusearch\MxusearchProvider"
```

**5.** 分布式安装，各自`laravel`项目需要配置以下`env`参数：

| env配置 | 中文释义 | 默认值 |
| :--: | :--: | :--: |
| MXUSEARCH_PROJECT | 索引库名 | mxu_project |
| MXUSEARCH_CHARSET | 字符编码 | utf-8 |
| MXUSEARCH\_INDEX_HOST | 索引服务器IP | 127.0.0.1(分布式部署配置详见“注意”) | 
| MXUSEARCH\_INDEX_PORT | 索引端口 | 8383 |
| MXUSEARCH\_SEARCH_HOST | 搜索服务器IP | 127.0.0.1(分布式部署配置详见“注意”) |
| MXUSEARCH\_SEARCH_PORT | 搜索端口 | 8384 |
| MXUSEARCH\_INI | INI配置文件名 | mxusearch.ini |

> 注意：以上配置全都有默认值，其中索引、搜索的host在分布式部署上需要注意区分。统一指向讯搜服务所在服务器。

**6.** 执行`console`命令，生成`ini`文件

```
php artisan search:reset-ini
```


### 基本服务方法设定


| 序号 | 方法名称 | 中文释义 | 备注 | 
| :--: | :--: |:--: |:--: |
| 1 | addIndex | 创建索引| 单条支持即时同步，多条存在时间误差，具体在2~3分钟，视具体情况 |
| 2 | deleteIndex | 删除索引 | 单条多条立即生效，无延迟 |
| 4 | searchIndex | 查找索引 | 支持setFuzzy模糊查询，支持特定字段定向查询（column:key） |
| 5 | cleanIndex | 清空索引 | 立即生效，无延迟 |
| 6 | rebuildIndex | 重建索引 | 暂不支持 | 
| 7 | getIndexCount | 获取索引总数 |  |
| 8 | checkServer | 检测全文检索服务状态 | 直接输出当前状态及索引条数 |
| 9| flushIndex | 强制刷新搜索日志 |  |
| 10| getHotWords | 获取热门搜索词 |  |
| 11| getMatchNum | 获取索引匹配数量 |  |
| 12| flushIndex | 强制刷新索引 | 强制刷新实现索引的即加即搜 |
| 13| flushLogging | 强制刷新搜索日志 |  |
| 14| checkServer | 讯搜服务状态检测 |  |
| 15| getKeyWords | 文本分词功能 |  |
| 16| resetIniFile | 重置INI文件方法 |  |
| 17| multiSearch | 多条件查询 |  |

### Artisan命令服务


提供 artisan 的命令实现 :


| 序号 | artisan命令 | console释义 | 备注 | 
| :--: | :--: |:--: |:--: |
| 1 | search:add | 创建索引 | 单条支持即时同步，多条存在时间误差，具体在2~3分钟，视具体情况 |
| 2 | search:delete | 删除索引 | 单条多条立即生效，无延迟 |
| 3 | search:search | 查找索引 | 终端交互，可选择匹配范围 |
| 4 | search:clear | 清空索引 |  立即生效，无延迟 |
| 5 | search:check-server | 检测全文检索服务状态 | 直接输出当前状态及索引条数 |
| 6 | search:flush | 强制刷新索引及搜索日志 | 默认为异步创建索引，强制刷新实现索引的即加即搜 |
| 7 | search:scws | 文本分词命令 |  |
| 8 | search:reset-ini | 重置ini文件 | 根据配置项重新配置INI文件 |

### ini配置

1、 `ini`配置文件：`mxu-backend/config/mxusearch.ini` ;

2、 服务器配置

```
project.name = {{MXUSEARCH_PROJECT}}// 项目名称
project.default_charset = {{MXUSEARCH_CHARSET}}// 字符编码
server.index = {{MXUSEARCH_INDEX_HOST}}:{{MXUSEARCH_INDEX_PORT}}// 索引服务端配置(Host&端口)
server.search = {{MXUSEARCH_SEARCH_HOST}}:{{MXUSEARCH_SEARCH_PORT}}// 搜索服务端配置(Host&端口)
```
3、索引字段配置

```
[id]
type = id
tokenizer = full

[column_id]
tokenizer = full
index = self

......
......

```

### 方法说明

#### multiSearch（多条件查询）

**方法示例**

```
/**
 * 多条件查询功能
 *
 * @param        $keyword
 * @param string $field
 * @param array  $other_field_value
 * @param int    $limit
 * @param int    $page
 *
 * @return array
 */
public function multiSearch($keyword, $field = '', array $other_field_value = [], $limit = 0, $page = 1);
```

**请求参数**

| 参数名 | 类型 | 参数说明 | 必填 | 备注 |
| --- | --- | --- | --- | --- |
| $keyword | string | 关键词 | N |  |
| $field | string | 字段名 | N | 默认null，表示全文匹配 |
| $other_field_value | array | 其他多条件参数 | N | 默认空数组 | 
| $limit | int | 分页参数 |  |
| $page | int | 分页参数 |  |

**请求示例**

```
$key = '我是关键词';
$field = 'title';
// 多条件
$other_field_value = [
	'site_id': 1,
	'column_id': 2,
	'type': 'article',
	......
];
// 分页控制
$limit = 10;
$page = 1;

// 调用服务
Mxusearch::multiSearch($key, $field, $other_field, $limit, $page);
```

### 索引管理注意事项：

1. 索引更新：多条同时更新存在时间误差，具体时长和需要创建的索引数量有关系，具体在`2~4分钟`能实现索引的更新；
2. 索引创建：多条同时新增存在时间误差，具体时长和需要创建的索引数量有关系，具体在`2~4分钟`能实现索引的创建；
3. 当前只支持单库存储；
4. ini配置默认存于`laravel config`目录中；


### 讯搜服务官方SDK参考
[讯搜 SDK 指南](http://www.xunsearch.com/doc/php/guide/start.overview)