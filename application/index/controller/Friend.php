<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/27
 * Time: 13:58
 */

namespace app\index\controller;
use GatewayClient\Gateway;



class Friend extends Base
{
    /**
     * 获取所有的好友和分组
     * @return \think\response\Json
     */
    public function getFriend(){
//        $user_id = session('user_id');
        $user_id = $this->user_id;
        $fz = model('Friend')->getGrouping($user_id);
        $users = model('Friend')->getFriend($user_id);
        foreach($users as $k=>$v){
            $users[$k]['isOnline'] = Gateway::isUidOnline($v['user_id']);
        }

        $users = array_column($users,null,'user_id');
        $users[$user_id]['grouping'] = $fz[0]['id'];
        $data = [
            'friend' => $users,
            'grouping' => $fz,
        ];
        return ApiMessage::returnData(0,$data);
    }

}