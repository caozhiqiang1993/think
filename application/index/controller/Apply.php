<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/25
 * Time: 17:34
 */

namespace app\index\controller;


use app\index\model\ApplyList;
use app\index\model\UserFriend;
use GatewayClient\Gateway;
use think\Db;

class Apply extends Base
{
    /**
     * 好友申请
     * @return mixed
     */
    public function addApply(){
        $input = input('post.');
        ApiMessage::setCodeData(1);
        if(isset($input['f_user_id']) && !empty($input['f_user_id'])){
            $applyModel = new ApplyList();
            $is = $applyModel->where(['user_id'=>$input['f_user_id'],'f_user_id'=>$this->user_id])->find();
            if($is){
                ApiMessage::setCodeData(2);
            }else{
                $applyModel->user_id = $input['f_user_id'];
                $applyModel->f_user_id = $this->user_id;
                $applyModel->add_time = time();
                $applyModel->save();
                if($applyModel->id){
                    Gateway::sendToUid($input['f_user_id'],json_encode(['type'=>'apply']));
                    ApiMessage::setCodeData(0);
                }
            }
        }
        return ApiMessage::returnData();
    }

    /**
     * 申请好友列表
     * @return mixed
     */
    public function applyList(){
        $applyModel = new ApplyList();
        $sql = "select * from (SELECT a.id,a.status,a.memo,u.user_name,u.img,u.id user_id,a.user_id fuid,a.add_time FROM `lt_apply_list` as a LEFT JOIN lt_users as u on u.id = a.f_user_id where a.user_id = {$this->user_id}
UNION ALL
SELECT a.id,a.status,a.memo,u.user_name,u.img,u.id user_id,a.user_id fuid,a.add_time FROM `lt_apply_list` as a LEFT JOIN lt_users as u on u.id = a.user_id where a.f_user_id = {$this->user_id}) a ORDER BY add_time desc";
        $list = Db::query($sql);
        $w_count = $applyModel->where(['user_id'=>$this->user_id,'status'=>0])->count();
        $data = [
            'w_count' => $w_count,
            'list' => $list?$list:[]
        ];
        ApiMessage::setCodeData(0,$data);
        return ApiMessage::returnData();

    }
    /**
     * 申请好友处理
     * @return mixed
     */
    public function applyDeal(){
        $input = input('post.');
        ApiMessage::setCodeData(1);
        if(isset($input['id']) && isset($input['type'])){
            $apply = ApplyList::get($input['id']);
            if($apply = $apply->toArray()){
                $ress = db('user_friend')->where("user_id = {$apply['user_id']} and f_user_id = {$apply['f_user_id']}")->field('id')->select();
                $ress2 = db('user_friend')->where("user_id = {$apply['f_user_id']} and f_user_id = {$apply['user_id']}")->field('id')->select();
                if(!empty($ress) && !empty($ress2)){
                    ApplyList::where(['id'=>$input['id']])->update(['status'=>1]);
                    ApiMessage::setCodeData(1000); //已是好友
                }else{
                    if($input['type'] == 1){
                        db('user_friend')->startTrans();
                        try{
                            //获取分组的第一条，没有就创建一条‘我的好友’
                            $fgId = db('friend_grouping')->where(['user_id'=>$apply['user_id']])->order('sort_by asc')->limit(1)->value('id');
                            $f_fgId = db('friend_grouping')->where(['user_id'=>$apply['f_user_id']])->order('sort_by asc')->limit(1)->value('id');
                            if(empty($fgId)){
                                $fg = [
                                    'user_id' => $apply['user_id'],
                                    'name' => '我的好友'
                                ];
                                $fgId = db('friend_grouping')->insertGetId($fg);
                                if(empty($f_fgId)){
                                    $fg['user_id'] = $apply['f_user_id'];
                                    $f_fgId = db('friend_grouping')->insertGetId($fg);
                                }
                            }
                            //分别双方添加好友
                            $data = [
                                [
                                    'user_id' => $apply['user_id'],
                                    'f_user_id' => $apply['f_user_id'],
                                    'grouping' => $fgId,
                                    'add_time' => time(),
                                ],
                                [
                                    'user_id' => $apply['f_user_id'],
                                    'f_user_id' => $apply['user_id'],
                                    'grouping' => $f_fgId,
                                    'add_time' => time(),
                                ],
                            ];
                            $ret = db('user_friend')->insertAll($data);
                            if($ret != 2){
                                db()->rollback();
                                ApiMessage::setCodeData(1); //失败
                            }
                            ApplyList::where(['id'=>$input['id']])->update(['status'=>1]);

                            ApiMessage::setCodeData(0); //成功
                            db()->commit();
                            //推送消息给双方
                            $data = [
                                'type' => 'addUser'
                            ];
                            $field = 'id user_id,user_name,img,explain';
                            $data['user'] = model('User')->getOneUserInfo('id='.$apply['user_id'],$field);
                            $data['user']['grouping'] = $f_fgId;
                            $data['user']['isOnline'] = 1;
                            Gateway::sendToUid($apply['f_user_id'],json_encode($data));
                            $data['user'] = model('User')->getOneUserInfo('id='.$apply['f_user_id'],$field);
                            $data['user']['grouping'] = $fgId;
                            $data['user']['isOnline'] = 1;
                            $data['msg'] = [
                                'type' => 'msg',
                                'info' => '我们已经是好友了，开始聊天吧！',
                                'uid' => $apply['user_id'],
                                'fuid' => $apply['f_user_id'],
                            ];
                            Gateway::sendToUid($apply['user_id'],json_encode($data));
                            /*$data = [
                                'type' => 'msg',
                                'info' => '我们已经是好友了，开始聊天吧！',
                                'uid' => $apply['user_id'],
                                'fuid' => $apply['f_user_id'],
                            ];
                            Gateway::sendToUid($apply['f_user_id'],json_encode($data));

                            $data['uid'] = $apply['user_id'];
                            $data['fuid'] = $apply['f_user_id'];
                            Gateway::sendToUid($apply['user_id'],json_encode($data));*/

                        }catch (\Exception $e){
                            ApiMessage::setCodeData(1); //失败
                            db()->rollback();
                        }
                    }elseif($input['type'] == 2){
                        ApplyList::where(['id'=>$input['id']])->update(['status'=>2]);
                        ApiMessage::setCodeData(0); //成功
                    }
                }
            }
        }
        return ApiMessage::returnData();
    }
}