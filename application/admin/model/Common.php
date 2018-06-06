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

class Common extends Model
{
    //获取系统配置
    public function myConfig(){
        $data = Db::name('config')->where(array('status'=>0))->select();
        $config = array();
        if($data && is_array($data)){
            foreach ($data as $value) {
                $config[$value['name']] = $this->parse($value['type'], $value['value']);
            }
        }
        foreach ($config as $k=>$v){
            config($k,$v);
        }
    }

    /**
     * 根据配置类型解析配置
     * @param  integer $type  配置类型
     * @param  string  $value 配置值
     * @return string||array $value
     */
    public function parse($type, $value){
        switch ($type) {
            case 3: //解析数组
                $array = preg_split('/[,;|\r\n]+/', trim($value, ",;|\r\n"));
                if(strpos($value,':')){
                    $value  = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value[$k]   = $v;
                    }
                }else{
                    $value =    $array;
                }
                break;
        }
        return $value;
    }

    //将系统变量转成combo数据源
    public function sys_config_to_combo($config){
        $this->myConfig();
        $res = config($config);
        $new = array();
        if(!empty($res)){
            foreach($res as $k=>$v){
                $new[]= array(
                    'id'=>$k,
                    'text'=>$v
                );
            }
        }
        return $new;
    }

    //获取系统变量名称
    public function get_sys_config_name($config,$id=''){
        $arr = config($config);
        if(!is_array($arr)){
            return '未知';
        }
        if(array_key_exists($id,$arr)){
            return $arr[$id];
        }else{
            return '未知';
        }
    }

    public function list_json($table,$map = '',$post) {
        if (empty($map)) {
            $map = "1=1";
        }
        //排序字段 默认为主键名
        $sort = '';
        $pk = Db::name($table)->getPk();
        if (isset($post['sort'])) {
            $sort = $post['sort'];
        }else{
            $sort = $pk;
        }
        //排序方式默认按照倒序排列
        $order = '';
        if (isset($post['order'])) {
            $order = $post['order'];
        }else{
            $order = $order ? 'asc' : 'desc';
        }

        if (isset($post['rows'])) {
            $rows = $post['rows'];
        }else{
            $rows = '15';
        }

        if (isset($post['page'])) {
            $pageCurrent = $post['page'];
        }else{
            $pageCurrent =1;
        }
        if($pageCurrent==0){
            $pageCurrent =1;
        }

        $start = ($pageCurrent-1)*$rows;
        //取得满足条件的记录数
        $count = Db::name($table)->where($map)->count();
        $newList = array();
        if ($count > 0) {
            $voList = Db::name($table)->where($map)->order($sort,$order)->limit($start,$rows)->select();
            foreach($voList as $k=>$v){
                $id = $v[$pk];
                if(isset($v['status'])){
                    switch($v['status']){
                        case 0:
                            $v['status']="<img title='点击关闭' style='cursor:pointer' height='18px' src='/static/images/ok.gif' onclick='changeStatus(1,".$id.")'>";
                            break;
                        case 1:
                            $v['status']="<img title='点击开启' style='cursor:pointer' height='18px' src='/static/images/locked.gif' onclick='changeStatus(0,".$id.")'>";
                            break;
                    }
                }
                $newList[$k] = $v;
            }
            $list = array('total'=>$count,'rows'=>$newList);
        }else{
            $list = array('total'=>0,'rows'=>$newList);
        }
        return $list;
    }

    //获取权限tree 列表数据源
    public function gridTreeJson($tb,$pid=0,$where="1=1"){
        $where = $where." and pid='$pid'";
        $tmp = db($tb)->where($where)->order('sort '.' asc')->select();
        $array=array();
        if(is_array($tmp) && !empty($tmp)){
            foreach($tmp as $v){
                $v['state'] = $this->hasChild($v['id'],$tb) ? 'closed' : 'open';
                if(isset($v['status'])){
                    switch($v['status']){
                        case 0:
                            $v['status']="<img title='点击关闭' style='cursor:pointer' height='18px' src='/static/images/ok.gif' onclick='changeStatus(1,".$v['id'].")'>";
                            break;
                        case 1:
                            $v['status']="<img title='点击开启' style='cursor:pointer' height='18px' src='/static/images/locked.gif' onclick='changeStatus(0,".$v['id'].")'>";
                            break;
                    }
                }
                array_push($array, $v);
            }
        }
        return $array;
    }

    public function hasChild($id,$tb){
        $count = db($tb)->where('pid',$id)->count();
        return $count > 0 ? 1 : 0;
    }

    //获取树形数组
    public function getTreeList($pid=0,$level=0,$table=0){
        $tmp = db($table)->where(["pid"=>$pid,"level"=>$level,"status"=>0])->order('sort','asc')->select();
        $new = array();
        if(is_array($tmp)){
            foreach($tmp as $k=>$v){
                if($table == 'menu'){
                    $new[$k]=array(
                        'id'=>$v['id'],
                        'text'=>$v['catename']
                    );
                }else if($table == 'member_section'){
                    $new[$k]=array(
                        'id'=>$v['id'],
                        'text'=>$v['name']
                    );
                }else{
                    $new[$k]=array(
                        'id'=>$v['id'],
                        'text'=>$v['title']
                    );
                }
                $children = $this->getTreeList($v['id'],$v['level']+1,$table);
                if(is_array($children)){
                    $new[$k]['children'] = $children;
                }
            }
        }
        return $new;
    }

    //获取最大sort
    public function get_max_sort($table,$idName,$idVal){
        $r = db($table)->where($idName,$idVal)->max('sort');
        if($r){
            return $r+1;
        }else{
            return 0;
        }
    }

}