<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

    function msgShow($msg){
    	$js="$.messager.show({
						title:'提示',
						msg:'$msg'
					});	";
    	echo "<script language='javascript'>".$js."</script>";exit();
    }
    //登录超时跳转
    function msgShow2($msg){
    	$js="$.messager.show({
						title:'提示',
						msg:'$msg'
					});	";
    	$js .= "setTimeout(\"location='/admin'\",3000)";
    	echo "<script language='javascript'>".$js."</script>";exit();
    }

    function AddYinhao($str){
	    $mystr="'";
	    $arr_str=explode(",",$str);
	    for($i=0;$i<count($arr_str);$i++){
	        $mystr.=$arr_str[$i]."',";
	    }
	    $mystr=substr($mystr,0,strlen($mystr)-1);
	    return $mystr;
    }

    //验证方法
	function regex($value,$rule) {
        $validate = array(
            'require'=> '/.+/',
            'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'url' => '/^http|https:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/',
            'currency' => '/^\d+(\.\d+)?$/',
            'number' => '/^\d+$/',
            'zip' => '/^\d{6}$/',
            'integer' => '/^[-\+]?\d+$/',
            'double' => '/^[-\+]?\d+(\.\d+)?$/',
            'english' => '/^[A-Za-z]+$/',
        	'password'=>'/^\w{5,17}$/',
            'uname' => '/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]+$/u',/* /^[".chr(0xa1)."-".chr(0xff)."A-Za-z0-9_]+$/   GB2312汉字字母数字下划线*/
        );
        // 检查是否有内置的正则表达式
        if(isset($validate[strtolower($rule)]))
            $rule = $validate[strtolower($rule)];
        return preg_match($rule,$value)===1;
    }

	//求2个日期相差的天数
	function daysCha($day1, $day2){
		$second1 = strtotime($day1);  $second2 = strtotime($day2);
		if ($second1 < $second2) {
			$tmp = $second2;
			$second2 = $second1;
			$second1 = $tmp;
		}
		return ($second1 - $second2) / 86400;
	}

	/**
	 * 获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
	 * @return mixed
	 */
	function get_client_ip($type = 0,$adv=false) {
	    $type       =  $type ? 1 : 0;
	    static $ip  =   NULL;
	    if ($ip !== NULL) return $ip[$type];
	    if($adv){
	        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	            $pos    =   array_search('unknown',$arr);
	            if(false !== $pos) unset($arr[$pos]);
	            $ip     =   trim($arr[0]);
	        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
	            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
	        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
	            $ip     =   $_SERVER['REMOTE_ADDR'];
	        }
	    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
	        $ip     =   $_SERVER['REMOTE_ADDR'];
	    }
	    // IP地址合法验证
	    $long = sprintf("%u",ip2long($ip));
	    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
	    return $ip[$type];
	}

	//php 获取url地址不包含路径（域名或ip地址）
	function getServerName(){
 		$ServerName= strtolower($_SERVER['SERVER_NAME']?$_SERVER['SERVER_NAME']:$_SERVER['HTTP_HOST']);
		if( strpos($ServerName,'http://') ){
			return str_replace('http://','',$ServerName);
		}
		return $ServerName;
	}

	// 获得当前的脚本网址(只有路径)
	function GetCurUrl(){
		if(!empty($_SERVER["REQUEST_URI"])){
			$scrtName = $_SERVER["REQUEST_URI"];
			$nowurl = $scrtName;
		}else{
			$scrtName = $_SERVER["PHP_SELF"];

			if(empty($_SERVER["QUERY_STRING"])){
				$nowurl = $scrtName;
			}else{
				$nowurl = $scrtName."?".$_SERVER["QUERY_STRING"];
			}
		}
		return $nowurl;
	}

	// 获得当前的文件名称;
	function GetCurFileName(){
		$url = $_SERVER['PHP_SELF'];
		$filename= substr( $url , strrpos($url , '/')+1 );
		return $filename;
	}

	// 不区分大小写的in_array实现
	function in_array_case($value,$array){
	    return in_array(strtolower($value),array_map('strtolower',$array));
	}

	/**
	 * 字符串截取，支持中文和其他编码
	 * @static
	 * @access public
	 * @param string $str 需要转换的字符串
	 * @param string $start 开始位置
	 * @param string $length 截取长度
	 * @return string
	 */
	function msubstr($str, $start=0, $length) {
		$charset="utf-8";
		$suffix=true;
	    if(function_exists("mb_substr"))
	        $slice = mb_substr($str, $start, $length, $charset);
	    elseif(function_exists('iconv_substr')) {
	        $slice = iconv_substr($str,$start,$length,$charset);
	        if(false === $slice) {
	            $slice = '';
	        }
	    }else{
	        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
	        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
	        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
	        preg_match_all($re[$charset], $str, $match);
	        $slice = join("",array_slice($match[0], $start, $length));
	    }
	    return $suffix ? $slice.'' : $slice;
	}

	/**
	 * 格式化字节大小
	 * @param  number $size      字节数
	 * @param  string $delimiter 数字和单位分隔符
	 * @return string            格式化后的带单位的大小
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	function format_bytes($size, $delimiter = '') {
	    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
	    return round($size, 2) . $delimiter . $units[$i];
	}

	//html代码输出
	function html_out($str){
	    if(function_exists('htmlspecialchars_decode')){
	        $str=htmlspecialchars_decode($str);
	    }else{
	        $str=html_entity_decode($str);
	    }
	    $str = stripslashes($str);
	    return $str;
	}

	function format_date($time){
	    $t=time()-$time;

	    $f=array(
	        '31536000'=>'年',
	        '2592000'=>'个月',
	        '604800'=>'星期',
	        '86400'=>'天',
	        '3600'=>'小时',
	        '60'=>'分钟',
	        '1'=>'秒'
	    );
	    foreach ($f as $k=>$v)    {
	        if (0 !=$c=floor($t/(int)$k)) {
	            return $c.$v.'前';
	        }
    	}
	}

	function ClearHtml($content) {
	   $content = preg_replace("/<a[^>]*>/i", "", $content);
	   $content = preg_replace("/<\/a>/i", "", $content);
	   $content = preg_replace("/<div[^>]*>/i", "", $content);
	   $content = preg_replace("/<\/div>/i", "", $content);
	   $content = preg_replace("/<!--[^>]*-->/i", "", $content);//注释内容
	   $content = preg_replace("/style=.+?['|\"]/i",'',$content);//去除样式
	   $content = preg_replace("/class=.+?['|\"]/i",'',$content);//去除样式
	   $content = preg_replace("/id=.+?['|\"]/i",'',$content);//去除样式
	   $content = preg_replace("/lang=.+?['|\"]/i",'',$content);//去除样式
	   $content = preg_replace("/width=.+?['|\"]/i",'',$content);//去除样式
	   $content = preg_replace("/height=.+?['|\"]/i",'',$content);//去除样式
	   $content = preg_replace("/border=.+?['|\"]/i",'',$content);//去除样式
	   $content = preg_replace("/face=.+?['|\"]/i",'',$content);//去除样式
	   $content = preg_replace("/face=.+?['|\"]/",'',$content);//去除样式只允许小写正则匹配没有带 i 参数
	   return $content;
	}

	function ClearHtml2($content) {
	   $content = preg_replace("/style=.+?['|\"]/i",'',$content);//去除样式
	   $content = preg_replace("/color=.+?['|\"]/i",'',$content);//去除样式
	   $content = preg_replace("/face=.+?['|\"]/i",'',$content);//去除样式
	   $content = preg_replace("/face=.+?['|\"]/",'',$content);//去除样式只允许小写正则匹配没有带 i 参数
	   return $content;
	}

	function get_first_img($content){
		$temp=mt_rand(1,4);
		$pattern="/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";
		preg_match_all($pattern,$content,$matchContent);
		if(isset($matchContent[1][0])){
		    $temp=$matchContent[1][0];
		}else{
		    $temp="./images/random/$temp.jpg";//需要在相应位置放置4张jpg的文件，名称为1，2，3，4
		}
		return $temp;
	}

	//将0,1转化为是否
	function YorN($data){
		switch($data){
			case 0:
				$data = '<font color="green">√</font>';
				break;
			case 1:
				$data = '<font color="red">×</font>';
				break;
		}
		return $data;
	}

	//去除图片中的style
	function del_img_style($content){
		//去掉图片宽度
		$search = '/(<img.*?)width:(["\'])?.*?(?(2)\2|\s)([^>]+>)/is';
		//去掉图片高度
		$search1 = '/(<img.*?)height:(["\'])?.*?(?(2)\2|\s)([^>]+>)/is';
		$content = preg_replace($search,'$1$3',$content);
		$content = preg_replace($search1,'$1$3',$content);
		return $content;
	}

		/**
	 * 关键字提取方法
	 *
	 * @param $title string
	 *         进行分词的标题
	 * @param $content string
	 *         进行分词的内容
	 * @return array 得到的关键词数组
	 */
	 function getKeywords($title = "", $content = "") {
	    if (empty ( $title )) {
	        return array ();
	    }
	    if (empty ( $content )) {
	        return array ();
	    }
	    $data = $title . $title . $title . $content; // 为了增加title的权重，这里连接3次

	    //这个地方写上phpanalysis对应放置路径
	    require_once './public/phpanalysis.class.php';

	    PhpAnalysis::$loadInit = false;
	    $pa = new PhpAnalysis ( 'utf-8', 'utf-8', false );
	    $pa->LoadDict ();
	    $pa->SetSource ( $data );
	    $pa->StartAnalysis ( true );

	    $tags = $pa->GetFinallyKeywords ( 3 ); // 获取文章中的五个关键字

	    $tagsArr = explode ( ",", $tags );
	    return $tagsArr;//返回关键字数组
	}
	//二维数组转字符窜
	function arr2str ($arr){
	    foreach ($arr as $v){
	        $v = join("|",$v); //可以用implode将一维数组转换为用|连接的字符串
	        $temp[] = $v;
	    }
	    $t="";
	    foreach($temp as $v){
	        $t.=$v.",";
	    }
	    $t=substr($t,0,-1);
	    return $t;
	}
	//字符窜转二维数组
	function s2a($str){
		$arr=explode(",",$str);
		$result=array();
		foreach($arr as $data){
			trim($data) && $result[]=explode("|",$data); //首先要检查$data是否为空
	 	}
	 	return $result;
	}

	function jsonReturn($status=0,$msg='',$data=''){
    	$result = array(
    		'status' => $status,
    		'msg' => $msg,
    		'data' => $data
    	);
    	echo json_encode($result);exit();
    }

	/**
 　　* 下划线转驼峰
 　　* step1.原字符串转小写,原字符串中的分隔符用空格替换,在字符串开头加上分隔符
 　　* step2.将字符串中每个单词的首字母转换为大写,再去空格,去字符串首部附加的分隔符.
 　　*/
     function camelize($uncamelized_words,$separator='_')
     {
         $uncamelized_words = $separator. str_replace($separator, " ", strtolower($uncamelized_words));
         return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator );
     }

     /**
 　　* 驼峰命名转下划线命名
 　　* 小写和大写紧挨一起的地方,加上分隔符,然后全部转小写
 　　*/
     function uncamelize($camelCaps,$separator='_')
     {
         return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
     }

?>

