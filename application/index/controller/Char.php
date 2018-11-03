<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/12
 * Time: 15:40
 */

namespace app\index\controller;


use think\Controller;

class Char extends Controller
{
    public function index(){
        $this->fetch();
    }
}