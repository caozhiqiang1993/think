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

class Login extends Controller
{
    public function login(){
        $input = input('post.');
        ApiMessage::setCodeData(1);
        $userInfo = db('users')->where(['user_name'=>$input['username']])->find();
        if(empty($userInfo)){
            $data = [
                'user_name' =>$input['username'],
                'password' =>md5($input['userpsd']),
            ];
            $userInfo['user_name'] = $input['username'];
            $userInfo['id'] = db('users')->insertGetId($data);
            db('friend_grouping')->insert(['user_id'=>$userInfo['id'],'name'=>'我的好友']);
        }else{
            if(md5($input['userpsd']) != $userInfo['password']){
                return json($arr);
            }
        }
        return ApiMessage::returnData(0,$userInfo['id']);
    }
}