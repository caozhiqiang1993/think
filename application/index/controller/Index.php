<?php
namespace app\index\controller;
use \think\Controller;

class Index extends Base
{
    public function index()
    {
        $this->request->token();
        $user_id = session('user_id');
        $fz = model('Friend')->getGrouping($user_id);
        $users = model('Friend')->getFriend($user_id);
        $users = array_column($users,null,'user_id');
        if(isset($users[$user_id])){
            $users[$user_id]['grouping'] = $fz[0]['id'];
        }
        $data = [
            'grouping' => json_encode($fz),
            'friend' => json_encode($users),
        ];

        $this->assign('data',$data);
        $this->assign('user_id',$user_id);
        $this->assign('user_name',session('user_name'));
        return $this->fetch();
    }
}
