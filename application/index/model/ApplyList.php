<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/25
 * Time: 17:39
 */

namespace app\index\model;

use think\Model;

class ApplyList extends Model
{
    protected $resultSetType = 'collection';
    public function users()
    {
        return $this->hasOne('users','f_user_id')->field('user_name');
    }
}