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

    static function version($what = ''){        $version = array('name' => 'Chuck', 'id' => '0.9.dev');
        return ($what ? $version[$what] : $version);    }

////////////////////////////////////////////////////////////////////////////////

    static function init(){        self::$cache['stat'] = microtime(true);
        if (!self::$path[0])
            self::$path[0] = realpath(dirname(__file__).'/../..');

        ini_set('docref_root', 'http://php.net/');
        spl_autoload_register(array('self', 'autoload'));
        date_default_timezone_set(self::config('lib.b.timezone'));
        setlocale(LC_ALL, self::lang('lib.b.locale'));

        $lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
        $lang and !self::$lang and self::$lang = $lang;

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

        if (!$config){            $dirs = array();

            foreach (self::$path as $path)
                foreach (array('mod', 'lib', 'ext', '') as $dir)
                    if (is_dir($path.'/'.$dir))                        $dirs[] = $path.'/'.$dir;
            if (!$config = cache::get('b/config.php', array('file' => $dirs))){                $files = array();
                foreach ($dirs as $dir){                    $name = basename($dir);

                    if (in_array($name, array('mod', 'lib')))
                        $array = file::dir($dir);
                    else                        $array = array_flip(file::glob($dir.'/*.yml'));

                    foreach ($array as $file => $iter)
                        if (substr($file, -4) == '.yml')
                            $files[$file] = $name;
                }

                foreach ($files as $file => $dir){                    $key = ($dir == 'lib' ? $dir.'.' : '').substr(basename($file), 0, -4);
                    $array = arr::flat(yaml::load($file));
                    $array['#file'] = $file;
                    $config = arr::merge($config, array($key => $array));
                }

                cache::set($config = arr::assoc($config));
            }
        }

        $args = func_get_args();

        if (!func_num_args()){            return $config;
        } elseif (func_num_args() == 1){            if (isset(self::$cache['config'][$args[0]]))
                return self::$cache['config'][$args[0]];

            $var = piles::varname($args[0], '$config');

            if ($func = create_function('$config', 'if (isset('.$var.')) return '.$var.';'))
                return self::$cache['config'][$args[0]] = $func($config);
        } else {            $array = explode('.', str_replace('\.', piles::char('.'), $args[0]));

            for ($i = 0, $c = self::len($array); $i < $c; $i++){                $section = join('.', $array);
                $var = piles::varname($section, '$config');
                $tmp = 'if (is_array('.$var.') and isset('.$var.'["#file"])) return '.$var.';';

                if (
                    ($func = create_function('$config', $tmp)) and
                    ($tmp = $func($config))
                ){                    $data = $tmp;
                    break;
                }
                array_pop($array);            }

            if (!isset($data))
                return;

            if ($data == $args[1])
                return 0;

            $file = $data['#file'];
            unset($data['#file']);

            if ($section = (substr($args[0], self::len($section) + 1)))
                $set = arr::merge($data, arr::assoc(array($section => $args[1])));
            else
                $set = $args[1];

            if ($data == $set)
                return 0;

            self::$cache['config'][$args[0]] = $args[1];
            cache::remove('b/config.php');
            return (bool)file_put_contents($file, yaml::dump($set));
        }
    }

////////////////////////////////////////////////////////////////////////////////

    static function lang($string, $array = array()){        if (isset(self::$cache['lang'][$string])){
            if (!is_array(self::$cache['lang'][$string]))
                return str::format(self::$cache['lang'][$string], $array);

            return self::$cache['lang'][$string];
        }
        $pos = array();

        for ($i = 0, $c = substr_count($string, '.'); $i < $c; $i++){
            $pos[] = $tmp = strpos($string, '.', (end($pos) + 1));

            if (!$i)
                continue;

            if (
                self::config($found = substr($string, 0, $tmp).'.'.self::$lang) or
                self::config($found = substr($string, 0, $tmp).'.en')
            ){                $result = $found.substr($string, $tmp);
                break;
            }
        }

        if (!$result)
            return;

        if (!is_array(self::$cache['lang'][$string] = self::config($result)))
            return str::format(self::$cache['lang'][$string], $array);

        return self::$cache['lang'][$string];
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_windows(){
        return (substr(PHP_OS, 0, 3) == 'WIN');
    }

////////////////////////////////////////////////////////////////////////////////

    static function call(){        static $call;

        $args = func_get_args();
        $name = array_shift($args);

        if (!is_array($name) and ($name = trim($name)) and $name[0] == '*'){            $name = substr($name, 1);
            $args = $args[0];        }

        if (!$call){            $dirs = array();

            foreach (self::$path as $path)
                $dirs[] = $path.'/ext';
            if (!$call = cache::get('b/call.php', array('file' => $dirs))){                $files = array();

                foreach ($dirs as $dir)
                    $files = array_merge($files, file::glob($dir.'/*.php'));

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

        if (!self::$path[1] or !is_dir($dir = self::$path[1].'/tmp'))
            $dir = self::$path[0].'/tmp';

        if (is_file($cache = $dir.'/b/autoload.php'))
            self::$cache['autoload'] = array_merge(
                include $cache,
                self::$cache['autoload']
            );

        $name = strtolower($Name);

        if (isset(self::$cache['autoload'][$name]))
            return include $prev = self::$cache['autoload'][$name];

        $files = array(
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
            $files = array_merge($files, array(
                $prev.'/'.$files[0],
                $prev.'/'.$files[1],
                $prev.'/'.$name.'/'.$name,
                $prev.'/'.$name,

                $prev.'/'.$files[4],
                $prev.'/'.$files[5],
                $prev.'/'.$Name.'/'.$Name,
                $prev.'/'.$Name
            ));

        foreach ($files as $file)
            if (
                (self::$path[1] and is_file($path = self::$path[1].'/lib/'.$file.'.php')) or
                is_file($path = self::$path[0].'/lib/berry/'.$file.'.php') or
                is_file($path = self::$path[0].'/lib/'.$file.'.php')
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

    static function load($name = '', $_ = array()){
        $name = ($name ? $name : b::config('lib.b.load'));
        $files = array(
            str_replace('.', '/', $name),
            str_replace('.', '/', substr($name, 0, strrpos($name, '.'))).strrchr($name, '.')
        );

        foreach ($files as $file)
            if (
                is_file(self::$cache['load'] = file::path('mod/'.$file.'.php')) or
                is_file(self::$cache['load'] = file::path('mod/'.$file.'/index.php'))
            ){
                unset($name, $files, $file);
                extract($_);
                include_once self::$cache['load'];
                return self::$cache['load'];
            }
    }

////////////////////////////////////////////////////////////////////////////////

    static function show($name = '', $_ = array()){
        $name = ($name ? $name : b::config('lib.b.show'));
        $name = str_replace('.', '/', $name);
        $files = array('ext/'.$name, 'mod/'.$name, 'lib/berry/'.$name, 'lib/'.$name);

        foreach ($files as $file)
            if (
                is_file(self::$cache['show'] = file::path($file.'.phtml')) or
                is_file(self::$cache['show'] = file::path($file.'/index.phtml'))
            ){
                !cache::exists('b/call.php') and self::call('#');
                $funcs = include cache::exists('b/call.php');

                foreach (token_get_all(file_get_contents($file)) as $token)
                    if ($token[0] == T_STRING){
                        if (!function_exists($token[1]) and $funcs[$token[1]])
                            include $funcs[$token[1]];
                    }

                ob_start();
                    unset($name, $files, $file, $funcs, $token);
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

    static function stat($what = ''){        $stat = array(
            'pgt' => (microtime(true) - self::$cache['stat']),
            'sql' => sql::stat(),
            'piles' => piles::stat(),
            'memory' => array(
                'limit' => int::size(int::bytes(ini_get('memory_limit'))),
                'usage' => int::size(self::call('memory_get_usage')),
                'peak'  => int::size(self::call('memory_get_peak_usage'))
            )
        );

        if (!$what)
            return $stat;

        $stat = arr::flat($stat);
        return $stat[$what];
    }

////////////////////////////////////////////////////////////////////////////////
}