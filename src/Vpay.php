<?php
/*
 * Copyright (c) 2023. Ankio.  由CleanPHP4强力驱动。
 */
/**
 * Package: Ankio
 * Class Vpay
 * Created By ankio.
 * Date : 2023/5/8
 * Time : 11:18
 * Description :  Vpay 调用SDK
 */

namespace Ankio;

use Ankio\objects\OrderObject;
use Ankio\objects\PayCreateObject;
use Ankio\objects\PayNotifyObject;
use Ankio\objects\StateObject;
use library\vpay\src\PayException;

class Vpay
{
    //后台api数据
    const SUCCESS = 3;//订单成功
    const PAID = 2;//已支付
    const WAIT = 1;//等待支付
    const CLOSE = -1;//已关闭

    const PAY_WECHAT = 1; //微信收款
    const PAY_ALIPAY = 2;//支付宝收款
    const PAY_QQ= 3;//QQ



    private PayConfig $config;


    /**
     * Pay constructor.
     * @param PayConfig $config 配置文件数组
     */
    public function __construct(PayConfig $config)
    {
        $this->config = $config;
        if(session_status()!==PHP_SESSION_ACTIVE ) session_start();
    }


    /**
     * 创建订单
     * @param PayCreateObject $createObject
     * @return OrderObject
     * @throws PayException
     */
    public function create(PayCreateObject $createObject): OrderObject
    {

        $params = $createObject->toArray();
        //预防恶意刷单，在订单有效期内重复刷取低价
        if(isset($_SESSION["pay_order"])&&isset($_SESSION["pay_order_timeout"])&&$_SESSION["pay_order_timeout"]>time()){
            /**
             * @var OrderObject $order
             */
            $order = unserialize($_SESSION["pay_order"]);
            try{
                if($this->state($order->order_id)->state==self::WAIT){
                    return $order;
                }
            }catch (PayException){

            }

        }

        $params = $this->createSign($params);

        //$params['sign'] = $sign;

       // var_dump($params);exit();
        //$_SESSION['__pay_timeout']=strtotime('+'.$this->config->time.' min');

        $result = $this->request($this->config->getCreateOrder(),$params);

        $json = json_decode($result);

        if($json){
            if($json->code===200){
                $object = new OrderObject($json->data);
                $_SESSION["pay_order"] = serialize($object);
                $_SESSION["pay_order_timeout"] = time() + $this->config->time*60;
               return $object;
            }

            throw new PayException($json->msg);

        }else{
            throw new PayException('远程支付站点发生问题，或创建订单的地址有误:'.$this->config->getCreateOrder().$result);
        }

    }

    /**
     * 签名校验，此处校验的是notify或者return的签名
     * @param $arg
     * @throws PayException
     */
    private function checkSign($arg): void
    {
        $result = PaySign::checkSign($arg,$this->config->key);
        if(!$result){
            throw new PayException("签名校验失败");
        }
        if(!isset($arg['t'])||time() - intval($arg['t']) > $this->config->time*60){
            throw new PayException("该请求已经超时");
        }

    }

    /**
     * 响应同步回调参数，此处的数据在于$_GET
     * @throws PayException
     */
    public function payReturn(): void
    {
        $this->checkSign($_GET);
        if(!isset($_SESSION['pay_order'])){
            throw new PayException("支付已完成，请勿重复刷新。");
        }
        $this->closeClient();
    }


    /**
     * 异步回调，成功后默认输出了“success”，需要在成功后结束程序运行
     * @param $callback mixed 需要提供回调函数，$callback({@link PayNotifyObject}$notifyObject)
     * @throws PayException
     */
    public function payNotify(mixed $callback): void
    {
        //检查sign
        $this->checkSign($_POST);
        if(is_callable($callback)){
            $callback(new PayNotifyObject($_POST));
        }else{
            throw new PayException("回调函数错误。");
        }
        echo "success";

    }//此处是异步回调

    /**
     * 关闭订单
     * @param $orderId string 根据创建订单返回的order_id关闭订单
     * @throws PayException
     */
    public function close(string $orderId): void
    {
        $this->closeClient();
       $result = $this->request($this->config->closeOrder(),$this->createSign([
           'order_id'=>$orderId
       ]));
        $json=json_decode($result);

        if($json->code!==200){
            throw new PayException($json->msg);
        }
    }//关闭订单，主要用于用户自己开启了之后使用

    /**
     * 根据OrderId查询当前订单状态
     * @param string $orderId
     * @return StateObject 返回object就是查询成功，否则就是失败
     * @throws PayException
     */
    public function state(string $orderId): StateObject
    {
        $result = $this->request($this->config->getOrderState(),$this->createSign([
            'order_id'=>$orderId
        ]));
        $json = json_decode($result);
        if($json->code===200){
            return new StateObject($json->data);
        }
        throw new PayException($json->msg);
    }

    /**
     * 关闭客户端订单
     * @return void
     */
    private function closeClient(): void
    {
        if(!isset($_SESSION['pay_order'])){
            return;
        }
        unset($_SESSION['pay_order']);
        unset($_SESSION["pay_order_timeout"]);
    }

    /**
     * post请求数据
     * @param $url string 地址
     * @param $post_data ?array 携带数据
     * @return bool|string
     */
   private function request(string $url, ?array $post_data): bool|string
   {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    /**
     * 对请求参数进行签名
     * @param $array
     * @return array
     */
    private function createSign($array): array
    {
        $array['t'] = time();
        $array['appid'] = $this->config->id;
       return PaySign::sign($array,$this->config->key);
    }
}