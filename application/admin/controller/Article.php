<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/7
 * Time: 15:00
 */
namespace app\admin\controller;

use Request;
class Article extends Base
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
        $table = 'article';
        $pk = db($table)->getPk();
        $columns = [
            ['field'=>'','title'=>'','width'=>0,'checkbox'=>true,],
            ['field'=>'id','title'=>'ID','width'=>50,'align'=>'center','sortable'=>'true'],
            ['field'=>'title','title'=>'文章标题','width'=>240],
            ['field'=>'typename','title'=>'文章分类','width'=>80,'align'=>'center'],
            ['field'=>'user','title'=>'发布者','width'=>60,'align'=>'center'],
            ['field'=>'click','title'=>'阅读量','width'=>60],
            ['field'=>'adddate','title'=>'添加日期','width'=>130],
            ['field'=>'sort','title'=>'排序','width'=>60,'sortable'=>'true'],
            ['field'=>'status','title'=>'状态','width'=>80,'align'=>'center'],
        ];
        $right_menu = [
            ['title'=>'编辑','f'=>'edit(860,500)','icon'=>'fa fa-edit'],
            ['title'=>'删除','f'=>'del()','icon'=>'fa fa-minus','menu_sep'=>true],
            ['title'=>'权限设置','f'=>'set_auth()','icon'=>''],
        ];
        $toolbar = [
            ['title'=>'标题','name'=>'title','type'=>'textbox','search_type'=>'like'],
            ['title'=>'分类','name'=>'member_section','type'=>'combotree','url'=>'/'.$this->ht.'/common/arctype_combotree','multiple'=>'true','search_type'=>'in','panelWidth'=>'180'],
        ];

        $buttons = [
            ['title'=>'快速搜索','f'=>'quick_search()','icon'=>'fa fa-search'],
            ['title'=>'清空','f'=>'reset_form()','icon'=>'fa fa-trash-o'],
            ['title'=>'自定义列','f'=>'OpenGetShowColumnDlg()','icon'=>'fa fa-eye'],
            ['title'=>'导出数据','f'=>"ExportExcel('".$table."',1,'0%')",'icon'=>'fa fa-mail-forward'],
            ['title'=>'添加','f'=>'add(860,500)','icon'=>'fa fa-plus'],
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
            'table_title'=>'文章列表',
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
            $v['adddate'] = date('Y-m-d H:i:s',$v['adddate']);
            $type =  db('arctype')->where('id',$v['type'])->field('name')->find();
            $v['typename'] = $type['name'];
            $list['rows'][$k] = $v;
        }
        return $list;
    }

    //添加之前的处理
    function _before_insert($post){
        $post['adddate'] = strtotime('now');
        $post['uid'] = $this->uid;
        $post['user'] = $this->user;
        $post['update_time'] = strtotime('now');
        $post['click'] = rand(1,200);
        $content = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "", strip_tags($post['content']));
        $post['description'] = msubstr($content,0,30);
        if(isset($post['headimg']) && $post['headimg'] == ""){
            $post['headimg'] = $this->getFirstImg($post['content']);
        }
        unset($post['upfile']);
        return $post;
    }

    //修改之前
    function _before_edit($post){
        $post['update_time'] = strtotime('now');
        $post['update_uid'] = $this->uid;
        $content = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", "", strip_tags($post['content']));
        $post['description'] = msubstr($content,0,30);
        if(isset($post['headimg']) && $post['headimg'] == ""){
            $post['headimg'] = $this->getFirstImg($post['content']);
        }
        unset($post['upfile']);
        return $post;
    }

    private function getFirstImg($content){
        $temp = mt_rand(1,4);
        $pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern,$content,$matchContent);
        if(isset($matchContent[1][0])){
            $temp = $matchContent[1][0];
        }else{
            $temp = "";
        }
        return $temp;
    }

    public function wangEditor()
    {
        $files = request()->file();
        $imags = [];
        $errors = [];
        foreach($files as $file){
            $info = $file->move( 'uploads');
            if($info){
                $path = '/uploads/'.$info->getSaveName();
                $path = str_replace('\\', '/', $path);
                array_push($imags,$path);
            }else{
                array_push($errors,$file->getError());
            }
        }
        if(!$errors){
            $msg['errno'] = 0;
            $msg['data'] = $imags;
            return json($msg);
        }else{
            $msg['errno'] = 1;
            $msg['data'] = $imags;
            $msg['msg'] = "上传出错";
            return json($msg);
        }
    }

}