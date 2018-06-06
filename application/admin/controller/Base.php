<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/24
 * Time: 9:31
 */

namespace app\admin\controller;
use Config;
use Db;
use Session;
use Request;
use PHPExcel;
use PHPExcel_IOFactory;
use think\Controller;

class Base extends Controller
{
    public function initialize(){
        $this->uid = Session::get('uid');
        $this->url = Request::url();
        $this->controller = uncamelize(request()->controller());

        if( $this->uid == null){
            $this->redirect('/admin/index/login');
        }
        //$this->checkAuth();
        Model('Common')->myConfig();
        $this->ht = config('HT_FILE_NAME');
    }

    //权限检查
    public function checkAuth($url=''){
        $uid = Session::get('uid');
        if($uid <= 0){
            msgShow2('登录超时,请重新登录');
        }
        if($url == ''){
            $url = Request::url();
        }

        if(!Model('AuthRule')->authCheck($url,$uid)){
            $this->add_log("很抱歉,此项操作您没有权限！",'NO_AUTH');
            msgShow('很抱歉,此项操作您没有权限！');
        }
    }

    //添加日志保存
    public function add_log($msg,$opera_type=''){
        $username = Session::get('username');
        $log['ip'] = get_client_ip();
        $log['addtime'] = time();
        $log['mid'] = Session::get('uid');
        $log['username'] = $username;
        $log['content'] = $msg;
        $log['url'] = Request::url();
        $log['os']=$_SERVER['HTTP_USER_AGENT'];
        $log['opera_type'] = $opera_type;

        Db::name('log')->insert($log);
    }

    public function index(){

    }

    //显示添加页面
    public function add($tb){
        $data=array(
            'tb'=>$tb
        );
        if (method_exists($this, '_before_show_add')) {
            $data = $this->_before_show_add($data);
        }

        return view( $this->controller.'/add',$data);
    }

    //公共保存添加方法
    public function save_add($table){
        $post = Request::post();
        if (method_exists($this, '_before_insert')) {
            $post = $this->_before_insert($post);
        }
        $data = array();
        $data['id'] = db($table)->insertGetId($post);
        if($data['id'] > 0){
            if (method_exists($this, '_after_insert')) {
                $this->_after_insert($post);
            }
            $this->add_log("添加成功".$data['id']."！",'C');
            return "ok";
        }else{
            return "添加失败";
        }
    }

    //显示修改页面
    public function edit($tb,$id){
        $pk = db($tb)->getPk();
        $data = db($tb)->where($pk,$id)->find();
        $data['tb'] = $tb;
        if (method_exists($this, '_befor_show_edit')) {
            $data = $this->_befor_show_edit($data);
        }
        return view( $this->controller.'/edit',$data);
    }

    //公共修改方法
    public function save_edit($table){
        $post = Request::post();
        if (method_exists($this, '_before_edit')) {
            $post = $this->_before_edit($post);
        }
        $id = $post['id'];
        db($table)->update($post);
        if (method_exists($this, '_after_edit')) {
            $this->_after_edit($post);
        }
        $this->add_log("修改成功".$id."！",'U');
        return "ok";
    }

    public function list_json($table){
        $map = " 1=1 ";
        $post = Request::post();
        if (method_exists($this, '_map')) {
            $map = $this->_map($map);
        }
        $list = Model('common')->list_json($table,$map,$post);
        //print_r($list);exit();
        if (method_exists($this, '_before_list')) {
            $list = $this->_before_list($list);
        }
        return json($list);
    }

    public function del($table,$isTure=0){
        $post = Request::post();
        if (method_exists($this, '_before_del')) {
            $post = $this->_before_del($post);
        }
        $id = $post['id'];
        $id_arr = explode(',',$id);
        if($isTure == 0){
            db($table)->delete($id_arr);
        }else{
            $set = [$isTure=>0];
            db($table)->where("id in ($id)")->update($set);
        }

        if (method_exists($this, '_after_del')) {
            $post = $this->_after_del($post);
        }
        if($isTure == 0) {
            $this->add_log("删除成功" . $id . "！", 'D');
        }else{
            $this->add_log("删除成功" . $id . "！", 'FD');
        }
        return "ok";

    }

    //更改状态
    function change_status(){
        $post = Request::post();
        $id = $post['id'];
        $status = $post['status'];
        $table = $post['table'];
        $set = array(
            'status'=>$status
        );
        $pk = db($table)->getPk();
        db($table)->where($pk,$id)->update($set);

        if (method_exists($this, '_after_change')) {
            $this->_after_change($post);
        }
        if($status == 0){
            $this->add_log("启用成功".$id."！",'CH_STATE');
        }else{
            $this->add_log("取消成功".$id."！",'CH_STATE');
        }
        return "ok";

    }

    public function gridTreeJson($table){
        $map = " 1=1 ";
        $post = Request::post();
        $pid = isset($post['id']) ? $post['id'] : 0;
        if (method_exists($this, '_map')) {
            $map = $this->_map($map);
        }
        $list = Model('common')->gridTreeJson($table,$pid,$map);
        if (method_exists($this, '_before_tree_json')) {
            $list = $this->_before_tree_json($list);
        }

        return json($list);
    }

    public function exportExcel(){
        $map = " 1=1 ";
        //查看是否有导出权限
        if(!Model('AuthRule')->authCheck($this->controller.'/export_excel',$this->uid)){
            $res = ['status'=>0,'msg' =>'您没有该权限！'];
            return json($res);
        }
        if (method_exists($this, '_map')) {
            $map = $this->_map($map);
        }
        $post = Request::post();

        $table = $post['table'];
        unset($post['table']);

        $page = $post['page'];
        $page = $page<1?1:$page;
        $rows = $post['rows'];
        $total = $rows;
        if($rows>500){
            $post['rows'] = 500;
        }

        $from = isset($post['from']) ? $post['from'] : '';
        switch($from){
            case 'fp_yestoday':
                $yestoday1 = strtotime(date('Y-m-d'))-86400;
                $yestoday2 = strtotime(date('Y-m-d'));
                $map .= " and lost=0 and id_sale_chance in (select chance_id from ".$this->db->dbprefix('log')." where opera_type='P' and addtime>'$yestoday1' and addtime<'$yestoday2' ) ";
                $list = Model('common')->list_json($table,$map,$post);
                break;
            default:
                $list = Model('common')->list_json($table,$map,$post);
                break;
        }

        if (method_exists($this, '_before_list')) {
            $list = $this->_before_list($list);
        }
        $data = $list['rows'];

        //当导出的数据大于500条时
        if($total>500){
            $realPage = ceil($total/500);
        }else{
            $realPage = $page;
        }

        if(empty($data)){
            jsonReturn(0,'没有数据！');
        }
        $excel_name = $post['excelname'];
        $dataindex_str = $post['dataindex_str'];
        if($dataindex_str == ''){
            jsonReturn(0,'参数错误！');
        }
        $dataindex= explode(',', $dataindex_str);
        $dataindex= array_filter($dataindex);

        $dataindex_arr= array();
        foreach ($dataindex as $k_di=>$v_di){
            $v_di= explode('|', $v_di);
            $di_temp[]= array($v_di[0],$v_di[1]);
        }
        krsort($di_temp);
        foreach ($di_temp as $k_di=>$v_di){
            $dataindex_arr[$v_di[0]]=$v_di[1];
        }

        $result = $data;
        if (method_exists($this, '_before_export')) {
            $result = $this->_before_export($result);
        }
        $outdata= array();
        foreach ($result as $k=>$v){
            foreach ($v as $key=>$val){
                if (array_key_exists($key,$dataindex_arr)){
                    $outdata[$k][$key]=$val;
                    $head_arr[$key]= $dataindex_arr[$key];
                }
            }
        }

        $sheetName = $excel_name;//excel名称
        $date = date("Y_m_d_H_i_s",time());
        if(!strpos($sheetName,'.xls')){
            $sheetName .= "_{$date}.xls";
        }

        $root = "./static/download/excel/";//文件保存路径
        $safemode = ini_get('safe_mode');
        if( $safemode ){
            ini_set("safe_mode",false);
        }
        if($page == 1){
            $dataName = $this->xlsout($sheetName,$head_arr,$outdata,$root);
        }else{
            $dataName = $this->xlsout($sheetName,$head_arr,$outdata,$root,1);
        }
        if( $safemode ){
            ini_set("safe_mode",true);
        }
        //$dataName = iconv("gb2312","utf-8", $dataName);
        $rt = array();
        $rt['file'] = $dataName;
        //默认完成，不分步
        $rt['finish'] = 1;
        if($total>500){
            if($page < $realPage){
                //未完成，继续分步
                $rt['finish'] = 0;
                $rt['nextpage'] = $page+1;
                $rt['progress'] = round((($page*500/$total)*100),2).'%';
            }
        }else{
            $rt['progress'] = '100%';
        }

        if($dataName!=""){
            if($rt['finish'] == 1){
                $this->add_log("导出数据成功,共".$total."条！",'EXP');
            }
            jsonReturn(1,'导出成功！',$rt);
        }else{
            jsonReturn(0,'导出失败！');
        }

    }

    //ajax回调下载excel
    public function download_excel($filename){
        header("Content-type:text/html;charset=utf-8");
        if (!$filename){echo "错误：无效的数据文件。";exit;}
        $filename = urldecode($filename);
        //$filename = 'file_';
        $filename = iconv("utf-8", "gb2312", $filename);
        $rootpath="./static/download/excel/";
        $filename_local= $rootpath.$filename;
        //echo $filename_local;exit();
        if (!file_exists($filename_local)){echo "错误：数据文件不存在。";exit;}

        ob_end_clean();//清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        readfile($filename_local);//将内容输出，以便下载。
        unlink($filename_local);
        exit();
    }

    //导出Excel
    public function xlsout($filename='数据表',$headArr,$list,$root=NULL,$start=0){
        $file = $this->getExcel($filename,$headArr,$list,$root,$start);
        if($root!=NULL){
            return $file;
        }
    }

    public function getExcel($fileName,$headArr,$data,$root,$start){
        //对数据进行检验
        if(empty($data) || !is_array($data)){
            die("data must be a array");
        }
        //检查文件名
        if(empty($fileName)){
            exit;
        }
        //是否是第一步
        if($start == 0){
            //创建PHPExcel对象，注意，不能少了\
            $objPHPExcel = new \PHPExcel();
            $objProps = $objPHPExcel->getProperties();
            //设置表头
            $key = ord("A");
            foreach($headArr as $v){
                $colum = chr($key);
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum.'1', $v);
                $key += 1;
            }
            $column = 2;
            $objActSheet = $objPHPExcel->getActiveSheet();

            //设置为文本格式
            foreach($data as $key => $rows){ //行写入
                $span = ord("A");
                foreach($rows as $keyName=>$value){// 列写入
                    $j = chr($span);
                    $objActSheet->setCellValueExplicit($j.$column, $value);
                    $span++;
                }
                $column++;
            }
            //重命名表
            // $objPHPExcel->getActiveSheet()->setTitle('test');
            //设置活动单指数到第一个表,所以Excel打开这是第一个表
            $objPHPExcel->setActiveSheetIndex(0);
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save($root.$fileName);
        }else{
            $PHPExcel = new \PHPExcel();
            $PHPReader = new \PHPExcel_Reader_Excel5();
            $PHPExcel = $PHPReader->load($root.$fileName);

            //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
            $currentSheet = $PHPExcel->getSheet(0);
            //获取总行数
            $allRow = $currentSheet->getHighestRow();
            //print_r($allRow);exit();
            $column = $allRow+1;
            $PHPExcel->setActiveSheetIndex(0);

            $objActSheet = $PHPExcel->getActiveSheet();
            foreach($data as $key => $val){ //行写入
                $span = 0;
                foreach($val as $keyName=>$value){// 列写入
                    $j = \PHPExcel_Cell::stringFromColumnIndex($span);
                    $objActSheet->setCellValue($j.$column, $value);
                    $span++;
                }
                $column++;
            }

            $PHPExcel->setActiveSheetIndex(0);
            $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel5');
            $objWriter->save($root.$fileName);
        }
        $fileName = iconv("utf-8", "gb2312", $fileName);
        if ($root==NULL){
            ob_end_clean();//清除缓冲区,避免乱码
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Disposition: attachment;filename=\"$fileName\"");
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output'); //文件通过浏览器下载
        }else{
            return $fileName;
        }
        exit;
    }
}
