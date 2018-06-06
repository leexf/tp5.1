<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29
 * Time: 12:25
 */
namespace app\admin\controller;

use Request;
class MemberRole extends Base
{
    public function _map($map){
        $post = Request::post();
        if(isset($post['role_name']) && $post['role_name'] != ''){
            $role_name = $post['role_name'];
            $map .= " and role_name like '%$role_name%' ";
        }

        if(isset($post['member_section']) && $post['member_section'] != ''){
            $member_section = $post['member_section'];
            $map .= " and section_id in ($member_section) ";
        }
        return $map;
    }

    public function index(){
        $table = 'member_role';
        $pk = db($table)->getPk();
        $columns = [
            ['field'=>'','title'=>'','width'=>0,'checkbox'=>true,],
            ['field'=>'id','title'=>'ID','width'=>50,'align'=>'center','sortable'=>'true'],
            ['field'=>'role_name','title'=>'角色名称','width'=>120],
            ['field'=>'role_desc','title'=>'角色描述','width'=>200,'align'=>'left'],
            ['field'=>'section_name','title'=>'部门名称','width'=>100,'align'=>'center'],
            ['field'=>'role_make_date','title'=>'创建时间','width'=>130],
            ['field'=>'role_make_id','title'=>'创建者','width'=>80],
            ['field'=>'role_edit_date','title'=>'修改时间','width'=>130],
            ['field'=>'role_edit_id','title'=>'修改者','width'=>80],
            ['field'=>'status','title'=>'状态','width'=>100,'align'=>'center'],
        ];
        $right_menu = [
            ['title'=>'编辑','f'=>'edit()','icon'=>'fa fa-edit'],
            ['title'=>'删除','f'=>'del()','icon'=>'fa fa-minus','menu_sep'=>true],
            ['title'=>'权限设置','f'=>'set_auth()','icon'=>''],
        ];
        $toolbar = [
            ['title'=>'角色名','name'=>'role_name','type'=>'textbox','search_type'=>'like'],
            ['title'=>'部门','name'=>'member_section','type'=>'combotree','url'=>'/'.$this->ht.'/common/member_section_combotree','multiple'=>'true','search_type'=>'in','panelWidth'=>'180'],
        ];

        $buttons = [
            ['title'=>'快速搜索','f'=>'quick_search()','icon'=>'fa fa-search'],
            ['title'=>'清空','f'=>'reset_form()','icon'=>'fa fa-trash-o'],
            ['title'=>'自定义列','f'=>'OpenGetShowColumnDlg()','icon'=>'fa fa-eye'],
            ['title'=>'导出数据','f'=>"ExportExcel('".$table."',1,'0%')",'icon'=>'fa fa-mail-forward'],
            ['title'=>'添加','f'=>'add()','icon'=>'fa fa-plus'],
        ];

        $pageList = ['15','20','30','50','100'];
        $hide_table_list = $table.'/index';
        $show_column = db('column')->where("mid=".$this->uid." and list='".$hide_table_list."'")
            ->order('id desc')->find();
        if(empty($show_column)){
            $show_column['id'] = 0;
            $show_column['show_column'] = '';
            $show_column['hide_column'] = '';
        }
        $data = [
            'fit'=>true,//自适应高度
            'table_title'=>'角色列表',
            'sortName'=>$pk,
            'sortOrder'=>"desc",
            'pagination'=>true,//分页
            'pageSize'=>15,
            'pageList'=>$pageList,
            'hide_table_list'=>$hide_table_list,
            'show_column'=>$show_column,
            'table'=>$table,
            'columns'=>$columns,
            'toolbar'=>$toolbar,
            'right_menu'=>$right_menu,
            'buttons'=>$buttons,
            'controller'=>$this->controller,
            'url'=>'/' .$this->ht. '/'.$this->controller.'/list_json/table/'.$table,
            'js'=>'/static/'.$this->ht.'/'.$this->controller.'/index.js',
        ];
        return view('index/table',$data);
    }

    public function _before_list($list){
        foreach($list['rows'] as $k=>$v){
            $v['role_make_date'] = date('Y-m-d',$v['role_make_date']);
            $v['role_make_id'] = Model('Member')->getUserName($v['role_make_id']);
            $v['role_edit_date'] = date('Y-m-d',$v['role_edit_date']);
            $v['role_edit_id'] = Model('Member')->getUserName($v['role_edit_id']);
            $section =  db('member_section')->where('id',$v['section_id'])->field('name')->find();
            $v['section_name'] = $section['name'];
            $list['rows'][$k] = $v;
        }
        return $list;
    }

    //添加之前的处理
    function _before_insert($post){
        $post['role_make_date'] = strtotime('now');
        $post['role_make_id'] = $this->uid;
        $is_exist_name = db('member_role')->where("role_name='".$post['role_name']."'")->count();
        if($is_exist_name>0){
            echo "该角色名已经存在！";exit();
        }
        return $post;
    }

    //修改之前
    function _before_edit($post){
        $post['role_edit_date'] = strtotime('now');
        $post['role_edit_id'] = $this->uid;
        $is_exist_name = db('member_role')->where("role_name='".$post['role_name']."' and id !=".$post['id'])->count();
        if($is_exist_name>0){
            echo "该角色已经存在！";exit();
        }
        return $post;
    }

    //权限设置页面
    public function set_auth($tb,$id){
        //获取所有顶级权限
        $data['topauth'] = db('auth_rule')->where("pid=0 and level=0 and status=0")->order('sort','asc')->select();
        //获取该职位现在的权限
        $arr = db('member_role')->where('id',$id)->find();
        $data['auth'] = explode(',', $arr['rules']);
        $data['tb'] = $tb;
        $data['id'] = $id;
        return view( $this->controller.'/set_auth',$data);
    }

    //保存权限更改
    public function save_set_auth(){
        $post = Request::post();
        $rules_array = $post['rules'];
        $rules['rules'] = implode(',', $rules_array);
        $rules['role_edit_date'] = strtotime('now');
        $rules['role_edit_id'] = $this->uid;
        db('member_role')->where('id',$post['id'])->update($rules);
        return "ok";
    }
}