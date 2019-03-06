<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/25
 * Time: 14:01
 */

namespace app\index\controller;


use Firebase\JWT\JWT;
use think\Controller;
use think\Exception;
use think\Request;

class Base extends Controller
{
    public $user_id;
    public function _initialize()
    {
        $info = Request::instance()->header();
        if($info['authorization'] != 'undefined' && !empty($info['authorization'])){
            try{
                JWT::$leeway = 60;
                $data = (array) JWT::decode($info['authorization'],config('jwt_key'),['HS256']);
                if($data['exp'] < time()){
                    throw new \Exception('Expired token');
                }

            }catch(\Exception $e){
                die(ApiMessage::returnData(10001));
            }
            $this->user_id = $data['data']->user_id;
        }else{
            die(ApiMessage::returnData(10000));
        }
    }
}