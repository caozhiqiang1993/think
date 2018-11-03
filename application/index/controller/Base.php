<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/25
 * Time: 14:01
 */

namespace app\index\controller;


use think\Controller;
use think\Request;

class Base extends Controller
{
    public $user_id;
    public function _initialize()
    {
        $info = Request::instance()->header();
        if($info['authorization'] != 'undefined' && !empty($info['authorization'])){
            if($info['authorization'] != input('post.user_id')){
                die(ApiMessage::returnData(10000));
            }
            $this->user_id = $info['authorization'];
        }else{
            die(ApiMessage::returnData(10000));
        }
    }
}