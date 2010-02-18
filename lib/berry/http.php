<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class HTTP {

////////////////////////////////////////////////////////////////////////////////

    static function cookie($name = null, $value = null, $expire = 0){        if (is_array($name))
            extract($name);

        if ($value === null)
            return ($name !== null ? $_COOKIE[str_replace('.', '_', $name)] : $_COOKIE);

        $expire = date::time($expire ? $expire : b::config('lib.http.cookie.expire'));
        $path = ($path ? $path : b::config('lib.http.cookie.path'));
        $domain = ($domain ? $domain : str::format(b::config('lib.http.cookie.domain'), array('current' => $_SERVER['SERVER_NAME'])));
        $secure = (isset($secure) ? $secure : b::config('lib.http.cookie.secure'));

        return setcookie($name, $value, $expire, $path, $domain, $secure, true);
    }////////////////////////////////////////////////////////////////////////////////

    static function go($location = '', $status = 303){        if (!$location)
            $location = b::q(0, 0);
        elseif (!strpos($location, '://'))
            $location = b::q(0).'/'.$location;

        self::status($status);
        header('Location: '.$location, true);
        exit('
            <script>window.location = "'.$location.'"</script>
            <noscript><meta http-equiv="refresh" content="0;URL='.$location.'" /></noscript>
        ');
    }

////////////////////////////////////////////////////////////////////////////////

    // http://php.net/manual/ru/function.header.php#60050
    static function status($num){
        static $http = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Large',
            415 => 'Unsupported Media Type',
            416 => 'Requested range not satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out'
        );

        $status = (substr(PHP_SAPI, 0, 3) == 'cgi' ? 'Status:' : $_SERVER['SERVER_PROTOCOL']);
        header($status.' '.$num.' '.$http[$num], true, $num);
    }

////////////////////////////////////////////////////////////////////////////////

    static function ping($url, $params = array()){        $params = array_merge(array(
            'name' => b::config('site.name'),
            'url'  => b::q(0)
        ), $params);
        $url = parse_url($url);
        $response = end(xmlrpc::request(
            $url['host'].($url['port'] ? ':'.$url['port'] : ''),
            $url['path'], 'weblogUpdates.ping',
            array($params['name'], $params['url']), 'Putoberry'
        ));

        if (!is_array($response))
            return array(-1, '');

        return array_values($response);
    }

////////////////////////////////////////////////////////////////////////////////

    static function trackback($url, $params = array()){
        $params = http_build_query(array_merge(array(
            'blog_name' => b::config('site.name'),
            'charset'   => 'utf-8'
        ), $params));

        $url = parse_url($url);
        $fp = fsockopen($url['host'], ($url['port'] ? $url['port'] : 80));

        fwrite($fp,
            'POST '.$url['path'].' HTTP/1.0'."\r\n".
            'Host: '.$url['host']."\r\n".
            'User-Agent: Putoberry'."\r\n".
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8'."\r\n".
            'Content-Length: '.b::len($params)."\r\n\r\n".
            $params
        );

        while (!feof($fp))
            $response .= fread($fp, 128);

        fclose($fp);

        if ($response = str::untag('message', $response))
            return $response[0];
    }

////////////////////////////////////////////////////////////////////////////////

    static function pingback($url){
        $headers = get_headers($url, true);

        if (!$to = $headers['X-Pingback']){
            if (!preg_match('/<link rel="pingback" href="([^"]+)" \/?>/i', file_get_contents($url), $match))
                return;

            $to = $match[1];
        }

        return $to;
    }

////////////////////////////////////////////////////////////////////////////////
}