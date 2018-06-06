<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/25
 * Time: 9:44
 */

namespace app\admin\model;
use think\Model;
use Db;
use Config;

class Msg extends Model
{
    //获取今天是否发送过了消息
    public function get_today_is_send(){
        $today = date('Y-m-d');
        $map = [
            "type"=>0,
            "FROM_UNIXTIME(sendtime,'%Y-%m-%d')"=>$today
        ];
        $count = Msg::where($map)->count();
        return $count;
    }

    //获取消息
    public function getMsg($uid,$type=0){
        $where = "is_del=1 and isread=0 and msgto=$uid";
        if($type!=0){
            $where .= " and type=$type ";
        }
        $msg = Msg::where($where)->select();
        return $msg;
    }

    //标记已读
    public function readed($id){
        $data = ['isread'=>1];
        Msg::where('id',$id)->update($data);
    }
}