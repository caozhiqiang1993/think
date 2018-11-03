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
            $applyModel->user_id = $input['f_user_id'];
            $applyModel->f_user_id = session('user_id');
            $applyModel->add_time = time();
            $applyModel->save();
            if($applyModel->id){
                ApiMessage::setCodeData(0);
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
        $list = $applyModel->alias('a')
            ->join('__USERS__ u','a.f_user_id=u.id')
            ->where(['a.user_id'=>session('user_id')])
            ->order('id desc')
            ->field('a.id,a.status,a.memo,u.user_name,u.img')
            ->select();
        $list = $list->toArray();
        $w_count = $applyModel->where(['user_id'=>session('user_id'),'status'=>0])->count();
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
                $ress = UserFriend::where('user_id','in',"{$apply['user_id']},{$apply['f_user_id']}")->field('id')->select();
                if(count($ress) == 2){
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