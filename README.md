# Yar 的 Laravel 封装

## Yar
Yar是鸟哥开发的轻量级rpc框架，纯 C 语言编写。前一阵自己找 rpc 的架子的时候，最终还是选定了 Yar 。

## 为什么要用 rpc
1. 很简单的例子，如果说我写一个项目，写完了之后写第二个项目时，需要重新开一个写。
2. 一个大项目，涉及到用户、论坛、问答、游戏等等特别大的架子，写在一个项目太过臃肿。
3. 自己百度。。。

## 安装&配置
1. 当前环境装有 yar 、 msgpack 扩展：
```bash
pecl install msgpack
pecl install yar
```
2. 在当前项目下执行：
```bash
composer require reprover/laravel-yar -vvv
```
3. 配置
```bash
php artisan vendor:publish --tag="laravel-yar"
```
会复制三个配置文件到 config.php 目录下，分别为
yar.php：yar 运行时配置
yar-services.php：rpc 服务注册映射表
yar-map.php：mapName 与 接口参数对应表

4. 改中间件
在app/Http/Middleware/VerifyCsrfToken.php 中的 except 数组中加上
```php
protected $except = [
    '/yar/*',
];
```
跳过 csrf 验证

## 原理解释
同一个 Laravel 框架可以提供多个不同模块的服务，使用 url 表示其模块地址和服务（后文讲），在不同服务中注册不同的方法去执行。
客户端通过对应的地址执行方法，这个时候 yar 使用其自定义的规则去 curl 服务端，取回数据。在同机器上速度远远大于正常的 http 请求。
说不清。。自己百度。。

## 使用
### 服务端
我们注册了一个路由用来代替 yar example 中的 server.php ，每次访问时会通过 `Reprover/LaravelYar/Controllers/` 引导执行 `app/Services`目录下的服务。客户端可以通过路由地址进行访问：
```
http://hostname/yar/{service}
```
在App/Services中的文件需 extends Reprover/LaravelYar/YarServices

Example Code
```php
<?php
namespace App\Services;

use Reprover\LaravelYar\YarService;

class TestService extends YarService
{
    function test_method($parameters)
    {
        return ['parameter' => $parameters];
    }
}
```
这样就是一个服务，我们可以直接通过浏览器访问`/yar/TestServices`来查看服务注册了的方法，也可以直接使用客户端请求。


### 客户端
客户端则是我们封装了一个类用于同步/异步请求，并进行了接口的封装。
1. 配置。
要将所使用的服务与模块在config/yar-services.php中进行配置，默认有示例文件：
```php
<?php
    return [
        'Example' => [
            'path' => 'http://resource.test/yar/', 'services' => [
            'ExampleService' => 'Example',
            'Example2Service' => 'Example2',
        ]
    ],
];
```
表示有一个Example模块（这个名字可以自定义的），path则是对应的地址，注意url后的 / 要加上。
services数组下分别表示此模块下的服务别名=>服务名。
服务别名可以自定义，在当前服务中使用。对应的是服务方的服务名称。
2. 配置 map
```php
<?php
return [
    'get_example' => [
        'module' => 'Example',
        'service' => 'ExampleService',
        'method' => 'getExample',
        'connect_timeout' => 1000,
        'read_timeout' => 5000,
    ]
];
```
get_example 是apiName，其中定义的就是这个接口的参数。module是模块，service是服务，method则是在service中定义的方法。使用方法必须先在yar-services中定义，目前没有做异常处理，如果调用到不存在的方法或服务会直接抛出。

3. 使用
*同步调用*
* 最简易：
```php
<?php 
use Reprover/LaravelYar/Yar;

$ret = Yar::test_get([123]);
var_dump($ret);
```
* 最标准：
```php
<?php

$yarClient = new \Reprover\LaravelYar\Yar('test_get');
$ret = $yarClient->call([123]);
```
*异步调用*
* 最简易：
```php
<?php
\Reprover\LaravelYar\Yar::asyncCall("test_get",[123],function($ret, $callbackinfo){
    var_dump($ret);
});
\Reprover\LaravelYar\Yar::asyncCall("test_get",[456],function($ret, $callbackinfo){
    var_dump($ret);
});
\Reprover\LaravelYar\Yar::loop();
```
* 最标准：
```php
<?php
$yarClient = new \Reprover\LaravelYar\Yar('test_get', true);
$yarClient->setCallback(function($ret, $callbackinfo){
                            var_dump($ret);
                        });
$yarClient->call([789]);
$yarClient::loop();
```
    
## 使用上需要注意的问题
1. curl使用当前环境作为基准，也就是说，使用 Laradock 类似的环境，将 nginx 作为 docker 提供服务的，会出现 curl 找不到服务器的报错。
2. 远程服务调用过程中，可能会出现其他人恶意访问的情况，注意添加ip白名单
3. 鸟哥的扩展优点在于快，缺点呢个人感觉是使用上的"优雅"问题。但是这种"优雅"必然是以牺牲性能为代价的。在 Laravel 中，使用 Laravel 的方式去实现 Yar，我觉得应该是最标准的方式了。
4. 其他比如 procotol 等慢慢添加。有问题欢迎 issue 。
