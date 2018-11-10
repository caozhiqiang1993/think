<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/20
 * Time: 17:54
 */

namespace app\index\controller;


use think\cache\driver\Redis;
use think\Controller;
use think\Log;
use GatewayClient\Gateway;
use Redist\Redist;

class Login extends Controller
{
    public function login(){
        $input = input('post.');
        ApiMessage::setCodeData(1);
        $userInfo = db('users')->where(['user_name'=>$input['username']])->find();
        if(empty($userInfo)){
            ApiMessage::setCodeData(2);
        }else{
            if(md5($input['userpsd']) != $userInfo['password']){
                ApiMessage::setCodeData(2);
            }else{
                ApiMessage::setCodeData(0,$userInfo['id']);
            }
        }

        return ApiMessage::returnData();
    }

    public function reg(){
        $input = input('post.');
        $userInfo = db('users')->where(['user_name'=>$input['username']])->find();
        if($userInfo){
            return ApiMessage::returnData(2);
        }
        $data = [
            'user_name' =>$input['username'],
            'password' =>md5($input['userpsd']),
        ];
        $userInfo['user_name'] = $input['username'];
        $userInfo['id'] = db('users')->insertGetId($data);
        db('friend_grouping')->insert(['user_id'=>$userInfo['id'],'name'=>'我的好友']);

        return ApiMessage::returnData(0);
    }

    public function test(){
        $config = [
            'host' => '127.0.0.1',
            'port' => '6379',
            'auth' => '123456',
        ];
        $redis = Redist::getInstance($config);
        $redis->set('aa','22');
        $a = $redis->get('aa');
    }
}