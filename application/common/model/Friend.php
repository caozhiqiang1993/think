<?php
namespace app\common\model;
use think\Model;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/31
 * Time: 17:37
 */
class Friend extends Model
{
    /**
     * 获取所有的好友包括自己
     * @param $user_id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getFriend($user_id){
        $users = db('user_friend')->alias('uf')
//            ->join('lt_friend_grouping fg','fg.id=uf.grouping','left')
            ->join('lt_users u','u.id=uf.f_user_id','right')
            ->where("uf.user_id = '$user_id' or u.id = $user_id")
            ->field('u.id user_id,u.user_name,u.img,u.explain,uf.grouping')
            ->order('uf.grouping asc')
            ->select();
        return $users;
    }

    /**
     * 获取所有分组
     * @param $user_id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getGrouping($user_id){
        return db('friend_grouping')->where(['user_id'=>$user_id])->order('sort_by asc')->select();
    }
}