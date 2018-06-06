<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/4
 * Time: 11:48
 */
namespace app\admin\controller;

use Request;
class Config extends Base
{
    public function _map($map){
        $post = Request::post();
        if(isset($post['name']) && $post['name'] != ''){
            $name = $post['name'];
            $map .= " and name like '%$name%' ";
        }
        if(isset($post['title']) && $post['title'] != ''){
            $title = $post['title'];
            $map .= " and title like '%$title%' ";
        }
        return $map;
    }

    public function index(){
        $table = 'config';
        $pk = db($table)->getPk();
        $columns = [
            ['field'=>'','title'=>'','width'=>0,'checkbox'=>true,],
            ['field'=>'id','title'=>'ID','width'=>50,'align'=>'center','sortable'=>'true'],
            ['field'=>'fenlei','title'=>'配置分类','width'=>60,'align'=>'center','sortable'=>'true'],
            ['field'=>'name','title'=>'配置名称','width'=>160,'align'=>'center'],
            ['field'=>'title','title'=>'配置标题','width'=>160,'align'=>'center'],
            ['field'=>'value','title'=>'配置值','width'=>240],
            ['field'=>'type','title'=>'配置类型','width'=>70,'align'=>'center'],
            ['field'=>'sort','title'=>'排序','width'=>50,'align'=>'center','sortable'=>'true'],
            ['field'=>'addtime','title'=>'添加时间','width'=>130,'align'=>'center','sortable'=>'true'],
            ['field'=>'status','title'=>'状态','width'=>70,'align'=>'center','sortable'=>'true'],
        ];
        $right_menu = [
            ['title'=>'编辑','f'=>'edit(600,450)','icon'=>'fa fa-edit','menu_sep'=>true],
            ['title'=>'删除','f'=>'del()','icon'=>'fa fa-minus'],
        ];
        $toolbar = [
            ['title'=>'配置名称','name'=>'name','type'=>'textbox','search_type'=>'like'],
            ['title'=>'配置标题','name'=>'title','type'=>'textbox','search_type'=>'like'],
        ];
        $buttons = [
            ['title'=>'快速搜索','f'=>'quick_search()','icon'=>'fa fa-search'],
            ['title'=>'清空','f'=>'reset_form()','icon'=>'fa fa-trash-o'],
            ['title'=>'自定义列','f'=>'OpenGetShowColumnDlg()','icon'=>'fa fa-eye'],
            ['title'=>'导出数据','f'=>"ExportExcel('".$table."',1,'0%')",'icon'=>'fa fa-mail-forward'],
            ['title'=>'添加','f'=>'add(600,450)','icon'=>'fa fa-plus'],
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
            'table_title'=>'配置列表',
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
        $config_types = config('CONFIG_TYPE_LIST');
        foreach($list['rows'] as $k=>$v){
            if(array_key_exists($v['type'],$config_types)){
                $v['type'] = $config_types[$v['type']];
            }
            $list['rows'][$k] = $v;
        }
        return $list;
    }
}