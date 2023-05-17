## 简介
Vpay接入SDK

## 引入SDK

### 作为子模块使用
```shell
git add submodule https://github.com/Vpay-Colloction/vpay-sdk
git  submodule init
git  submodule update --remote
```
### 通过`Composer`调用
```shell
composer require ankio/vpay-sdk
```

- 创建订单

```php
$order = new \Ankio\objects\PayCreateObject();
$order->app_item = "商品名称";
$order->appid = $config->id;
$order->param = json_encode(array_merge(arg(),["item"=>$item->toArray()]));
$order->price = $item->item_price;
$order->pay_type =$pay_type;
$order->notify_url = url("api_shop","main","notify");
$order->return_url = url("shop","main","return");
$crateObject->
(new \Ankio\Vpay(new \Ankio\PayConfig($config)))->create();
```

- 关闭订单
- 查询订单状态