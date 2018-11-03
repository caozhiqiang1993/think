<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/25
 * Time: 14:19
 */

namespace app\index\controller;


use think\Exception;

class ApiMessage
{
    public static $code;
    public static $data = [];

    /**
     * @param null $code    状态码
     * @param null $data    数据
     */
    public static function setCodeData($code = null,$data = null){
        if($code !== null){
            self::$code = $code;
        }
        if($data !== null){
            self::$data = $data;
        }
    }

    /**
     * @param null $code    状态码
     * @param null $data    数据
     */
    public static function returnData($code = null,$data = null){
        self::setCodeData($code,$data);
        $res = [
            'status' => self::$code,
            'msg' => self::getMsg(),
            'data' => self::$data
        ];
        return json_encode($res);
    }

    /**
     * 获取状态码消息
     * @param $code      状态码
     * @return string
     */
    public static function getMsg(){
        try{
            $msg = config('message.code')[self::$code];
        }catch (\Exception $e){
            $msg = '';
        }
        return $msg;
    }

}