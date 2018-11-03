<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/25
 * Time: 14:09
 */

namespace app\index\controller;


use app\index\model\Users;

class User extends Base
{
    /**
     * 查找用户
     */
    public function getUserInfo(){
        $input = input('post.');
        ApiMessage::setCodeData(1);
        if(isset($input['username']) && !empty($input['username'])){
            $info = Users::get(['user_name'=>$input['username']]);
            if($info){
                ApiMessage::setCodeData(0,$info);
            }
        }
        return ApiMessage::returnData();
    }

    /**
     * 修改头像
     * @return \think\response\Json
     */
    public function uploadImg(){
        ApiMessage::setCodeData(1);
        $url = dealUploadImg('img');
        if($url){
            model('User')->editUserInfo(['id'=>session('user_id')],['img'=>$url]);
            ApiMessage::setCodeData(0,$url);
        }
        return ApiMessage::returnData();
    }

    /**
     * 修改个人信息
     * @return \think\response\Json
     */
    public function editUserInfo(){
        $input = input('post.');
        model('User')->editUserInfo(['id'=>session('user_id')],['explain'=>$input['explain']]);
        return ApiMessage::returnData(0);
    }
}