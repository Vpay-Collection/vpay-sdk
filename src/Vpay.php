<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
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
    private string $error = "";

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
     * 获取错误信息
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * 创建订单
     * @param PayCreateObject $createObject
     * @return false|mixed
     */
    public function create(PayCreateObject $createObject){

        $params = $createObject->toArray();

        $key = md5($createObject->param.$createObject->price.$createObject->app_item.$createObject->pay_type);

        //预防恶意刷单，在订单有效期内重复刷取低价
        if(isset($_SESSION[$key])&&isset($_SESSION[$key."_timeout"])&&$_SESSION[$key."_timeout"]>time()){
            return $_SESSION[$key];
        }

        $params = $this->createSign($params);

        //$params['sign'] = $sign;

       // var_dump($params);exit();
        //$_SESSION['__pay_timeout']=strtotime('+'.$this->config->time.' min');

        $result = $this->request($this->config->getCreateOrder(),$params);

        $json = @json_decode($result);

        if($json){
            if($json->code===200){
                $object = new OrderObject($json->data);
                $_SESSION['key'] = $key;
                $_SESSION[$key] = $object;
                $_SESSION[$key."_timeout"] = time() + $this->config->time*60;
               return $object;
            }
       //         return $json->data;
            else{
                $this->error=$json->msg;
                return false;
            }
        }else{
            $this->error='远程支付站点发生问题，或创建订单的地址有误:'.$this->config->getCreateOrder().$result;
            return false;
        }

    }

    /**
     * 签名校验，此处校验的是notify或者return的签名
     * @param $arg
     * @return bool
     */
    private function checkSign($arg): bool
    {
        $result = PaySign::checkSign($arg,$this->config->key);
        if(!$result){
            $this->error = "签名校验失败";
            return false;
        }
        if(!isset($arg['t'])||time() - intval($arg['t']) > $this->config->time*60){
            $this->error = "该请求已经超时";
            return false;
        }
        return true;

    }

    /**
     * 响应同步回调参数，此处的数据在于$_GET
     * @return bool
     */
    public function payReturn(): bool
    {
       $bool=$this->CheckSign($_GET);
       //$payId=$this->checkClient($arg['price'],$arg['param']);
        if($bool){
            if(!isset($_SESSION['key'])){
                $this->error="支付已完成，请勿重复刷新。";
                return false;
            }
            $this->closeClient();
            return true;
        }else{
            //   if($bool)$this->error='支付已完成！请不要重复刷新！';
            return false;
        }
    }


    /**
     * 异步回调，成功后默认输出了“success”，需要在成功后结束程序运行
     * @param $callback mixed 需要提供回调函数，$callback({@link PayNotifyObject}$notifyObject)
     * @return bool
     */
    public function payNotify($callback): bool
    {
        //检查sign
        if(!$this->checkSign($_POST))return false;
        if(is_callable($callback)){
            $callback(new PayNotifyObject($_POST));
        }else{
            $this->error = "回调函数错误";
            return false;
        }
        echo "success";
        return true;

    }//此处是异步回调

    /**
     * 关闭订单
     * @param $orderId string 根据创建订单返回的order_id关闭订单
     * @return bool
     */
    public function close(string $orderId): bool
    {
        $this->closeClient();
       $result = $this->request($this->config->closeOrder(),$this->createSign([
           'order_id'=>$orderId
       ]));
        $json=json_decode($result);

        if($json->code===200){
            $this->error=$json->msg;
            return false;
        }else return true;
    }//关闭订单，主要用于用户自己开启了之后使用

    /**
     * 根据OrderId查询当前订单状态
     * @param string $orderId
     * @return false|StateObject 返回object就是查询成功，否则就是失败
     */
    public function state(string $orderId){
        $result = $this->request($this->config->getOrderState(),$this->createSign([
            'order_id'=>$orderId
        ]));
        $json = json_decode($result);
        if($json->state===200)return new StateObject($json->data);
        $this->error = $json->msg;
        return false;
    }

    /**
     * 关闭客户端订单
     * @return void
     */
    private function closeClient(){
        if(!isset($_SESSION['key'])){
            return;
        }
        $key = $_SESSION["key"];
        unset($_SESSION['key']);
        unset($_SESSION[$key]);
        unset($_SESSION[$key."_timeout"]);
    }

    /**
     * post请求数据
     * @param $url string 地址
     * @param $post_data ?array 携带数据
     * @return bool|string
     */
   private function request(string $url, ?array $post_data) {
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