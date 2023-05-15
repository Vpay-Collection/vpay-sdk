## 简介
Vpay接入SDK

## 使用

这里给出的方案是将该仓库作为子模块使用，你也可以直接复制src路径到指定目录下使用
```shell
git add submoduls https://github.com/Vpay-Colloction/vpay-sdk
```

- 创建订单

```php
$crateObject = new \Ankio\objects\PayCreateObject();
$crateObject->price = 0.01;//需要支付的金额
$crateObject->
(new \Ankio\Vpay(new \Ankio\PayConfig($config)))->create();
```

- 关闭订单
- 查询订单状态