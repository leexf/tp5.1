<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/28
 * Time: 15:58
 */
namespace app\admin\controller;
use Session;
use think\facade\Request;

class Call extends Base
{
    public function _map($map){
        $post = Request::post();
        if(isset($post['id_sale_chance']) && $post['id_sale_chance'] != ''){
            $map .= " and id_sale_chance = '".$post['id_sale_chance']."' ";
        }
        if(isset($post['chance_title']) && $post['chance_title'] != ''){
            $chance_title = $post['chance_title'];
            $map .= " and chance_title like '%$chance_title%' ";
        }
        if(isset($post['chance_mobile']) && $post['chance_mobile'] != ''){
            $chance_mobile = $post['chance_mobile'];
            $map .= " and chance_mobile like '%$chance_mobile%' ";
        }
        if(isset($post['chance_phone']) && $post['chance_phone'] != ''){
            $chance_phone = $post['chance_phone'];
            $map .= " and chance_phone like '%$chance_phone%' ";
        }
        if(isset($post['chance_wechat']) && $post['chance_wechat'] != ''){
            $chance_wechat = $post['chance_wechat'];
            $map .= " and chance_wechat like '%$chance_wechat%' ";
        }

        if(isset($post['chance_gravidity_state']) && $post['chance_gravidity_state'] != ''){
            $chance_gravidity_state = $post['chance_gravidity_state'];
            $map .= " and chance_gravidity_state in ($chance_gravidity_state) ";
        }
        return $map;
    }

    public function index(){
        $table = 'sale_chance';
        $pk = db($table)->getPk();
        $columns = array(
            array('field'=>'','title'=>'','width'=>0,'checkbox'=>true,),
            array('field'=>'id_sale_chance','title'=>'ID','width'=>50,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_title','title'=>'机会名称','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_user','title'=>'所属销售','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'childbirth_date','title'=>'预产期','width'=>120,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_source','title'=>'机会来源','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_ask_type','title'=>'咨询方式','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_ask_type_source','title'=>'400电话来源','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_web_source','title'=>'网站来源','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_url_source','title'=>'链接来源','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_keywords','title'=>'关键字','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_gravidity_state','title'=>'怀孕状态','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_gravidity_mon','title'=>'怀孕月数','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_visa_state','title'=>'签证状态','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_area_name','title'=>'客户区域','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_province_name','title'=>'客户省市','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_city_name','title'=>'客户地区市','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_make_date','title'=>'创建时间','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'chance_make_member','title'=>'创建人','width'=>100,'align'=>'center','sortable'=>'true'),
            array('field'=>'fp_time','title'=>'分配时间','width'=>100,'align'=>'center','sortable'=>'true'),

        );
        $pageList = array('15','20','30','50','100');
        $toolbar = array(
            array('title'=>'联系人','name'=>'chance_title','type'=>'textbox','search_type'=>'like'),
            array('title'=>'手机','name'=>'chance_mobile','type'=>'textbox','search_type'=>'like'),
            array('title'=>'电话','name'=>'chance_phone','type'=>'textbox','search_type'=>'like'),
            array('title'=>'微信','name'=>'chance_wechat','type'=>'textbox','search_type'=>'like'),
            array('title'=>'怀孕状态','name'=>'chance_gravidity_state','type'=>'combobox','url'=>'/'.config('HT_FILE_NAME').'/common/sysCombo/config/sale_chance_gravidity_state','search_type'=>'in'),
            array('title'=>'签证状态','name'=>'chance_visa_state','type'=>'combobox','url'=>'/'.config('HT_FILE_NAME').'/common/sysCombo/config/sale_chance_visa_state','search_type'=>'in'),
            array('title'=>'机会来源','name'=>'chance_source','type'=>'combobox','url'=>'/'.config('HT_FILE_NAME').'/common/sysCombo/config/sale_chance_source','search_type'=>'in'),
            array('title'=>'咨询方式','name'=>'chance_ask_type','type'=>'combobox','url'=>'/'.config('HT_FILE_NAME').'/common/sysCombo/config/sale_chance_ask_type','multiple'=>'true','search_type'=>'in'),
            array('title'=>'网站来源','name'=>'chance_web_source','type'=>'combobox','url'=>'/'.config('HT_FILE_NAME').'/common/sysCombo/config/sale_chance_web_source','search_type'=>'in'),
            array('title'=>'私海/公海','name'=>'lost','type'=>'combobox','url'=>'/'.config('HT_FILE_NAME').'/common/sysCombo/config/search_lost','search_type'=>'in')

        );

        $buttons = [
            ['title'=>'快速搜索','f'=>'quick_search()','icon'=>'fa fa-search'],
            ['title'=>'清空','f'=>'reset_form()','icon'=>'fa fa-trash-o'],
            ['title'=>'自定义列','f'=>'OpenGetShowColumnDlg()','icon'=>'fa fa-eye'],
            ['title'=>'导出数据','f'=>"ExportExcel('member',1,'0%')",'icon'=>'fa fa-mail-forward']
        ];

        if(Model('AuthRule')->authCheck('system/show_pk',$this->uid)){
            $first = array('title'=>'ID','name'=>'id_sale_chance','type'=>'textbox','search_type'=>'=');
            array_unshift($toolbar,$first);
        }
        $right_menu = array(
            array('title'=>'编辑机会','f'=>'edit_chance','icon'=>'fa fa-edit','menu_sep'=>true),
            array('title'=>'删除','f'=>'del','icon'=>'fa fa-minus'),
        );
        $hide_table_list = 'call/index';
        $show_column = array();
        $show_column = db('column')->where("mid=".$this->uid." and list='".$hide_table_list."'")
            ->order('id desc')->find();
        if(empty($show_column)){
            $show_column['id'] = 0;
            $show_column['show_column'] = '';
            $show_column['hide_column'] = '';
        }

        $data = array(
            'controller'=>$this->controller,
            'table'=>'sale_chance',
            'columns'=>$columns,
            'url'=>'/' .$this->ht. '/'.$this->controller.'/list_json/table/'.$table,
            'table_title'=>'全部销售机会列表',
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
            'sortName'=>$pk,
            'sortOrder'=>"desc"
        );
        return view('index/table',$data);
    }
}