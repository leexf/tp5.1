<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29
 * Time: 12:25
 */
namespace app\admin\controller;

class Msg extends Base
{
    public function index(){
        $uid = $this->uid;
        $table = 'msg';
        $columns = [
            ['field'=>'','title'=>'','width'=>0,'checkbox'=>true,],
            ['field'=>'id','title'=>'ID','width'=>50,'align'=>'center','sortable'=>'true'],
            ['field'=>'content','title'=>'消息','width'=>750,'align'=>'center','sortable'=>'true'],
            ['field'=>'isread','title'=>'是否已读','width'=>80,'align'=>'center','sortable'=>'true'],
            ['field'=>'sendtime','title'=>'收到时间','width'=>130,'align'=>'center','sortable'=>'true'],
        ];
        $pageList = ['15','20','30','50','100'];
        $toolbar = [];
        $buttons = [
            ['title'=>'导出数据','f'=>"ExportExcel('".$table."',1,'0%')",'icon'=>'fa fa-mail-forward']
        ];
        $right_menu = [
            ['title'=>'删除','f'=>'del','icon'=>'fa fa-minus'],
        ];
        $hide_table_list = $this->controller.'/index';
        $show_column = db('column')->where("mid=".$uid." and list='".$hide_table_list."'")
            ->order('id desc')->find();
        if(empty($show_column)){
            $show_column['id'] = 0;
            $show_column['show_column'] = '';
            $show_column['hide_column'] = '';
        }

        $data = [
            'table'=>$table,
            'columns'=>$columns,
            'url'=>'/' .$this->ht. '/'.$this->controller.'/list_json/table/'.$table,
            'table_title'=>'消息列表',
            'fit'=>true,//自适应高度
            'pagination'=>true,//分页
            'pageSize'=>15,
            'pageList'=>$pageList,
            'toolbar'=>$toolbar,
            'right_menu'=>$right_menu,
            'js'=>'/static/'.$this->ht.'/'.$this->controller.'/index.js',
            'hide_table_list'=>$hide_table_list,
            'show_column'=>$show_column,
            'buttons'=>$buttons,
            'controller'=>$this->controller,
            'sortName'=>"isread",
            'sortOrder'=>"asc"
        ];
        return view('index/table',$data);
    }

    public function _before_list($list){
        foreach($list['rows'] as $k=>$v){
            $v['sendtime'] = $v['sendtime']!= 0 ? date('Y-m-d H:i:s',$v['sendtime']) : '';
            $v['isread'] = $v['isread'] == '1' ? '<i style="color:green">已读</i>': '<i style="color:grey">未读</i>';
            $list['rows'][$k] = $v;
        }
        return $list;
    }
}