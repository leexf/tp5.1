<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/28
 * Time: 16:32
 */
namespace app\admin\controller;
use Config;
use Db;
use Session;
use Request;

class Column extends Base
{
    public function save(){
        $post = Request::post();
        $post['savetime'] = time();
        $post['mid'] = $this->uid;
        $column = db('column')->where("mid=".$this->uid." and list='".$post['list']."'")
            ->order('id desc')->find();
        if(!empty($column)){
            $post['id'] = $column['id'];
        }else{
            $post['id'] = 0;
        }
        db('column')->insert($post,true);
        return "ok";
    }
}