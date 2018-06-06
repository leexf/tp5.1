<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/28
 * Time: 16:32
 */
namespace app\admin\controller;
use Config;
use Db;
use Session;
use Request;
use app\admin\model\AuthRule;


use think\Controller;

class Common extends Controller
{
    public function sysCombo($config){
        $res = Model('Common')->sys_config_to_combo($config);
        return json($res);
    }

    //部门下拉列表数据源
    public function member_section_combotree(){
        $section = Model('MemberSection')->getSectionTreeList();
        return json($section);
    }

    public function member_section_combotree_top(){
        $section = Model('MemberSection')->getSectionTreeList();
        array_unshift($section,['id'=>0,'text'=>'顶级部门']);
        return json($section);
    }

    //菜单下拉列表数据源
    public function menu_combo(){
        $res = Model('Common')->getTreeList(0,0,'menu');
        $top=array(
            'id'=>'0',
            'text'=>'顶级菜单'
        );
        array_unshift($res,$top);
        echo json_encode($res);
    }

    public function auth_rule_combo(){
        $res = Model('Common')->getTreeList(0,0,'auth_rule');
        $top=array(
            'id'=>'0',
            'text'=>'顶级权限'
        );
        array_unshift($res,$top);
        echo json_encode($res);
    }

    //获取角色下拉数据(combobox用)
    public function member_role_combo(){
        $array = db('member_role')->where('status',0)->order('section_id ',' asc')->select();
        $new=array();
        foreach($array as $k=>$v){
            $new[$k]=array(
                'id'=>$v['id'],
                'text'=>$v['role_name'],
            );
        }
        return json($new);
    }

    //自动获取最大sort并且+1
    public function autoSort(){
        $post = Request::post();
        $field = $post['field'];
        $table = $post['table'];
        $id = $post['id'];
        if($table == '' || $field == '' || $id == 0){
            exit('0');
        }
        $sort = Model('common')->get_max_sort($table,$field,$id);
        return $sort;
    }
}