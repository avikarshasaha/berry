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

    function cookie($name = null, $value = null, $time = '+1 hour'){
        if ($name !== null and $value !== null)
            return setcookie($name, $value, date::time($time), '/', '', false, true);
        elseif ($name !== null)
            return $_COOKIE[str_replace('.', '_', $name)];
        else
            return $_COOKIE;
    }////////////////////////////////////////////////////////////////////////////////

    function go($location = '', $status = 303){        if (!$location)
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
    function status($num, $replace = true){
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

        $status = (substr(php_sapi_name(), 0, 3) == 'cgi' ? 'Status:' : $_SERVER['SERVER_PROTOCOL']);
        return header($status.' '.$num.' '.$http[$num], $replace);
    }

////////////////////////////////////////////////////////////////////////////////

    function ping($urls){
        foreach ((array)$urls as $url)
            if ($ping = parse_url($url))
                $result[$url] = xmlrpc::request(
                    $ping['host'].($ping['port'] ? ':'.$ping['port'] : ''),
                    $ping['path'], 'weblogUpdates.ping',
                    array(b::config('site.name'), b::q(0)), 'Putoberry'
                );

        return ($result ? $result : array());
    }

////////////////////////////////////////////////////////////////////////////////

    function trackback($urls, $params = array()){
        $params = http_build_query(array_merge(array(
            'blog_name' => b::config('site.name'),
            'charset'   => 'utf-8'
        ), $params));

        foreach ((array)$urls as $url)
            if ($ping = parse_url($url)){
                $fp = fsockopen($ping['host'], ($ping['port'] ? $ping['port'] : 80));

                fwrite($fp,
                    'POST '.$ping['path'].' HTTP/1.0'."\r\n".
                    'Host: '.$ping['host']."\r\n".
                    'User-Agent: Putoberry'."\r\n".
                    'Content-Type: application/x-www-form-urlencoded; charset=utf-8'."\r\n".
                    'Content-Length: '.b::len($params)."\r\n\r\n".
                    $params
                );

                while (!feof($fp))
                    $response .= fread($fp, 128);

                fclose($fp);

                if ($response = str::untag('message', $response))
                    $result[$url] = $response[0];
            }

        return ($result ? $result : array());
    }

////////////////////////////////////////////////////////////////////////////////

    function pingback($url){
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