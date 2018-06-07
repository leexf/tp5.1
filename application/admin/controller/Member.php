<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29
 * Time: 12:25
 */
namespace app\admin\controller;
use Session;
use think\facade\Request;
use app\admin\model\Member as MemberModel;

class Member extends Base
{
    public function _map($map){
        $post = Request::post();
        if(isset($post['id']) && $post['id'] != ''){
            $map .= " and id = '".$post['id']."' ";
        }
        if(isset($post['username']) && $post['username'] != ''){
            $username = $post['username'];
            $map .= " and chance_title like '%$username%' ";
        }
        if(isset($post['truename']) && $post['truename'] != ''){
            $truename = $post['truename'];
            $map .= " and truename like '%$truename%' ";
        }
        if(isset($post['member_level']) && $post['member_level'] != ''){
            $member_level = $post['member_level'];
            $map .= " and member_level in ($member_level)";
        }
        if(isset($post['member_role']) && $post['member_role'] != ''){
            $member_role = $post['member_role'];
            $map .= " and role_id in ($member_role) ";
        }
        if(isset($post['member_area']) && $post['member_area'] != ''){
            $member_area = $post['member_area'];
            $map .= " and member_area in ($member_area) ";
        }
        if(isset($post['member_section']) && $post['member_section'] != ''){
            $member_section = $post['member_section'];
            $map .= " and section_id in ($member_section) ";
        }
        return $map;
    }

    public function index(){
        $table = 'member';
        $columns = [
            ['field'=>'','title'=>'','width'=>0,'checkbox'=>true,],
            ['field'=>'id','title'=>'ID','width'=>50,'align'=>'center','sortable'=>'true'],
            ['field'=>'username','title'=>'用户名','width'=>80,'align'=>'center','sortable'=>'true'],
            ['field'=>'truename','title'=>'真实姓名','width'=>100,'align'=>'center','sortable'=>'true'],
            ['field'=>'section_name','title'=>'用户部门','width'=>90,'align'=>'center','sortable'=>'true'],
            ['field'=>'role_name','title'=>'用户角色','width'=>80,'align'=>'center','sortable'=>'true'],
            ['field'=>'member_level_name','title'=>'用户等级','width'=>70,'align'=>'center','sortable'=>'true'],
            ['field'=>'member_area','title'=>'用户地区','width'=>70,'align'=>'center','sortable'=>'true'],
            ['field'=>'loginip','title'=>'登录IP','width'=>80,'align'=>'center','sortable'=>'true'],
            ['field'=>'logintime','title'=>'登录日期','width'=>130,'align'=>'center','sortable'=>'true'],
            ['field'=>'adddate','title'=>'添加日期','width'=>130,'align'=>'center','sortable'=>'true'],
            ['field'=>'status','title'=>'用户状态','width'=>70,'align'=>'center','sortable'=>'true'],
        ];
        $pageList = ['15','20','30','50','100'];
        $toolbar = [
            ['title'=>'用户名','name'=>'username','type'=>'textbox','search_type'=>'like'],
            ['title'=>'真实姓名','name'=>'truename','type'=>'textbox','search_type'=>'like'],
            ['title'=>'部门','name'=>'member_section','type'=>'combotree','url'=>'/'.$this->ht.'/common/member_section_combotree','multiple'=>'true','search_type'=>'in','panelWidth'=>'180'],
            ['title'=>'用户等级','name'=>'member_level','type'=>'combobox','url'=>'/'.$this->ht.'/common/sysCombo/config/member_level_name','search_type'=>'in'],
            ['title'=>'用户角色','name'=>'member_role','type'=>'combobox','url'=>'/'.$this->ht.'/common/member_role_combo','search_type'=>'in'],
            ['title'=>'地区','name'=>'member_area','type'=>'combobox','url'=>'/'.$this->ht.'/common/sysCombo/config/member_province_name','multiple'=>'true','search_type'=>'in'],
        ];

        $buttons = [
            ['title'=>'快速搜索','f'=>'quick_search()','icon'=>'fa fa-search'],
            ['title'=>'清空','f'=>'reset_form()','icon'=>'fa fa-trash-o'],
            ['title'=>'自定义列','f'=>'OpenGetShowColumnDlg()','icon'=>'fa fa-eye'],
            ['title'=>'导出数据','f'=>"ExportExcel('member',1,'0%')",'icon'=>'fa fa-mail-forward'],
            ['title'=>'添加','f'=>'add()','icon'=>'fa fa-plus'],
        ];

        if(Model('AuthRule')->authCheck('system/show_pk',$this->uid)){
            $first = array('title'=>'ID','name'=>'id','type'=>'textbox','search_type'=>'=');
            array_unshift($toolbar,$first);
        }
        $right_menu = array(
            array('title'=>'编辑','f'=>'edit()','icon'=>'fa fa-edit','menu_sep'=>true),
            array('title'=>'删除','f'=>"del(0)",'icon'=>'fa fa-minus'),
        );
        $hide_table_list = 'member/index';
        $show_column = db('column')->where("mid=".$this->uid." and list='".$hide_table_list."'")
            ->order('id desc')->find();
        if(empty($show_column)){
            $show_column['id'] = 0;
            $show_column['show_column'] = '';
            $show_column['hide_column'] = '';
        }

        $data = [
            'controller'=>$this->controller,
            'table'=>$table,
            'columns'=>$columns,
            'url'=>'/' .$this->ht. '/'.$this->controller.'/list_json/table/'.$table,
            'table_title'=>'用户列表',
            'fit'=>true,//自适应高度
            'pagination'=>true,//分页
            'pageSize'=>15,
            'pageList'=>$pageList,
            'toolbar'=>$toolbar,
            'right_menu'=>$right_menu,
            'buttons'=>$buttons,
            'js'=>'/static/'.$this->ht.'/'.$this->controller.'/index.js',
            'hide_table_list'=>$hide_table_list,
            'show_column'=>$show_column,
            'sortName'=>"id",
            'sortOrder'=>"desc"
        ];
        return view('index/table',$data);
    }
    //列表显示之前的处理
    public function _before_list($list){
        foreach($list['rows'] as $k=>$v){
            $v['adddate'] = $v['adddate']!= 0 ? date('Y-m-d H:i:s',$v['adddate']) : '';
            $v['add_user'] = MemberModel::get($v['member_make_id'])->username;
            $v['role_name'] = Model('Member')->getRoleField($v['id'],'role_name');
            $v['section_name'] = Model('Member')->getSectionField($v['id'],'name');
            $v['member_level_name'] = Model('Common')->get_sys_config_name('member_level_name',$v['member_level']);//用户等级
            $v['member_area'] = Model('Common')->get_sys_config_name('member_province_name',$v['member_area']);//用户地区

            $list['rows'][$k] = $v;
        }
        return $list;
    }
    //导出之前的处理
    public function _before_export($result){
        foreach ($result as $k=>&$v){
            unset($v['status']);
            unset($v['id']);
        }
        return $result;
    }

    //添加之前的处理
    function _before_insert($post){
        $post['adddate'] = strtotime('now');
        $post['member_make_id'] = $this->uid;
        $post['hash']= substr(md5(time()),0,5);
        $post['password'] = md5($post['hash'].md5(trim($post['password'])));
        $is_exist_name = db('member')->where("username='".$post['username']."'")->count();
        if($is_exist_name>0){
            echo "该用户名已经存在！";exit();
        }
        $file = request()->file('upfile');
        if( $file != "") {
            $info = $file->move('uploads');
            if ($info) {
                $post['cover_img'] = '/uploads/' . $info->getSaveName();
                $post['cover_img'] = str_replace('\\', '/', $post['cover_img']);
            } else {
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
        return $post;
    }

    //修改用户之前
    function _before_edit($post){
        $post['update_time'] = strtotime('now');
        $post['member_edit_id'] = $this->uid;
        $is_exist_name = db('member')->where("username='".$post['username']."' and id !=".$post['id'])->count();
        if($is_exist_name>0){
            echo "该用户名已经存在！";exit();
        }
        $file = request()->file('upfile');
        if($file != ""){
            $info = $file->move( 'uploads');
            if($info){
                //删除之前的头像
                $arr = db('member')->where('id',$post['id'])->find();
                $img = $arr['cover_img'];
                if(is_file($img)){
                    unlink($img);
                }
                $post['cover_img'] = '/uploads/'.$info->getSaveName();
                $post['cover_img'] = str_replace('\\','/',$post['cover_img']);
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
        return $post;
    }

    public function batch_fp($uid){
        $map = " chance_user_id=$uid and chance_del=1 and is_chance_suss !=1 and lost=0";
        $chance = db('sale_chance')->where($map)->field('id_sale_chance')->select();
        foreach($chance as $k=>$v){
            $new[] = $v['id_sale_chance'];
        }
        $chance_count = count($chance);
        if($chance_count == 0){
            exit("没有数据");
        }
        $userinfo = MemberModel::get($uid)->toArray();
        $member_area = $userinfo['member_area'];
        $members = db('member')->where('member_area='.$member_area.' and role_id=36 and status=0')->field('id,username')->select();
        $members_count = count($members);
        if($members_count == 0){
            exit("没有要分配的人");
        }
        $every_fp = floor($chance_count/$members_count);
        $yu = $chance_count%$members_count;
        //print_r("共".$chance_count."条数据，平均分给".$members_count."个人，每人得".$every_fp."，剩下".$yu);exit();

        $n = array_chunk($new,$every_fp);
        if($yu>0){
            foreach($n[$members_count] as $k=>$v){
                array_push($n[$k],$v);
            }
            unset($n[$members_count]);
        }
        foreach($n as $k=>$v){
            $chance_list[] = implode(',',$v);
        }
        foreach($members as $k=>$v){
            $set = array('chance_user_id'=>$v['id'],'chance_start_date'=>time());
            //db('sale_chance')->where("id_sale_chance in (".$chance_list[$k].")")->update($set);
            echo "分给".$v['username']." ".count($n[$k])."条</br>";
        }
        //print_r($chance_list);exit();

    }
}