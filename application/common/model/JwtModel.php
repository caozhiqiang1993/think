<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/5
 * Time: 13:47
 */

namespace app\common\model;


use think\Model;
use Firebase\JWT\JWT;
use Redist\Redist;

class JwtModel extends Model
{
    public function getKey($data){
        $nowtime = time();
        $token = [
            'iss' => '', //签发者
            'aud' => '', //jwt所面向的用户
            'iat' => $nowtime, //签发时间
            'nbf' => $nowtime + 10, //在什么时间之后该jwt才可用
            'exp' => $nowtime + 600, //过期时间-10min
            'data' => $data
        ];
        $token = JWT::encode($token,config('jwt_key'));

//        $redis = Redist::getInstance(config('session'));
//        $redis->hset('redis_token_id',$data['user_id'],json_encode(['token'=>$token,'']));
        return $token;
    }
}