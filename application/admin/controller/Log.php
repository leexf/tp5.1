<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/4
 * Time: 10:39
 */

namespace app\admin\controller;

use Request;
class Log extends Base
{
    public function _map($map){
        $post = Request::post();
        if(isset($post['url']) && $post['url'] != ''){
            $url = $post['url'];
            $map .= " and url like '%$url%' ";
        }
        if(isset($post['opera_type']) && $post['opera_type'] != ''){
            $opera_type = $post['opera_type'];
            $map .= " and opera_type = '$opera_type' ";
        }
        if(isset($post['mid']) && $post['mid'] != ''){
            $mid = $post['mid'];
            $map .= " and mid like '%$mid%' or username like '%$mid%' ";
        }
        if(isset($post['addtime1']) && $post['addtime1'] != ''){
            $addtime1 = strtotime($post['addtime1']);
            $map .= " and addtime >= '$addtime1' ";
        }
        if(isset($post['addtime2']) && $post['addtime2'] != ''){
            $addtime2 = strtotime($post['addtime2'])+86400;
            $map .= " and addtime < '$addtime2' ";
        }
        return $map;
    }

    public function index(){
        $table = 'log';
        $pk = db($table)->getPk();
        $columns = [
            ['field'=>'','title'=>'','width'=>0,'checkbox'=>true,],
            ['field'=>'id','title'=>'ID','width'=>50,'align'=>'center','sortable'=>'true'],
            ['field'=>'username','title'=>'操作人','width'=>120,'align'=>'center'],
            ['field'=>'opera_type','title'=>'操作类型','width'=>130,'align'=>'center'],
            ['field'=>'content','title'=>'操作内容','width'=>200,'align'=>'center'],
            ['field'=>'url','title'=>'相关网址','width'=>200],
            ['field'=>'ip','title'=>'IP','width'=>80,'align'=>'center'],
            ['field'=>'addtime','title'=>'添加时间','width'=>130,'align'=>'center'],
        ];
        $right_menu = [
            ['title'=>'删除','f'=>'del()','icon'=>'fa fa-minus'],
        ];
        $toolbar = [
            ['title'=>'相关网址','name'=>'url','type'=>'textbox','search_type'=>'like'],
            ['title'=>'操作类型','name'=>'opera_type','type'=>'textbox','search_type'=>'like'],
            ['title'=>'操作用户','name'=>'mid','type'=>'textbox','search_type'=>'like'],
            ['title'=>'操作日期','name'=>'addtime1','type'=>'datebox','search_type'=>'like'],
            ['title'=>'至','name'=>'addtime2','type'=>'datebox','search_type'=>'like'],
        ];
        $buttons = [
            ['title'=>'快速搜索','f'=>'quick_search()','icon'=>'fa fa-search'],
            ['title'=>'清空','f'=>'reset_form()','icon'=>'fa fa-trash-o'],
            ['title'=>'自定义列','f'=>'OpenGetShowColumnDlg()','icon'=>'fa fa-eye'],
            ['title'=>'导出数据','f'=>"ExportExcel('".$table."',1,'0%')",'icon'=>'fa fa-mail-forward'],
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
            'table_title'=>'日志列表',
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
        $opera_type_arr = config('opera_type');
        foreach($list['rows'] as $k=>$v){
            if(array_key_exists($v['opera_type'],$opera_type_arr)){
                $v['opera_type'] = $opera_type_arr[$v['opera_type']]."(".$v['opera_type'].")";
            }
            $v['addtime'] = date('Y-m-d',$v['addtime']);
            $list['rows'][$k] = $v;
        }
        return $list;
    }
}