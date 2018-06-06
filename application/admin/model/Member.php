<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/24
 * Time: 12:01
 */

namespace app\admin\model;
use think\Model;
use Db;
use Config;

class Member extends Model
{
    //获取用户角色表的某一字段
    public function getRoleField($uid,$field){
        $arr =  Member::alias('m')->where('m.id',$uid)
            ->join('member_role', 'member_role.id = m.role_id')
            ->find();
        if(!empty($arr))
        {
            return $arr[$field];
        }else{
            return '';
        }
    }

    //获取部门表的某一字段
    public function getSectionField($uid,$field){
        $arr =  Member::alias('m')->where('m.id',$uid)
            ->join('member_section', 'member_section.id = m.section_id')
            ->find();
        if(!empty($arr))
        {
            return $arr[$field];
        }else{
            return '';
        }
    }

    //获取用户名称
    public function getUserName($uid){
        $res = db('member')->where('id',$uid)->find();
        $username = '';
        if(!empty($res)){
            $username = $res['username'];
        }
        return $username;
    }

    /**
     * 获取指定用户的区域
     * */
    public function get_member_areas_name($_id=0){
        $out= '';
        $r=null;$r = db('member')->where('id= '.$_id)->find();
        if (!$r){return $out;}
        $out= $this->city->get_member_area_by($r['member_area']);

        return $out;
    }

}