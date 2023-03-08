# Owl Admin 微信用户管理

## 效果

基础会员管理, 分表 (含微信小程序授权登录)

## 安装

#### zip 下载地址

[https://gitee.com/slowlyo/owl-wechat-user/repository/archive/master.zip](https://gitee.com/slowlyo/owl-wechat-user/repository/archive/master.zip)

#### composer

```bash
composer require slowlyo/owl-wechat-user
```

## 使用说明

1. 安装扩展
2. 在扩展管理中启用扩展
3. ojbk

## 依赖

### `overtrue/laravel-wechat`: `6.*`
[仓库地址](https://github.com/overtrue/laravel-wechat)

### `jiannei/laravel-response`: `^5.2`

#### 配置

[仓库地址](https://github.com/jiannei/laravel-response)

##### 发布配置文件

```bash
php artisan vendor:publish --provider="Jiannei\Response\Laravel\Providers\LaravelServiceProvider"
```

##### 格式化异常响应

```php
// 在 app/Exceptions/Handler.php 中添加以下代码
use Jiannei\Response\Laravel\Support\Traits\ExceptionTrait;

use ExceptionTrait;

// 引入以后对于 API 请求产生的异常都会进行格式化数据返回
// 要求请求头 header 中包含 /json 或 +json，如：Accept:application/json
// 或者是 ajax 请求，header 中包含 X-Requested-With：XMLHttpRequest;
```

##### controller 中使用

```php

use Jiannei\Response\Laravel\Support\Format;
use Jiannei\Response\Laravel\Support\Traits\JsonResponseTrait;

class Controller extends BaseController
{
    // use 这个 trait
    use JsonResponseTrait

    // 添加 formatter 属性
    protected $formatter;

    public function __construct()
    {
        // 初始化 formatter
        $this->formatter = new Format();
    }
}
```


***
***

## 注意

> 本扩展有第三方依赖, 无法使用 `zip` 方式安装, 请使用 `composer` 安装 <br>
> 卸载扩展不会删除 `users` `user_oauth` 表, 请手动处理

***
***

## 配置

可在扩展管理中配置以下参数, 方便二开:

- service_class: 服务类
- user_model_class: 用户模型类
- user_oauth_model_class: 用户授权模型类
- resource_class: 资源类

## 现成的功能

### 后台会员列表

### api

#### 小程序登录 (含注册)

- url: `/api/wechat/mini_program_login`
- method: `any`
- params: `code` `encryptedData` `iv`

#### 获取用户信息

- url: `/api/wechat/user`
- method: `get`

#### 更新用户信息

- url: `/api/wechat/user`
- method: `post`
- params: `nickname` `avatar`

### 事件

以下事件都有 `user` `request` 两个参数

- 用户注册事件: `UserRegisteredEvent`
- 用户登录事件: `UserLoggedInEvent`
- 用户更新事件: `UserUpdatedEvent`

## 你可能想问的问题

1. 如何重写后台功能?
    - 覆盖 `users` 路由

2. 默认头像 `public/extensions/slowlyo/owl-wechat-user/images/avatar.jpg`
    - 有需要可以自行替换

3. 自行阅读源码
4. 后续会增加公众号登录
