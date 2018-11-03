<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/14
 * Time: 10:46
 */

namespace app\api\controller;
use app\api\model\Users;
use Firebase\JWT\JWT;
use localIp\IpLocation;

header('Access-Control-Allow-Origin:*');

// 响应类型

header('Access-Control-Allow-Methods:POST,GET');

// 响应头设置，允许设置Authorization和lpy这两个http头

header('Access-Control-Allow-Headers:Authorization,Test');
class Login
{
    public function login(){

        $header = apache_request_headers();
//        $key = $_SERVER[HTTP_AUTHORIZATION];
        $userName = input('get.user');
        $password = input('get.psd');
//        $user = new Users();
//        $userInfo = $user::get(['user_name'=>$userName]);
        $userInfo = DB('users')->where(['user_name'=>$userName])->find();
        if(empty($userInfo) || md5($password) != $userInfo['password']){
            return jsonp(['status'=>false,'msg'=>'账号密码错误','data'=>'']);
        }
        $nowtime = time();
        $token = [
            'iss' => 'http://192.168.2.52', //签发者
            'aud' => 'http://192.168.2.52', //jwt所面向的用户
            'iat' => $nowtime, //签发时间
            'nbf' => $nowtime + 10, //在什么时间之后该jwt才可用
            'exp' => $nowtime + 600, //过期时间-10min
            'data' => [
                'uid' => $userInfo['id'],
                'username' => $userName
            ]
        ];
        $jwt = JWT::encode($token, config('jwt_key'));
        $data = [
            'key' => $jwt,
            'uid' => $userInfo['id']
        ];
        return jsonp(['status'=>true,'msg'=>'登录成功','data'=>$data]);
    }

    public function bindUid(){
        $client_id = input('get.client_id');
        $key = input('get.key');
        $res['msg'] = '';
        try {
            JWT::$leeway = 60;
            $decoded = JWT::decode($key, config('jwt_key'), ['HS256']);
            $arr = (array)$decoded;
            if ($arr['exp'] < time()) {
                $res['msg'] = '请重新登录';
            }
        }catch(BeforeValidException $e){
            $res['msg'] = $e->getMessage();
        }catch(SignatureInvalidException $e){
            $res['msg'] = $e->getMessage();
        }catch(\Exception $e) {
            $res['msg'] = $e->getMessage();
        }
        if($res['msg']){
            $res['status'] = false;
            return jsonp($res);
        }

        import('GatewayClient.Gateway', EXTEND_PATH, '.class.php');
        \Gateway::bindUid($client_id,$arr['data']['uid']);
        $res['status'] = true;
        $res['msg'] = '成功';
        return jsonp($res);
    }

    public function test(){
        $ip = new IpLocation('qqwry.dat');
        $res = $ip->getlocation();
        print_r($res);
    }
}