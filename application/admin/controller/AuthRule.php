<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/5
 * Time: 14:22
 */
namespace app\admin\controller;

class AuthRule extends Base
{
    public function index(){
        $table = 'auth_rule';
        $columns = [
            ['field'=>'','title'=>'','width'=>0,'checkbox'=>true,],
            ['field'=>'id','title'=>'ID','width'=>50,'align'=>'center','sortable'=>'true'],
            ['field'=>'title','title'=>'权限名称','width'=>250],
            ['field'=>'name','title'=>'权限地址','width'=>300],
            ['field'=>'sort','title'=>'排序','width'=>100,'align'=>'center','sortable'=>'true'],
            ['field'=>'status','title'=>'状态','width'=>100,'align'=>'center'],
        ];
        $right_menu = [
            ['title'=>'编辑','f'=>'edit()','icon'=>'fa fa-edit','menu_sep'=>true],
            ['title'=>'删除','f'=>'del()','icon'=>'fa fa-minus'],
        ];
        $buttons = [
            ['title'=>'添加','f'=>'add()','icon'=>'fa fa-plus']
        ];
        $data = [
            'fit'=>true,//自适应高度
            'idField'=>'id',
            'treeField'=>'title',
            'table_title'=>'权限列表',
            'sortName'=>"sort",
            'sortOrder'=>"asc",
            'table'=>$table,
            'columns'=>$columns,
            'right_menu'=>$right_menu,
            'buttons'=>$buttons,
            'controller'=>$this->controller,
            'url'=>'/' .$this->ht. '/'.$this->controller.'/gridTreeJson/table/'.$table,
            'js'=>'/static/'.$this->ht.'/'.$this->controller.'/index.js',
        ];
        return view('index/tree_grid',$data);
    }

    //添加之前的处理
    function _before_insert($post){
        if($post['pid'] == 0){
            $post['level'] = 0;
        }else{
            $level = db('auth_rule')->field('level')->where('id',$post['pid'])->find();
            $post['level'] = $level['level']+1;
        }
        $is_exist_name = db('auth_rule')->where("name='".$post['name']."'")->count();
        if($is_exist_name>0){
            echo "该权限已经存在！";exit();
        }

        return $post;
    }

    //修改之前
    function _before_edit($post){
        if($post['pid'] == 0){
            $post['level'] = 0;
        }else{
            $level = db('auth_rule')->field('level')->where('id',$post['pid'])->find();
            $post['level'] = $level['level']+1;
        }
        $is_exist_name = db('auth_rule')->where("name='".$post['name']."' and id!=".$post['id'])->count();
        if($is_exist_name>0){
            echo "该权限已经存在！";exit();
        }
        return $post;
    }

    //删除之前，找出子权限，全部删除
    public function _before_del($post){
        $id = $post['id'];
        $id_arr = explode(',',$id);
        foreach($id_arr as $k=>$v){
            $table = 'auth_rule';
            $r = db($table)->where('id',$v)->field('level')->find();
            $level = $r['level'];
            $children = Model('MemberSection')->getChild($table,$v,$level+1);
            foreach ($children as $kk=>$vv){
                $post['id'] .= ','.$vv['id'];
            }
        }
        return $post;
    }
}