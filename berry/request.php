<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Request {

////////////////////////////////////////////////////////////////////////////////

    static function GET($key = '', $check = array()){
        return self::_request('GET', $key, $check);
    }

////////////////////////////////////////////////////////////////////////////////

    static function POST($key = '', $check = array()){
        return self::_request('POST', $key, $check);
    }

////////////////////////////////////////////////////////////////////////////////

    static function PUT($key = '', $check = array()){
        return self::_request('PUT', $key, $check);
    }

////////////////////////////////////////////////////////////////////////////////

    static function DELETE($key = '', $check = array()){
        return self::_request('DELETE', $key, $check);
    }

////////////////////////////////////////////////////////////////////////////////

    static function FILES($key = '', $check = array()){
        $result = array();

        if (self::method() != 'POST')
            throw new Check_Except;

        if (is_array($key))
            list($key, $check) = array('', $key);

        if ($key){
            $len = (strlen($key) + 1);
            
            foreach ($check as $k => $v)
                if (!isset($check[$key.'.'.$k]) and substr($k, 0, $len) != $key.'.'){
                    $check[$key.'.'.$k] = $v;
                    unset($check[$k]);
                }
        }
        
        if ($check and !check::is_valid($check, $_FILES))
            throw new Check_Except($check, '_FILES');        
        
        foreach (arr::flat($_FILES) as $k => $v){
            if (substr_count($k, '.') == 1){
                $result[$k] = $v;
            } else {
                $k = explode('.', $k);
                $second = $k[1];
                $last = array_pop($k);
                
                unset($k[1]);
                array_push($k, $last);
                array_push($k, $second);
                
                $k = join('.', $k);
                $result[$k] = $v;
            } 
            
            if ($key){
                unset($result[$k]);

                if (substr($k, 0, $len) == $key.'.')
                    $result[substr($k, $len)] = $v;
            }
        }
        
        return arr::assoc($result);
    }          

////////////////////////////////////////////////////////////////////////////////

    static function method(){
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

////////////////////////////////////////////////////////////////////////////////

    static function cookie($name = null, $value = null, $expire = 0){
        if (is_array($name))
            extract($name);

        if ($value === null)
            return ($name !== null ? $_COOKIE[str_replace('.', '_', $name)] : $_COOKIE);

        $config = b::config('request.cookie');
        $expire = date::time($expire ? $expire : $config['expire']);
        $path = ($path ? $path : $config['path']);
        $domain = ($domain ? $domain : str::format($config['domain'], array('current' => $_SERVER['SERVER_NAME'])));
        $secure = (isset($secure) ? $secure : $config['secure']);

        return setcookie($name, $value, $expire, $path, $domain, $secure, true);
    }

////////////////////////////////////////////////////////////////////////////////

    static function redirect($location = '', $status = 302){
        if (!$location){
            $location = b::q(0, 0);
        } elseif (!strpos($location, '://')){
            if ($location[0] == '/')
                $location = b::q(0).$location;
            else
                $location = b::q(0, 0).'/'.$location;
        }

        self::status($status);
        header('Location: '.$location);
        exit('
            <script>window.location = "'.$location.'"</script>
            <noscript><meta http-equiv="refresh" content="0;URL='.$location.'" /></noscript>
        ');
    }

////////////////////////////////////////////////////////////////////////////////

    static function status($code){
        $status = (substr(PHP_SAPI, 0, 3) == 'cgi' ? 'Status:' : $_SERVER['SERVER_PROTOCOL']);
        $header = b::config('request.status.'.$code);

        header($status.' '.trim($code.' '.$header));
    }

////////////////////////////////////////////////////////////////////////////////

    static function client_ip(){
        return $_SERVER['REMOTE_ADDR'];
    }

////////////////////////////////////////////////////////////////////////////////

    static function user_agent($cap = false){
        if ($cap)
            return self::_user_agent();

        return str::plain($_SERVER['HTTP_USER_AGENT']);
    }

////////////////////////////////////////////////////////////////////////////////

	static function accept($type = ''){
		$accepts = self::_accept($_SERVER['HTTP_ACCEPT']);
        $category = reset(explode('/', $type));

        if (!$type)
            return $accepts;
        
        return (isset($accepts[$type]) or isset($accepts[$category.'/*']) or isset($accepts['*/*']));
	}

////////////////////////////////////////////////////////////////////////////////

	static function accept_language($language = ''){
		$accepts = self::_accept($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		return ($language ? isset($accepts[$language]) : $accepts);
	}

////////////////////////////////////////////////////////////////////////////////

	static function accept_encoding($encoding = ''){
		$accepts = self::_accept($_SERVER['HTTP_ACCEPT_ENCODING']);
		return ($encoding ? isset($accepts[$encoding]) : $accepts);
	}

////////////////////////////////////////////////////////////////////////////////

	static function headers(){
		if (function_exists('getallheaders'))
		    return getallheaders();

        $result = array();
        
        foreach ($_SERVER as $k => $v)
            if (substr($k, 0, 5) == 'HTTP_'){
                $k = str_replace('_', ' ', strtolower(substr($k, 5)));
                $k = str_replace(' ', '-', ucwords($k));
                $result[$k] = $v;
            }

		return $result;
	}

////////////////////////////////////////////////////////////////////////////////

	static function is_ajax(){
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _request($method, $key = '', $check = array()){
        $method = '_'.$method;

        if (self::method() != substr($method, 1))
            throw new Check_Except;        

        if (!isset($GLOBALS[$method])){
            $fp = fopen('php://input', 'r');
            
            while (!feof($fp))    
                $data .= fread($fp, 1024);

            fclose($fp);
            parse_str($data, $GLOBALS[$method]);
        }

        if (is_array($key))
            list($key, $check) = array('', $key);

        if ($key){
            $data = b::l(strtolower($method).'.'.$key);
            $len = (strlen($key) + 1);

            foreach ($check as $k => $v)
                if (!isset($check[$key.'.'.$k]) and substr($k, 0, $len) != $key.'.'){
                    $check[$key.'.'.$k] = $v;
                    unset($check[$k]);
                }
        } else {
            $data = $GLOBALS[$method];
        }

        if (!$data or ($check and !check::is_valid($check, $GLOBALS[$method])))
            throw new Check_Except($check, $method.($key ? '.'.$key : ''));

        return $data;
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _user_agent(){
        if (!$file = cache::get_path('request/browscap.ini')){
            $fp = fsockopen('browsers.garykeith.com', 80);
            
            fwrite($fp, "GET /stream.asp?Lite_PHP_BrowsCapINI HTTP/1.1\r\n");
            fwrite($fp, "User-Agent: Putoberry/1.0\r\n");
            fwrite($fp, "Host: browsers.garykeith.com\r\n");
            fwrite($fp, "Accept: */*\r\n");
            fwrite($fp, "\r\n");
            
            while (!feof($fp))
                $data .= fgets($fp);

            $data = substr($data, strpos($data, "\r\n\r\n"));
            $file = cache::set($data);
        }
        
        if (!$ini = cache::get('request/browscap.php')){
            $ini = parse_ini_file($file, true, INI_SCANNER_RAW);
        
            foreach ($ini as $k => $v){
                $k = preg_quote($k, '/');
                $k = str_replace('\?', '.', $k);
                $k = str_replace('\*', '.*', $k);
                
                $ini[$k]['browser_name_regex'] = '^'.$k.'$';
            }
        
            cache::set($ini);
        }
        
        foreach ($ini as $k => $v){
            if (preg_match('/^'.$v['browser_name_regex'].'$/i', self::user_agent())){
                $v = array_merge((array)$ini[$v['Parent']], $v);

                unset($v['browser_name_regex']);
                return array_change_key_case($v);
            }
        }        
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _accept($accepts){
        $result = array();
        
        foreach (explode(',', $accepts) as $pos => $accept){
            $accept = explode(';', trim($accept));
            $result[$accept[0]] = array('q' => 1);
            
            for ($i = 1, $c = count($accept); $i < $c; $i++){
                list($k, $v) = explode('=', $accept[$i], 2);                
                $result[$accept[0]][$k] = $v;
            }
        }
        
        uasort($result, create_function('$a, $b', 'return ($a["q"] < $b["q"]);'));
        return $result;
    }

////////////////////////////////////////////////////////////////////////////////
}