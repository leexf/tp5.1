<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29
 * Time: 15:40
 */

namespace app\admin\model;
use think\Model;
use Db;
use Config;

class MemberSection extends Model
{
    //获取树形数组
    public function getSectionTreeList($pid=0,$level=0){
        $where = " pid=$pid and status=0";
        $tmp = db('member_section')->where($where)->order('sort','asc')->select();
        $new = array();
        if(is_array($tmp)){
            foreach($tmp as $k=>$v){
                $new[$k]=array(
                    'id'=>$v['id'],
                    'text'=>$v['name']
                );
                $children = $this->getSectionTreeList($v['id'],$level+1);
                if(!empty($children)){
                    $new[$k]['children'] = $children;
                }
            }
        }
        return $new;
    }

    public function getChild($table,$pid,$level){
        $children = db($table)->where(['pid'=>$pid,'level'=>$level])->field('id,level')->select();
        $r = [];
        foreach($children as $k=>$v){
            $new = $this->getChild($table,$v['id'],$v['level']+1);
            $r = array_merge($children,$new);
        }
        return $r;
    }

    public function getSectionMember($map){
        $members = db('member')->where($map)->select();
        $new = array();
        foreach($members as $k=>$v){
            $new[$k]=array(
                'id'=>$v['id_member'],
                'text'=>$v['userid']
            );
        }
        return $new;
    }

}