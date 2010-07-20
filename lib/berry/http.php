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

    static function status($num){
        $http = b::config('lib.http.status');
        $status = (substr(PHP_SAPI, 0, 3) == 'cgi' ? 'Status:' : $_SERVER['SERVER_PROTOCOL']);

        header($status.' '.$num.' '.$http[$num], true);
    }

////////////////////////////////////////////////////////////////////////////////

    function request($url, $params = array()){
        $current = array('method' => $_SERVER['REQUEST_METHOD']);

        foreach ($_SERVER as $k => $v)
            if (substr($k, 0, 5) == 'HTTP_')
                $current[substr($k, 5)] = $v;

        unset($current['COOKIE'], $current['CONNECTION']);
        $params = array_merge($current, $params);

        foreach ($params as $k => $v){
            unset($params[$k]);

            $k = strtr(strtolower($k), '_', ' ');
            $k = strtr(ucwords($k), ' ', '-');
            $params[$k] = $v;
        }

        $url = parse_url($url);
        $fp = @fsockopen($url['host'], ($url['port'] ? $url['port'] : 80));

        if (!$fp)
            return array();

        $query = array(strtoupper($params['Method']).' '.$url['path'].' HTTP/1.0');
        $content = (is_array($params['Content']) ? http_build_query($params['Content']) : $params['Content']);
        $params['Host'] = $url['host'];

        if (!$params['Content-Length'] and $content)
            $params['Content-Length'] = strlen($content);

        unset($params['Method'], $params['Content']);

        foreach ($params as $k => $v)
            $query[] = $k.': '.$v;

        $query = join("\r\n", $query)."\r\n\r\n".$content;

        fputs($fp, $query);

        while (!feof($fp))
            $response .= fgets($fp);

        fclose($fp);
        $pos1 = strpos($response, "\r\n\r\n");
        $result = array();

        foreach (explode("\r\n", substr($response, 0, $pos1)) as $line){
            if (!is_int($pos2 = strpos($line, ':')))
                $result[] = $line;
            else
                $result[substr($line, 0, $pos2)] = substr($line, ($pos2 + 1));
        }

        $result['Content'] = substr($response, $pos1);
        return $result;
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