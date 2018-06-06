<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/24
 * Time: 12:01
 */

namespace app\admin\model;
use think\Model;
use Db;
use Config;

class AuthRule extends Model
{
    public function authCheck($name, $uid, $type=1, $mode='url', $relation='or'){
        if(!in_array($uid,config('ADMINISTRATOR'))){
            return $this->check($name, $uid, $type, $mode, $relation) ? true : false;
        }else{
            return true;
        }
    }

    /**
     * 检查权限
     *
     * @param name string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param uid  int           认证用户的id
     * @param string mode        执行check的模式
     * @param relation string    如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * @return boolean           通过验证返回true;失败返回false
     */
    public function check($name, $uid, $type=1, $mode='url', $relation='or') {

        $authList = $this->getAuthList($uid,$type); //获取用户需要验证的所有有效规则列表
        if($authList == 'ALL'){
            return true;
        }
        //print_r($authList);exit();
        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = array($name);
            }
        }
        $list = array(); //保存验证通过的规则名
        if ($mode=='url') {
            $REQUEST = unserialize( strtolower(serialize($_REQUEST)) );
        }
        foreach ( $authList as $auth ) {
            $query = preg_replace('/^.+\?/U','',$auth);
            if ($mode=='url' && $query!=$auth ) {
                parse_str($query,$param); //解析规则中的param
                $intersect = array_intersect_assoc($REQUEST,$param);
                $auth = preg_replace('/\?.*$/U','',$auth);
                if ( in_array($auth,$name) && $intersect==$param ) {  //如果节点相符且url参数满足
                    $list[] = $auth ;
                }
            }else if (in_array($auth , $name)){
                $list[] = $auth ;
            }
        }
        if ($relation == 'or' and !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ($relation == 'and' and empty($diff)) {
            return true;
        }
        return false;
    }

    /**
     * 根据用户id获取用户组,返回值为数组
     * @param  uid int     用户id
     * @return array       用户所属的用户组 array(
     *                                         array('uid'=>'用户id','group_id'=>'用户组id','title'=>'用户组名称','rules'=>'用户组拥有的规则id,多个,号隔开'),
     *                                         ...)
     */
    function getGroups($uid){
        $res = Db::name('member')
            ->alias('m')
            ->join('member_role r','m.role_id = r.id')
            ->where("m.id='$uid' and r.status='0'")
            ->field('rules')
            ->select();
        return $res;
    }

    /**
     * 获得权限列表
     * @param integer $uid  用户id
     * @param integer $type
     * @return string $authList
     */
    function getAuthList($uid,$type=1) {

        static $_authList = array(); //保存用户验证通过的权限列表
        //读取用户所属用户组
        $groups = $this->getGroups($uid);
        $ids = array();//保存用户所属用户组设置的所有权限规则id
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);
        if (empty($ids)) {
            $_authList[$uid] = array();
            return array();
        }
        $ids=implode(',',$ids);
        if($ids == 'ALL'){
            return 'ALL';
        }

        //读取用户组所有权限规则
        $rules = Db::name('auth_rule')
                ->where("id in ($ids) and type=$type and status=0")
                ->select();

        //循环规则，判断结果。
        $authList = array();   //
        foreach ($rules as $rule) {
            //只要存在就记录
            $authList[] = strtolower($rule['name']);
        }
        $_authList[$uid] = $authList;
        //规则列表结果保存到session

        return array_unique($authList);
    }
}