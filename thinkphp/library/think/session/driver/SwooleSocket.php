<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/21
 * Time: 13:33
 */
class SwooleSocket
{
    public $server;
    public $users = [];
    public $table = [];
    public function __construct() {
        /*$table = new swoole_table(1024);
        $table->column('fd', swoole_table::TYPE_INT);
        $table->create();
        $this->table = $table;*/

        $this->server = new swoole_websocket_server("0.0.0.0", 9501);
        $this->server->on('open', function (swoole_websocket_server $server, $request) {
            echo "server:{$request->fd} 连接进来\n";
        });
        $this->server->on('message', function (swoole_websocket_server $server, $frame) {
//            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            $users = [];
            echo $frame->data;
            foreach ($this->server->connections as $fd) {
                /*if($fd != $frame->fd){
                    $users[] = $fd;
                }*/
                echo $fd.'-';
            }
            $data = json_decode($frame->data,true);
            if($data['type'] == 'login'){
                /*if(isset($this->users[$data['uid']]) && !empty($this->users[$data['uid']])){
                    $server->disconnect($this->users[$data['uid']]);
                }
                $this->users[$data['uid']] = $frame->fd;*/
//                $data['uid'] = $frame->fd;
                if($data['uid']){
                    $this->users[$data['uid']] = [
                        'fd' => $frame->fd,
                        'user_name' => $data['user_name'],
                        'user_id' => $data['uid'],
                    ];
                }
                $data['data'] = array_values($this->users);
//                $this->server->push($frame->fd, json_encode($data));
                var_dump($this->users);
                foreach($this->users as $k => $v){
                    if($data['uid']){
                        $this->server->push($v['fd'], json_encode($data));
                    }
                }
            }elseif($data['type'] == 'msg'){
                $data['uid'] = $data['uid'];
                $data['user_name'] = $data['user_name'];
                $this->server->push($this->users[$data['fuid']]['fd'], json_encode($data));
            }
        });
        $this->server->on('close', function ($ser, $fd) {
//            unset($this->users[$fd]);
            echo "client {$fd} closed\n";
        });
        $this->server->on('request', function ($request, $response) {
            // 接收http请求从get获取message参数的值，给用户推送
            // $this->server->connections 遍历所有websocket连接用户的fd，给所有用户推送
            foreach ($this->server->connections as $fd) {
                $this->server->push($fd, $request->get['message']);
            }
        });
        $this->server->start();
    }
}
new SwooleSocket();