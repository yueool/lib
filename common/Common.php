<?php
/**
 * 常用函数
 * @version 20190727
 */
namespace yueool\lib\common;

class Common{

    /**
     * 分页
     * @version 20190727
     * @param $dataSum 数组总数
     * @param $dataPerPage 每页数据数量
     * @param $curPageNum
     * @return array
     */
	public static function bypage($dataSum, $dataPerPage, $curPageNum){

        $dataSum = intval($dataSum);//数据总数
        $dataPerPage = intval($dataPerPage);//每页数据
		$curPageNum = intval($curPageNum);//当前页数

		//计算总页数 $pageSum
		if($dataSum <= 0){
			$pageSum = 1;
		}else if($dataSum % $dataPerPage == 0){
			$pageSum = intval($dataSum / $dataPerPage);
		}else{
			$pageSum = intval($dataSum / $dataPerPage) + 1;
		}

		//修正当前页
		if($curPageNum < 1)$curPageNum = 1;
		if($curPageNum > $pageSum)$curPageNum = $pageSum;

		//计算OFFSET
        $offset = ($curPageNum -1) * $dataPerPage;

        //余数
		$remainder = $dataSum % $dataPerPage;
		$curPageDataSum = ($curPageNum == $pageSum && $remainder != 0) ? $remainder : $dataPerPage;

		//计算上一页和下一页
		$prevPageNum = $curPageNum > 1 ? $curPageNum - 1 : 1;
        $nextPageNum = $curPageNum < $pageSum ? $curPageNum + 1 : $pageSum;

		$result = array(
		    'pageSum' => $pageSum,
			'curPageNum' => $curPageNum,
			'prevPageNum' => $prevPageNum,
            'nextPageNum'=> $nextPageNum,
			'dataSum' => $dataSum,
            'dataPerPage' => $dataPerPage,
			'offset' => $offset,
			'curPageDataSum' => $curPageDataSum,
			'limitSql' => ($offset == 0) ? ' LIMIT '.$curPageDataSum : ' LIMIT '.$offset.", ".$curPageDataSum
		);

		return $result;
	}

    /**
     * 分屏
     * @version 20190727
     * @param $dataSum
     * @param $dataPerPage 每页数据数量
     * @param $curPageNum 当前页数
     * @param $dataPerScreen
     * @param $curScreenNum
     * @return array
     */
	public static function byscreen($dataSum, $dataPerPage, $curPageNum, $dataPerScreen, $curScreenNum){
		$pageInfo = self::bypage($dataSum, $dataPerPage, $curPageNum);
		//echo '<pre>';print_r($pageInfo);

		$screen = self::bypage($pageInfo["curPageDataSum"], $dataPerScreen, $curScreenNum);
        //print_r($screen);

        $offset = $pageInfo["offset"] + $screen["offset"];
		return [
		    'curPageNum' => $curPageNum,
			'screenSum' => $screen['pageSum'],
            'curScreenNum'=> $screen['curPageNum'],
            'dataSum' => $dataSum,
            'dataPerPage' => $dataPerPage,
            'offset' => $offset,
            'curPageDataSum' => $pageInfo["curPageDataSum"],
            'dataPerScreen' => $dataPerScreen,
            'curScreenDataSum' => $screen["curPageDataSum"],
            'limitSql' => ($offset == 0) ? ' LIMIT '.$screen["curPageDataSum"] : ' LIMIT '.$offset.", ".$screen["curPageDataSum"]
		];
	}

	
	//取文件后缀名
	static function extname($str){
		return substr(strtolower(strrchr($str,'.')), 1);
	}
	
	//取文件名 比如 ldfjsld.jpg 结果为：ldfjsld
	static function filename($str){
		return substr($str, 0, strrpos($str, "."));	
	}
	
	//URL安全的base64加密（+换-   /换_ 去掉“=”）
	static function enurl64($data) { 
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
	}
	
	//URL安全的base64解密
	static function deurl64($data) { 
		return base64_decode(strtr($data, '-_', '+/'));
	}
	
	//数组转文件
	static function a2f($file, $array){
		file_put_contents($file, "<?php \r"."return ".var_export($array, TRUE).";\r?>");
	}
	
	//得到来访IP
	static function getip(){
		if(!empty($_SERVER["HTTP_CLIENT_IP"])){
		  $cip = $_SERVER["HTTP_CLIENT_IP"];
		}elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
		  $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}elseif(!empty($_SERVER["REMOTE_ADDR"])){
		  $cip = $_SERVER["REMOTE_ADDR"];
		}else{
		  $cip = "无法获取！";
		}
		return $cip;
	}
	
	//截取中文字符串的时候如果出现半个字符会出现乱码，用此函数可避免
	static function zhsubstr_gb2312($str, $len){
		if(strlen($str) <= $len){
			return mb_strcut($str, 0, $len,'gb2312');
		}else{
			return mb_strcut($str, 0, $len-2,'gb2312')."..";
		}
	}
	
	//截取中文字符串的时候如果出现半个字符会出现乱码，用此函数可避免
	static function zhsubstr_utf8($str, $len){
		if(strlen($str) <= $len){
			return mb_strcut($str, 0, $len,'utf-8');
		}else{
			return mb_strcut($str, 0, $len-2,'utf-8')."..";
		}
	}
	
	//对象转数组
	static function object_to_array($obj){
//		$_arr = is_object($obj) ? get_object_vars($obj) : $obj;
//		$_arr = is_object($obj) ? (array)$obj : $obj;
		$_arr = (array)$obj;

		foreach ($_arr as $key => $val){
			$val = (is_array($val) || is_object($val)) ? self::object_to_array($val) : $val;
			$arr[$key] = $val;
		}
		return $arr;
	}
	
	//对象转数组(与上一个的区别是：此方法之保留public的成员变量)
	static function o2a($object){
		$array = array();
		foreach($object as $key => $value){
			$array[$key] = (is_array($value) || is_object($value)) ? self::o2a($value) : $value;
		}
		return $array;
	}
	
	/**
	 * 支持私有属性的对象转数组，同时去掉命名空间
	 * 特殊说明：
	 * 想保留私有变量转数组只能$_arr = (array)$obj;注意这步转完，如果对象有私有变量，或者有命名空间，那么KEY（键）并非
	 * 正常的字符串，里边有类似 \0 等字符。将KEY（键）用addslashes()后即可现形，或者将数组json_encode()也可以现形,将其
	 * 过滤掉才行，如果不处理后果是无法读取，例如 $arr["aa"]，如果aa是私有变量就读不出来，或者转成JSON也会出现多余字符
	 * @author YueYa
	 * @param $obj
	 * @return mixed
	 */
	public static function o2a_ns($obj){
		$_arr = (array)$obj;//注意这步转完，如果对象有私有变量，那么KEY并非正常的字符串，里边有类似 \0 等字符
		foreach ($_arr as $key => $val){
			$key = addslashes($key);//用这步可让\0等特殊字符现形
			if(strpos($key, "\\") !== false){//如果含有反斜杠（说明有命名空间或有私有变量，需要处理下）
				$key = strrchr($key, "\\0");//取\0及以后的字符串，命名空间或私有变量都是以它为边界
				$key = trim(str_replace("\\0", "", $key));//去掉\0
			}
			$val = (is_array($val) || is_object($val)) ? self::o2a_ns($val) : $val;//递归
			$arr[$key] = $val;
		}
		return $arr;
	}
	
	// 判断是 EMAIL
	public static function isEmail($logincode){
		if(strlen($logincode) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $logincode)){
			return true;	
		}else {
			return false;	
		}
	}
	
	// 判断是 手机号
	public static function isMobile($logincode){
		if(preg_match("/^13[0-9]{9}$|15[0-9]{9}$|18[0-9]{9}$/", $logincode)){
			return true;
		}else {
			return false;	
		}
	}

	//判断是否是IP
	public static function isIp($ipaddres) {
		$preg="/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
		if(preg_match($preg,$ipaddres))return true;
		return false;
	}
	
	public static function curlget($url){
		$ch = curl_init();  
		curl_setopt($ch, CURLOPT_URL, $url);  
		curl_setopt($ch, CURLOPT_HEADER, false);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');  
		  
		$res = curl_exec($ch);  
		$rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE);   
		curl_close($ch) ;  
		//echo $res;
		return $res;
	}
	
	static function curlpost($url, $params){
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array("content-type: application/x-www-form-urlencoded; charset=UTF-8"));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);//设置为POST方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	/**
	 * 这个是带HEADER的CURLPOST
	 * @param $url
	 * @param $data
	 * @param array $header 例如：$header=[]; $header[0] = "Authorization:".$Authorization;//注意用冒号分隔
	 * @param int $curl_port
	 * @return mixed|string
	 */
	public static function curl_post_header($url, $data, $header=[], $curl_port = 80){
		$curl = curl_init();                                                // 启动一个CURL会话
		curl_setopt($curl, CURLOPT_URL, $url);                                // 要访问的地址
		curl_setopt($curl, CURLOPT_PORT, $curl_port);							// 端口
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);                        // 对认证证书来源的检查
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);                        // 从证书中检查SSL加密算法是否存在
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
		// curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);                        // 使用自动跳转
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1);                            // 自动设置Referer

		if(is_array($header) && count($header)>0){
			curl_setopt ($curl, CURLOPT_HTTPHEADER, $header );
		}

		curl_setopt($curl, CURLOPT_POST, 1);                                // 发送一个常规的Post请求
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);                        // Post提交的数据包
		// curl_setopt($curl, CURLOPT_COOKIEFILE, $GLOBALS['cookie_file']);    // 读取上面所储存的Cookie信息
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);                            // 设置超时限制防止死循环
		curl_setopt($curl, CURLOPT_HEADER, 0);                                // 显示返回的Header区域内容
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);                        // 获取的信息以文件流的形式返回

		$temp_data = curl_exec($curl);                                     // 执行操作
		if (curl_errno($curl)) {
			// echo 'Errno'.curl_error($curl);                                // 返回curl错误信息
			return 'error';
		}
		curl_close($curl);                                                    // 关键CURL会话
		return $temp_data;                                                    // 返回临时数据
	}
	
	//CURLPOST https版
	static function httpscurl($url, $params, $timeout = 30){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//严格认证
		curl_setopt($ch, CURLOPT_HEADER, 0 ); // 过滤HTTP头
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($ch,CURLOPT_POST,true); // post传输数据
		curl_setopt($ch,CURLOPT_POSTFIELDS, $params);// post传输数据
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$responseText = curl_exec($ch);
		curl_close($ch);
		return $responseText;
	}

	static function u2g($str){
		return iconv("utf-8", "gbk", $str);	
	}
	
	//防注入过滤
	public static function filter($str, $type = 0){
		switch($type){
			case 1 : return preg_replace("/[\"\\\']|(count)|(%20)|(char)/i", "", trim(addslashes($str)));//过滤掉'"\ 只去除两边空格
			default : return preg_replace("/[\"\\\']|( )|(count)|(%20)|(char)/i", "", trim(addslashes($str)));//过滤掉'"\ 去除所有空格
		}
	}
	
	/**
	 * 数组转文件
	 * @param string $file 文件名 
	 * @param array $array 数组 
	 */
	static function mkarrfile($file, $array){
		return file_put_contents($file, "<?php \nreturn ".var_export($array, true).";\n?>");
	}
	
	/**
	 * 对象转文件
	 * @param string $file 文件名 
	 * @param object $obj 数组 
	 */
	static function mkobjfile($file, $obj){
		return file_put_contents($file, "<?php return '".serialize($obj)."';?>");
	}

	/**
	 * 删除有KEY数组中指定KEY的元素
	 */
	static function array_remove(&$data, $key){
		if(array_key_exists($key, $data)){
			$keys = array_keys($data);
			$index = array_search($key, $keys);
			if($index !== FALSE){
				array_splice($data, $index, 1);
			}
		}
	}
	

    //IP转数字
    public static function ipton($ip){
        $ip_arr=explode('.',$ip);//分隔ip段
        $ipstr = "";
        foreach ($ip_arr as $value){
            $iphex=dechex($value);//将每段ip转换成16进制
            if(strlen($iphex)<2){//255的16进制表示是ff，所以每段ip的16进制长度不会超过2
                $iphex='0'.$iphex;//如果转换后的16进制数长度小于2，在其前面加一个0
                //没有长度为2，且第一位是0的16进制表示，这是为了在将数字转换成ip时，好处理
            }
            $ipstr.=$iphex;//将四段IP的16进制数连接起来，得到一个16进制字符串，长度为8
        }
        return hexdec($ipstr);//将16进制字符串转换成10进制，得到ip的数字表示
    }

    //数字转IP
    public static function ntoip($n){
        $iphex=dechex($n);//将10进制数字转换成16进制
        $len=strlen($iphex);//得到16进制字符串的长度
        if(strlen($iphex)<8){
            $iphex='0'.$iphex;//如果长度小于8，在最前面加0
            $len=strlen($iphex); //重新得到16进制字符串的长度
        }
        //这是因为ipton函数得到的16进制字符串，如果第一位为0，在转换成数字后，是不会显示的
        //所以，如果长度小于8，肯定要把第一位的0加上去
        //为什么一定是第一位的0呢，因为在ipton函数中，后面各段加的'0'都在中间，转换成数字后，不会消失
        for($i=0,$j=0;$j<$len;$i=$i+1,$j=$j+2){//循环截取16进制字符串，每次截取2个长度
            $ippart=substr($iphex,$j,2);//得到每段IP所对应的16进制数
            $fipart=substr($ippart,0,1);//截取16进制数的第一位
            if($fipart=='0'){//如果第一位为0，说明原数只有1位
                $ippart=substr($ippart,1,1);//将0截取掉
            }
            $ip[]=hexdec($ippart);//将每段16进制数转换成对应的10进制数，即IP各段的值
        }
        return implode('.', $ip);//连接各段，返回原IP值
    }
    
    /**
     * POST形式的file_get_contents 相当于POSTMAN 里的 BODY下x-www-form-urlencoded方式请求
     * 此方法对调用SERVLET效果非常好，都是UTF-8的时候不用转编码，而且不需要URLENCODE了
     * @param $url
     * @param $params
     * @return bool|string
     */
    public static function file_get_contents_post($url, $params){
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => http_build_query($params),
                'timeout' => 20
            ]
        ]);
        return file_get_contents($url, false, $context);
    }
	
}

?>