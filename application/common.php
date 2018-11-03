<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
//不同环境下获取真实的IP
function get_client_ip(){
    //判断服务器是否允许$_SERVER
    if(isset($_SERVER)){
        if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }elseif(isset($_SERVER["HTTP_CLIENT_IP"])) {
            $realip = $_SERVER["HTTP_CLIENT_IP"];
        }else{
            $realip = $_SERVER["REMOTE_ADDR"];
        }
    }else{
        //不允许就使用getenv获取
        if(getenv("HTTP_X_FORWARDED_FOR")){
            $realip = getenv( "HTTP_X_FORWARDED_FOR");
        }elseif(getenv("HTTP_CLIENT_IP")) {
            $realip = getenv("HTTP_CLIENT_IP");
        }else{
            $realip = getenv("REMOTE_ADDR");
        }
    }

    return $realip;
}

function dealUploadImg($filename){
// 获取表单上传文件 例如上传了001.jpg
    $file = request()->file($filename);
    // 移动到框架应用根目录/public/uploads/ 目录下
    $url = '';
    if($file){
        $info = $file->move(ROOT_PATH . 'public/uploads/user');
        if($info){
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg

            $url = request()->domain(). '/public/uploads/user/'.$info->getSaveName();
        }else{
            // 上传失败获取错误信息
//                echo $file->getError();
        }
    }
    return $url;
}
