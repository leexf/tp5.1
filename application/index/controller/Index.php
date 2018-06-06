<?php
namespace app\index\controller;
use Config;
use Db;
use think\Controller;


class Index extends Controller
{
    public function index()
    {
        return Config::get('database.username');
    }

    public function hello()
    {
        $user = Db::table('user')->where('status',0)->select();
        //设置成功后跳转页面的地址，默认的返回页面是$_SERVER['HTTP_REFERER']
        //$this->success('新增成功', 'Index/index');
        return json($user);
        //print_r($user);
    }
}
