<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class B {    static $path = array();
    static $lang;
    static $autoload = array();

////////////////////////////////////////////////////////////////////////////////

    function version($what = ''){        $version = array('name' => 'Chinpoko', 'id' => '0.1.6');
        return ($what ? $version[$what] : $version);    }

////////////////////////////////////////////////////////////////////////////////

    function init($level = 0){        $path = realpath(dirname(__file__).'/../../');        self::$path = (isset(self::$path[0]) ? array($path, self::$path[0]) : array($path));
        error_reporting($level);        spl_autoload_register(array('self', 'autoload'));

        debug::timer();
        sql::connect(self::config('site.db'));

        self::$lang = self::config('site.lang');
        $lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
        is_dir(file::path('lang/'.$lang)) and
        self::$lang = $lang;

        date_default_timezone_set(self::config('site.timezone'));
        setlocale(LC_ALL, self::i18n('site.locale'));

        if ($level = self::config('site.debug')){
            error_reporting($level);
            sql::logger(array('debug', 'sql'));
        }
    }
////////////////////////////////////////////////////////////////////////////////

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
            $dir = '/data/config';
            $dirs = array(self::$path[0].$dir, self::$path[1].$dir);

            if (!$cache = cache::get('config.php', array('file' => $dirs))){                $files = file::glob($dirs[0].'/*.yaml', $dirs[1].'/*.yaml');

                foreach ($files as $k => $v){
                    $k = substr(basename($v), 0, -5);
                    $k = explode('.', $k);
                    $yaml = yaml::load($v);
                    $array = array($k[0] => (self::len($k) == 2 ? array($k[1] => $yaml) : $yaml));
                    $config = arr::merge($config, $array);
                }

                cache::set($config);
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

    function i18n($text, $array = array()){
        static $lang = array();

        if (!$lang){            $dir = '/lang/en';
            $dirs = array(self::$path[0].$dir, self::$path[1].$dir);

            if (self::$lang != 'en'){                $dir = '/lang/'.self::$lang;
                $dirs = array_merge($dirs, array(self::$path[0].$dir, self::$path[1].$dir));
            }

            if (!$cache = cache::get('lang/'.self::$lang.'.php', array('file' => $dirs))){
                $func = create_function('$v', 'return $v."/*.yaml";');
                $dirs = array_map($func, $dirs);
                $files = call_user_func_array(array('file', 'glob'), $dirs);

                foreach ($files as $k => $v)
                    $lang = arr::merge($lang, array(substr(basename($v), 0, -5) => yaml::load($v)));

                $lang = arr::assoc($lang);
                cache::set($lang);
            } else {
                $lang = include $cache;
            }
        }

        $var = tags::varname($text, '$lang');

        if ($func = create_function('$lang', 'if (isset('.$var.')) return '.$var.';'))
            $text = $func($lang);

        return (is_array($text) ? $text : str::format($text, $array));
    }

////////////////////////////////////////////////////////////////////////////////

    function is_windows(){
        return (strtolower(substr(PHP_OS, 0, 3)) == 'win');
    }

////////////////////////////////////////////////////////////////////////////////

    function call(){        static $func = array();

        $args = func_get_args();
        $name = trim(array_shift($args));

        if ($name[0] == '*'){            $name = substr($name, 1);
            $args = $args[0];        }

        if (!$func){            $dir = '/ext';
            $dirs = array(self::$path[0].$dir, self::$path[1].$dir);
            if (!$cache = cache::get('ext.php', array('file' => $dirs))){                $files = file::glob($dirs[0].'/*.php', $dirs[1].'/*.php');

                foreach ($files as $k => $v){                    foreach (token_get_all(file_get_contents($v)) as $token){
                        if ($token[0] == T_FUNCTION)
                            $line = $token[2];

                        if ($token[0] == T_STRING and $token[2] == $line){
                            $line = 0;
                            $func[$token[1]] = $v;
                        }
                    }                }

                cache::set($func);
            } else {
                $func = include $cache;
            }        }

        if (!is_array($name)){            if ($file = $func[$name]){                if (!function_exists($name))
                    include $file;

                return call_user_func_array($name, $args);            }

            if (strpos($name, '::'))
                $name = explode('::', $name);
        }

        if (
            (is_array($name) and method_exists($name[0], $name[1])) or
            (!is_array($name) and function_exists($name))
        )
            return call_user_func_array($name, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    static function autoload($Name){
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

    function show($string, $_ = array(), $is_main = false){        static $_main;

        if (!is_array($_))
            list($_, $is_main) = array(array(), $_);        else            extract($_);

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
}