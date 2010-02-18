<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class B {    static $path = array('');
    static $lang = 'ru';
    protected static $cache = array();

////////////////////////////////////////////////////////////////////////////////

    static function version($what = ''){        $version = array('name' => 'Chinpoko', 'id' => '0.1.6.dev');
        return ($what ? $version[$what] : $version);    }

////////////////////////////////////////////////////////////////////////////////

    static function init(){        if (!self::$path[0])
            self::$path[0] = realpath(dirname(__file__).'/../..');

        ini_set('docref_root', 'http://php.net/');
        ini_set('session.use_trans_sid', false);
        ini_set('session.use_cookies', true);
        ini_set('session.cookie_lifetime', 0);

        spl_autoload_register(array('self', 'autoload'));
        debug::timer();

        $lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
        $lang and is_dir(file::path('lang/'.$lang)) and
        self::$lang = $lang;

        date_default_timezone_set(self::config('lib.b.timezone'));
        setlocale(LC_ALL, self::lang('lib.b.locale'));
        session_start();

        $_GET['berry'] = ($_GET['berry'] ? str::clean($_GET['berry']) : 'home');
        self::router();
    }
////////////////////////////////////////////////////////////////////////////////

    static function q($i = '', $c = '', $s = '/'){
        $q = explode('/', $_GET['berry']);

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

    static function l(){
        $args = func_get_args();
        $var = piles::varname($args[0], '$GLOBALS');

        if (func_num_args() == 1){            if ($func = create_function('', 'if (isset('.$var.')) return '.$var.';'))
                return $func();
        } else {
            if ($func = create_function('$def', 'return '.$var.' = $def;'))
                return $func($args[1]);
        }
    }

////////////////////////////////////////////////////////////////////////////////

    static function len($mixed){
        return ((is_array($mixed) or is_object($mixed)) ? sizeof($mixed) : strlen($mixed));
    }

////////////////////////////////////////////////////////////////////////////////

    static function config(){
        static $config;

        if (!$config){
            $dir = '/data/config';
            $dirs = array(self::$path[0].$dir, self::$path[1].$dir);

            if (!$config = cache::get('b/config.php', array('file' => $dirs))){                $files = file::glob($dirs[0].'/*.yaml', $dirs[1].'/*.yaml');

                foreach (array_reverse($files) as $v)
                    $config = arr::merge($config, arr::assoc(array(
                        substr(basename($v), 0, -5) => arr::flat(yaml::load($v))
                    )));

                cache::set($config);
            }
        }

        $args = func_get_args();

        if (func_num_args() == 1){            if (isset(self::$cache['config'][$args[0]]))
                return self::$cache['config'][$args[0]];

            $var = piles::varname($args[0], '$config');

            if ($func = create_function('$config', 'if (isset('.$var.')) return '.$var.';'))
                return self::$cache['config'][$args[0]] = $func($config);
        } else {
            return $config;
        }
    }

////////////////////////////////////////////////////////////////////////////////

    static function lang($text, $array = array()){
        static $lang;

        if (!$lang){            $dir = '/lang/en';
            $dirs = array(self::$path[0].$dir, self::$path[1].$dir);

            if (self::$lang != 'en'){                $dir = '/lang/'.self::$lang;
                $dirs = array_merge($dirs, array(self::$path[0].$dir, self::$path[1].$dir));
            }

            if (!$lang = cache::get('b/lang/'.self::$lang.'.php', array('file' => $dirs))){
                $func = create_function('$v', 'return $v."/*.yaml";');
                $dirs = array_map($func, $dirs);
                $files = call_user_func_array(array('file', 'glob'), $dirs);

                foreach ($files as $k => $v)
                    $lang = arr::merge($lang, array(substr(basename($v), 0, -5) => yaml::load($v)));

                $lang = arr::assoc($lang);
                cache::set($lang);
            }
        }

        if (isset(self::$cache['lang'][$text]))
            return self::$cache['lang'][$text];

        $var = piles::varname($text, '$lang');

        if ($func = create_function('$lang', 'if (isset('.$var.')) return '.$var.';'))
            $result = $func($lang);

        if (is_array($result))
            return self::$cache['lang'][$text] = $result;

        return self::$cache['lang'][$text] = str::format($result, $array);
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_windows(){
        return (substr(PHP_OS, 0, 3) == 'WIN');
    }

////////////////////////////////////////////////////////////////////////////////

    static function call(){        static $call;

        $args = func_get_args();
        $name = trim(array_shift($args));

        if ($name[0] == '*'){            $name = substr($name, 1);
            $args = $args[0];        }

        if (!$call){            $dir = '/ext';
            $dirs = array(self::$path[0].$dir, self::$path[1].$dir);
            if (!$call = cache::get('b/call.php', array('file' => $dirs))){                $files = file::glob($dirs[0].'/*.php', $dirs[1].'/*.php');

                foreach ($files as $k => $v)                    foreach (token_get_all(file_get_contents($v)) as $token){                        if ($token[0] == T_CLASS)
                            break;

                        if ($token[0] == T_FUNCTION)
                            $line = $token[2];

                        if ($token[0] == T_STRING and $token[2] == $line){
                            $line = 0;
                            $call[$token[1]] = $v;
                        }
                    }

                cache::set($call);
            }        }

        if (!is_array($name)){            if ($file = $call[$name]){                if (!function_exists($name))
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

    static function function_exists($func){
        static $funcs = array();

        if (function_exists($func))
            return true;

        !cache::exists('b/call.php') and self::call('#');

        if (!$funcs and ($file = cache::exists('b/call.php')))
            $funcs = include $file;

        return isset($funcs[$func]);
    }

////////////////////////////////////////////////////////////////////////////////

    static function autoload($Name){
        static $prev;

        if (!isset(self::$cache['autoload']))
            self::$cache['autoload'] = array();

        if (!is_dir($dir = self::$path[1].'/cache'))
            $dir = self::$path[0].'/cache';

        if (is_file($cache = $dir.'/b/autoload.php'))
            self::$cache['autoload'] = array_merge(
                include $cache,
                self::$cache['autoload']
            );

        $name = strtolower($Name);

        if (isset(self::$cache['autoload'][$name]))
            return include $prev = self::$cache['autoload'][$name];

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
                include self::$cache['autoload'][$name] = $prev = $path;

                $contents  = "<?php\r\n";
                $contents .= 'return '.var_export(self::$cache['autoload'], true);
                $contents .= ";\r\n";

                file::mkdir(dirname($cache));
                file_put_contents($cache, $contents);
                return true;
            }
    }

////////////////////////////////////////////////////////////////////////////////

    static function load($string = '', $_ = array()){
        $string = ($string ? $string : b::config('lib.b.load'));
        $files = array(
            str_replace('.', '/', $string),
            str_replace('.', '/', substr($string, 0, strrpos($string, '.'))).strrchr($string, '.')
        );

        foreach ($files as $file)
            if (
                is_file(self::$cache['load'] = file::path('load/'.$file.'.php')) or
                is_file(self::$cache['load'] = file::path('load/'.$file.'/index.php'))
            ){
                unset($string, $files, $file);
                extract($_);
                include_once self::$cache['load'];
                return self::$cache['load'];
            }
    }

////////////////////////////////////////////////////////////////////////////////

    static function show($string = '', $_ = array()){
        $string = ($string ? $string : b::config('lib.b.show'));
        $string = str_replace('.', '/', $string);

        if (
            is_file(self::$cache['show'] = file::path('show/'.$string.'.phtml')) or
            is_file(self::$cache['show'] = file::path('show/'.$string.'/index.phtml'))
        ){            !cache::exists('b/call.php') and self::call('#');
            $funcs = include cache::exists('b/call.php');

            foreach (token_get_all(file_get_contents($file)) as $token)
                if ($token[0] == T_STRING){
                    if (!function_exists($token[1]) and $funcs[$token[1]])
                        include $funcs[$token[1]];
                }

            ob_start();
                unset($string, $funcs, $token);
                extract($_);
                include self::$cache['show'];
            return trim(ob_get_clean());
        }
    }

////////////////////////////////////////////////////////////////////////////////

    static function router(){
        $rules = self::config('lib.b.router');

        if (func_num_args()){
            list($key, $array) = func_get_args();

            if (preg_match_all('/\[([^\]]*)\]/', $rules[$key]['url'], $match)){
                for ($i = 0, $c = self::len($match[0]); $i < $c; $i++)
                    $match[1][$i] = $rules[$match[1][$i]]['url'];

                $rules[$key]['url'] = str_replace($match[0], $match[1], $rules[$key]['url']);
            }

            return str::format($rules[$key]['url'], ($array ? $array : array()));
        }

        foreach ($rules as $k => $v){
            if (preg_match_all('/\[([^\]]*)\]/', $v['re'], $match))
                for ($i = 0, $c = self::len($match[0]); $i < $c; $i++)
                    $rules[$k]['re'] = str_replace($match[0][$i], $rules[$match[1][$i]]['re'], $v['re']);
        }

        foreach ($rules as $rule)            $_GET['berry'] = preg_replace('/^'.str_replace('/', '\/', $rule['re']).'$/i', $rule['route'], $_GET['berry']);

        if (($url = parse_url($_GET['berry'])) and $url['query']){
            parse_str($url['query'], $query);
            $_GET['berry'] = $url['path'];
            $_GET = array_merge($_GET, $query);
        }
    }

////////////////////////////////////////////////////////////////////////////////
}