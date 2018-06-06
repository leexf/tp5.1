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
}