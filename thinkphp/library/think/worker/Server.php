<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\worker;

use Workerman\Worker;
use GatewayWorker\Register;
use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;

/**
 * Worker控制器扩展类
 */
abstract class Server
{
    protected $worker;
    protected $worker2;
    protected $socket    = '';
    protected $protocol  = 'http';
    protected $host      = '0.0.0.0';
    protected $port      = '2346';
    protected $processes = 1;

    /**
     * 架构函数
     * @access public
     */
    public function __construct()
    {
        //初始化各个GatewayWorker
        //初始化register
        new Register('text://0.0.0.0:1236');

        // 初始化 gateway 进程
        $gateway = new Gateway("websocket://0.0.0.0:7272");
        $gateway->name = 'YourAppGateway';
        $gateway->count = 4;
        $gateway->lanIp = '127.0.0.1';
        $gateway->startPort = 2300;
        // 心跳间隔
        $gateway->pingInterval = 10;
        // 心跳数据
        $gateway->pingData = '{"type":"ping"}';
        // 服务注册地址
        $gateway->registerAddress = '127.0.0.1:1236';

        //初始化 bussinessWorker 进程
        $worker = new BusinessWorker();
        $worker->name = 'YourAppBusinessWorker';
        $worker->count = 4;
        $worker->registerAddress = '127.0.0.1:1236';
        // 初始化
        $this->init();

        // 设置回调
        foreach (['onWorkerStart', 'onConnect', 'onMessage', 'onClose', 'onError', 'onBufferFull', 'onBufferDrain', 'onWorkerStop', 'onWorkerReload'] as $event) {
            if (method_exists($this, $event)) {
                $this->worker->$event = [$this, $event];
            }
        }
        // Run worker
        Worker::runAll();
    }

    protected function init()
    {
    }

}