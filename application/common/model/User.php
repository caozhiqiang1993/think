<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/1
 * Time: 13:57
 */

namespace app\common\model;


use think\Model;

class User extends Model
{
    public function editUserInfo($where = [],$data = []){
        return db('users')->where($where)->update($data);
    }
}