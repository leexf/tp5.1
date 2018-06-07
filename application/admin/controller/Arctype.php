<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/7
 * Time: 16:49
 */
namespace app\admin\controller;

class Arctype extends Base
{
    public function index(){
        $table = 'arctype';
        $columns = [
            ['field'=>'','title'=>'','width'=>0,'checkbox'=>true],
            ['field'=>'id','title'=>'ID','width'=>50,'align'=>'center','sortable'=>'true','hidden'=>'true'],
            ['field'=>'name','title'=>'分类名称','width'=>120],
            ['field'=>'seo_title','title'=>'SEO标题','width'=>120,'align'=>'center'],
            ['field'=>'keywords','title'=>'关键词','width'=>120,'align'=>'center'],
            ['field'=>'desc','title'=>'栏目描述','width'=>200,'align'=>'center'],
            ['field'=>'headimg','title'=>'分类封面','width'=>120,'align'=>'center'],
            ['field'=>'sort','title'=>'排序','width'=>60,'align'=>'center','sortable'=>'true'],
            ['field'=>'status','title'=>'状态','width'=>100,'align'=>'center'],
        ];
        $right_menu = [
            ['title'=>'编辑','f'=>'edit(600,500)','icon'=>'fa fa-edit','menu_sep'=>true],
            ['title'=>'删除','f'=>'del()','icon'=>'fa fa-minus'],
        ];
        $buttons = [
            ['title'=>'添加','f'=>'add(600,500)','icon'=>'fa fa-plus']
        ];
        $data = [
            'fit'=>true,//自适应高度
            'idField'=>'id',
            'treeField'=>'name',
            'table_title'=>'文章分类列表',
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
            $level = db('arctype')->field('level')->where('id',$post['pid'])->find();
            $post['level'] = $level['level']+1;
        }
        $is_exist_name = db('arctype')->where("name='".$post['name']."'")->count();
        if($is_exist_name>0){
            echo "该分类已经存在！";exit();
        }
        unset($post['upfile']);
        return $post;
    }

    //修改之前
    function _before_edit($post){
        if($post['pid'] == 0){
            $post['level'] = 0;
        }else{
            $level = db('arctype')->field('level')->where('id',$post['pid'])->find();
            $post['level'] = $level['level']+1;
        }
        $is_exist_name = db('arctype')->where("name='".$post['name']."' and id!=".$post['id'])->count();
        if($is_exist_name>0){
            echo "该分类已经存在！";exit();
        }
        unset($post['upfile']);
        return $post;
    }

    //删除之前，找出子权限，全部删除
    public function _before_del($post){
        $id = $post['id'];
        $id_arr = explode(',',$id);
        foreach($id_arr as $k=>$v){
            $table = 'arctype';
            $r = db($table)->where('id',$v)->field('level')->find();
            $level = $r['level'];
            $children = Model('MemberSection')->getChild($table,$v,$level+1);
            foreach ($children as $kk=>$vv){
                $post['id'] .= ','.$vv['id'];
            }
        }
        return $post;
    }

    public function saveHeadImg(){
        $file = request()->file('upfile');
        if($file != ""){
            $info = $file->move('uploads');
            if ($info) {
                $res['info'] = true;
                $res['name'] =  $info->getFilename();
                $res['headimg'] =  '/uploads/' . $info->getSaveName();
                $res['headimg'] = str_replace('\\', '/', $res['headimg']);
            } else {
                $res['info'] = false;
            }
            return $res;
        }
    }

    public function _before_list($list){
        foreach($list as $k=>$v){
            if(!empty($v['headimg'])){
                $v['headimg'] = '<a data-fancybox="arcTypeImg"  href="'.$v['headimg'].'" data-caption="'.$v['name'].'">
                <img style="padding:5px 0 5px 0" src="'.$v['headimg'].'" height="60px" /></a>';
            }
            $list[$k] = $v;
        }
        return $list;
    }

}