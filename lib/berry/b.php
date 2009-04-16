<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class B {
    static $autoload = array();

////////////////////////////////////////////////////////////////////////////////

    function version($what = ''){
        return ($what ? $version[$what] : $version);

////////////////////////////////////////////////////////////////////////////////

    function init(){

        debug::timer();
        sql::connect(self::config('site.dsn'));

        date_default_timezone_set(self::config('site.timezone'));
        setlocale(LC_ALL, str::text('site.locale'));

        if ($level = (int)self::config('site.debug')){
            error_reporting(self::config('site.debug'));
            sql::logger(array('debug', 'sql'));
        }
    }


    function q($i = '', $c = '', $s = '/'){
        $q = explode('/', $_GET['q']);

        if (is_int($pos = strpos($_SERVER['REQUEST_URI'], '?')))
            $uri = substr($_SERVER['REQUEST_URI'], 0, $pos);
        else
            $uri = $_SERVER['REQUEST_URI'];

        $uri = substr($uri, 0, self::len(dirname($_SERVER['PHP_SELF'])));
        array_unshift($q, 'http'.($_SERVER['HTTPS'] ? 's' : '').'://'.str::clean($_SERVER['SERVER_NAME'].$uri));

        if (is_numeric($i) and $i >= 0 and !is_numeric($c))
            $result = $q[$i];
        elseif (is_numeric($i) and $i < 0 and !is_numeric($c))
            $result = join($s, array_slice($q, $i, 1));
        elseif (is_numeric($i) and is_numeric($c) and $c >= 0)
            $result = join($s, array_slice($q, $i, (
                ($c ? $c : self::len($q)) + ($i ? 0 : 1)
            )));
        elseif (is_numeric($i) and is_numeric($c) and $c < 0)
            $result = join($s, array_slice($q, $i, $c));
        else
            return $q;

        return urldecode($result);
    }

////////////////////////////////////////////////////////////////////////////////

    function l(){
        $args = func_get_args();
        return call_user_func_array(array('tags', 'vars'), $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function len($mixed){
        return ((is_array($mixed) or is_object($mixed)) ? sizeof($mixed) : strlen($mixed));
    }

////////////////////////////////////////////////////////////////////////////////

    function config(){
        static $config = array();

        if (!$config){
            $pattern = '/data/config/*.yaml';
            $files = file::glob(self::$path[0].$pattern, self::$path[1].$pattern);

            if (!$cache = self::cache('config.php', array('file' => $files))){
                foreach ($files as $k => $v){
                    $k = substr(basename($v), 0, -5);
                    $k = explode('.', $k);
                    $yaml = yaml::load($v);

                    if (self::len($k) == 2)
                        $array = array($k[0] => array($k[1] => $yaml));
                    else
                        $array = array($k[0] => $yaml);

                    $config = arr::merge($config, $array);
                }

                self::cache($config);
            } else {
                $config = include $cache;
            }
        }

        $args = func_get_args();

        if (func_num_args() == 1){
            $var = tags::varname($args[0], '$config');

            if ($func = create_function('$config', 'if (isset('.$var.')) return '.$var.';'))
                return $func($config);
        } else {
            return $config;
        }
    }

////////////////////////////////////////////////////////////////////////////////

    function is_windows(){
        return (strtolower(substr(PHP_OS, 0, 3)) == 'win');
    }

////////////////////////////////////////////////////////////////////////////////

    function call(){
        $args = func_get_args();
        $func = array_shift($args);

        if (!is_array($func) and is_int($pos = strrpos($func, '.'))){
            $file = substr($func, 0, $pos);
            $func = substr($func, ($pos + 1));
        }

        if (!is_array($func) and strpos($func, '::'))
            $func = explode('::', $func);

        if (
            (is_array($func) and method_exists($func[0], $func[1])) or
            (!is_array($func) and function_exists($func))
        )
            return call_user_func_array($func, $args);

        if (is_file($path = file::path('lib/'.str_replace('.', '/', $file).'.php'))){
            $args = func_get_args();
            include $path;
            return call_user_func_array(array('self', 'call'), $args);
        }
    }

////////////////////////////////////////////////////////////////////////////////

    function register_autoload(){

////////////////////////////////////////////////////////////////////////////////

    function autoload($Name){
        static $prev;

        $name = strtolower($Name);

        if (isset(self::$autoload[$name]))
            return include $prev = self::$autoload[$name];

        $file = array(
             str_replace('_', '/', $name),
            substr($name, 0, strpos($name, '_')).'/'.substr($name, strpos($name, '_') + 1),
            $name.'/'.$name,
            $name,

            str_replace('_', '/', $Name),
            substr($Name, 0, strpos($Name, '_')).'/'.substr($Name, strpos($Name, '_') + 1),
            $Name.'/'.$Name,
            $Name
        );

        if ($prev = substr(str_replace(b::$path, '', dirname($prev)), 5))
            $file = array_merge($file, array(
                $prev.'/'.$file[0],
                $prev.'/'.$file[1],
                $prev.'/'.$name.'/'.$name,
                $prev.'/'.$name,

                $prev.'/'.$file[4],
                $prev.'/'.$file[5],
                $prev.'/'.$Name.'/'.$Name,
                $prev.'/'.$Name
            ));

        foreach ($file as $try)
            if (
                (self::$path[1] and is_file($path = self::$path[1].'/lib/'.$try.'.php')) or
                is_file($path = self::$path[0].'/lib/berry/'.$try.'.php') or
                is_file($path = self::$path[0].'/lib/'.$try.'.php')
            ){
                include self::$autoload[$name] = $prev = $path;
                return true;
            }
    }

////////////////////////////////////////////////////////////////////////////////

    function load($string, $_ = array()){
        extract($_);

        $_['string'] = $string;
        $_['files']  = array(
            str_replace('.', '/', $string),
            str_replace('.', '/', substr($string, 0, strrpos($string, '.'))).strrchr($string, '.')
        );

        foreach ($_['files'] as $file)
            if (
                is_file($_['file'] = file::path('load/'.$file.'.php')) or
                is_file($_['file'] = file::path('load/'.$file.'/index.php'))
            ){
                unset($string, $file);
                include_once $_['file'];
                return $_['file'];
            }
    }

////////////////////////////////////////////////////////////////////////////////

    function show($string, $_ = array(), $is_main = false){

        if (is_bool($_))
            list($_, $is_main) = array(array(), $_);

        if ($is_main){
            $_main = $string;
            return;
        }

        if ($_main and $string == self::config('site.show'))
            $string = $_main;

        $_['string'] = $string;
        $_['is_main'] = $is_main;
        $string = str_replace('.', '/', $string);

        if (
            is_file($_['file'] = file::path('show/'.$string.'.phtml')) or
            is_file($_['file'] = file::path('show/'.$string.'/index.phtml'))
        ){
            unset($string, $is_main);
            ob_start();
                include $_['file'];
            return ob_get_clean();
        }
    }

////////////////////////////////////////////////////////////////////////////////

    function cache(){
        global $sql;
        static $file, $md5;

        $args = func_get_args();

        if (func_num_args() == 1){
            file::mkdir(dirname($file));

            $func = (!is_scalar($args[0]) ? 'arr::export' : 'file_put_contents');

            self::call($func, substr($file, 0, -4), $args[0]);
            file_put_contents($file, $md5);
            return $file;
        }

        if ($args[1]['file'])
            $time = array_sum(array_map('filemtime', (array)$args[1]['file']));

        if ($args[1]['db'])
            foreach ((array)$args[1]['db'] as $table)
                if ($query = sql::getRow('show table status like "?_"', $table))
                    $time += strtotime($query['Update_time']);

        if ($args[1]['url'])
            foreach ((array)$args[1]['url'] as $url)
                if ($headers = get_headers($url, true))
                    $time += strtotime($headers['Last-Modified']);

        $file = file::path('cache/').$args[0].'.md5';
        $md5  = md5($time);

        if (
            is_file($cache = substr($file, 0, -4)) and
            is_file($file) and file_get_contents($file) == $md5
        )
            return $cache;
    }

////////////////////////////////////////////////////////////////////////////////
