<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class B {
    static $path;
    static $lang;
    static $query;
    protected static $cache = array();

////////////////////////////////////////////////////////////////////////////////

    static function version($what = ''){
        $version = array('name' => 'Holly', 'id' => '1.0.dev');
        return ($what ? $version[$what] : $version);
    }

////////////////////////////////////////////////////////////////////////////////

    static function init($config = array()){
        if ($_SERVER['QUERY_STRING'])
            $uri = substr($_SERVER['REQUEST_URI'], 0, -self::len($_SERVER['QUERY_STRING']));
        else
            $uri = $_SERVER['REQUEST_URI'];

        $uri = trim(preg_replace('/\/+/', '/', $uri), '/');
        $uri = parse_url($uri);
        $len = self::len(dirname($_SERVER['PHP_SELF']));
        $query = substr($uri['path'], ($len - 1));

        $config = array_merge(array(
            'path' => '.',
            'lang' => strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)),
            'query' => ($query ? trim($query, '/') : 'home')
        ), $config);

        self::$cache['stat'] = microtime(true);
        self::$path = $config['path'].';'.realpath(dirname(__file__).'/..');
        self::$lang = $config['lang'];
        self::$query = $config['query'];

        spl_autoload_register(array('self', 'autoload'));
    }

////////////////////////////////////////////////////////////////////////////////

    static function q($i = '', $c = '', $s = '/'){
        if (!isset(self::$cache['q'][self::$query])){
            $url = parse_url(str::clean($_SERVER['REQUEST_URI']));
            $len = self::len(dirname($_SERVER['PHP_SELF']));

            $host  = 'http'.($_SERVER['HTTPS'] ? 's' : '').'://'.$_SERVER['SERVER_NAME'];
            $host .= '/'.substr($url['path'], 0, ($len - 1));

            $q = explode('/', self::$query);
            array_unshift($q, trim($host, '/'));

            self::$cache['q'][self::$query] = $q;
        }

        $q = self::$cache['q'][self::$query];

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

        if (func_num_args() == 1){
            if ($func = create_function('', 'if (isset('.$var.')) return '.$var.';'))
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
            $files = array();

            if ($config = cache::get('b/config.php'))
                $files = $config['#files'];

            if (!$config = cache::get('b/config.php', array('file' => $files))){
                $files = array();

                foreach (array_reverse(explode(';', self::$path)) as $dir){
                    if (is_dir($dir))
                        foreach (file::dir($dir, '/\.yml$/i') as $file => $info)
                            $files[] = $file;
                }

                foreach ($files as $file){
                    $key = substr(basename($file), 0, -4);
                    $array = yaml::load($file);
                    $array['#file'] = $file;
                    $config = arr::merge($config, arr::assoc(array($key => $array)));
                    $config['#files'][] = $file;
                }

                cache::set($config);
            }
        }

        $args = func_get_args();

        if (!func_num_args()){
            return $config;
        } elseif (func_num_args() == 1){
            if (is_array($args[0]))
                return $config = arr::merge($config, arr::assoc($args[0]));

            if (isset(self::$cache['config'][$args[0]]))
                return self::$cache['config'][$args[0]];

            $var = piles::varname($args[0], '$config');

            if ($func = create_function('$config', 'if (isset('.$var.')) return '.$var.';'))
                return self::$cache['config'][$args[0]] = $func($config);
        } else {
            $array = explode('.', str_replace('\.', piles::char('.'), $args[0]));

            for ($i = 0, $c = self::len($array); $i < $c; $i++){
                $section = join('.', $array);
                $var = piles::varname($section, '$config');
                $tmp = 'if (is_array('.$var.') and isset('.$var.'["#file"])) return '.$var.';';

                if (
                    ($func = create_function('$config', $tmp)) and
                    ($tmp = $func($config))
                ){
                    $data = $tmp;
                    break;
                }

                array_pop($array);
            }

            if (!$data)
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
            file::chmod(dirname($file));
            return (bool)file_put_contents($file, yaml::dump($set));
        }
    }

////////////////////////////////////////////////////////////////////////////////

    static function lang($string, $array = array()){
        if (isset(self::$cache['lang'][$string])){
            if (!is_array(self::$cache['lang'][$string]))
                return str::format(self::$cache['lang'][$string], $array);

            return self::$cache['lang'][$string];
        }

        if (self::config($found = self::$lang.'.'.$string))
            $result = $found;
        elseif (self::config($found = $string.'.'.self::$lang))
            $result = $found;
        elseif (self::config($found = $string))
            $result = $found;

        if (!$result)
            return;

        if (!is_array(self::$cache['lang'][$string] = self::config($result)))
            return str::format(self::$cache['lang'][$string], $array);

        return self::$cache['lang'][$string];
    }

////////////////////////////////////////////////////////////////////////////////

    static function call(){
        static $call;

        $args = func_get_args();
        $name = array_shift($args);

        if (!is_array($name) and ($name = trim($name)) and $name[0] == '*'){
            $name = substr($name, 1);
            $args = $args[0];
        }

        if (!$call){
            $dirs = array();

            foreach (array_reverse(explode(';', self::$path)) as $path)
                $dirs[] = $path.'/ext';

            if (!$call = cache::get('b/call.php', array('file' => $dirs))){
                $files = array();

                foreach ($dirs as $dir)
                    $files = array_merge($files, file::glob($dir.'/*.php'));

                foreach ($files as $k => $v)
                    foreach (token_get_all(file_get_contents($v)) as $token){
                        if ($token[0] == T_CLASS)
                            break;

                        if ($token[0] == T_FUNCTION)
                            $line = $token[2];

                        if ($token[0] == T_STRING and $token[2] == $line){
                            $line = 0;
                            $call[$token[1]] = $v;
                        }
                    }

                cache::set($call);
            }
        }

        if (!is_array($name)){
            if ($file = $call[$name]){
                if (!function_exists($name))
                    include $file;

                return call_user_func_array($name, $args);
            }

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

    static function function_exists($name){
        static $funcs = array();

        if (strpos($name, '::'))
            $name = explode('::', $name);

        if (is_array($name))
            return method_exists($name[0], $name[1]);

        if (function_exists($name))
            return true;

        !cache::exists('b/call.php') and self::call('#');

        if (!$funcs and ($file = cache::exists('b/call.php')))
            $funcs = include $file;

        return isset($funcs[$name]);
    }

////////////////////////////////////////////////////////////////////////////////

    static function autoload($Name){
        static $prev;

        if (!isset(self::$cache['autoload']))
            self::$cache['autoload'] = array();

        $paths = explode(';', self::$path);
        $dir = end($paths).'/tmp';

        foreach ($paths as $v)
            if (is_dir($dir = $v.'/tmp')){
                $dir = $v.'/tmp';
                break;
            }

        if (is_file($cache = $dir.'/b/autoload.php'))
            self::$cache['autoload'] = array_merge(
                include $cache,
                self::$cache['autoload']
            );

        $Name = str_replace('\\', '/', $Name);
        $name = strtolower($Name);

        if (isset(self::$cache['autoload'][$name]))
            return include $prev = self::$cache['autoload'][$name];

        $files = array(
            str_replace('_', '/', $name),
            substr($name, 0, strpos($name, '_')).'/'.substr($name, (strpos($name, '_') + 1)),
            $name.'/'.$name,
            $name,

            str_replace('_', '/', $Name),
            substr($Name, 0, strpos($Name, '_')).'/'.substr($Name, (strpos($Name, '_') + 1)),
            $Name.'/'.$Name,
            $Name
        );

        if ($prev = str_replace($paths, '', dirname($prev)))
            foreach ($files as $file)
                $files[] = $prev.'/'.$file;

        foreach ($paths as $path)
            foreach ($files as $file)
                if (
                    is_file($tmp = $path.'/berry/'.$file.'.php') or
                    is_file($tmp = $path.'/lib/'.$file.'.php') or
                    is_file($tmp = $path.'/'.$file.'.php')
                ){
                    if (in_array($tmp, self::$cache['autoload']))
                        return true;

                    include self::$cache['autoload'][$name] = $prev = $tmp;

                    $contents  = "<?php\r\n";
                    $contents .= 'return '.var_export(self::$cache['autoload'], true);
                    $contents .= ";\r\n";

                    file::mkdir(dirname($cache));
                    file_put_contents($cache, $contents);
                    return true;
                }
    }

////////////////////////////////////////////////////////////////////////////////

    static function show($name, $_ = array()){
        $name = str_replace('.', '/', $name);
        $files = array(
            $name,
            $name.'/'.$name
        );

        foreach ($files as $file)
            if (is_file(self::$cache['show'] = file::path($file.'.phtml'))){
                !cache::exists('b/call.php') and self::call('#');
                $funcs = include cache::exists('b/call.php');

                foreach (token_get_all(file_get_contents(self::$cache['show'])) as $token)
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

    static function router($array){
        $q = self::q();
        array_shift($q);

        for ($i = self::len($q); $i >= -1; $i--)
            if (
                ($class = $array[self::q(1, $i)]) or
                ($i == -1 and $class = $array['home'])
            ){
                $q = array_slice($q, ($i == -1 ? 0 : $i));

                if (is_callable($class)){
                    echo call_user_func_array($class, $q);
                    return true;
                }

                if (is_string($class) and class_exists($class, true))
                    $class = new $class;
                elseif (!is_object($class))
                    return false;

                if ($method = array_shift($q)){
                    $method .= ($method[0] == '_' ? '?' : '');
                } else {
                    $method = 'index';
                }

                try {
                    $len = max(0, (b::len($q) - 1));
                    new ReflectionParameter(array($class, $method), $len);
                } catch (ReflectionException $e){
                    if ($method[0] != '_')
                        $method = '_'.$method.'_'.b::len($q);
                }

                ob_start();
                    call_user_func_array(array($class, $method), $q);
                $content = ob_get_clean();

                try {
                    $reflection = new ReflectionClass($class);
                    $reflection = $reflection->getProperty('content');
                    $reflection->setAccessible(true);
                    $reflection->setValue($class, $content);
                } catch (ReflectionException $e){
                    $class->content = $content;
                }

                echo $class;
                return true;
            }
    }

////////////////////////////////////////////////////////////////////////////////

    static function stat($what = ''){
        $stat = array(
            'pgt' => (microtime(true) - self::$cache['stat']),
            'sql' => self::call('sql::stat'),
            'piles' => self::call('piles::stat'),
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

    static function references(){
        $paths = explode(';', self::$path);
        $dir = end($paths).'/lib';
        $result = array();

        foreach (file::dir($dir, '/\.php$/i') as $file => $info){
            if ($info->isDir())
                continue;

            $contents = file_get_contents($file);
            $tokens = token_get_all($contents);

            foreach ($tokens as $k => $v)
                if (is_array($v) and ($v[0] == T_COMMENT or $v[0] == T_DOC_COMMENT))
                    $contents = str_replace($v[1], '', $contents);

            foreach ($tokens as $k => $v)
                if (is_array($v) and $v[0] == T_CLASS){
                    $class = $tokens[$k + 2][1];
                    break;
                }

            preg_match_all('/(\w+)::\w+/s', $contents, $array);
            $array = array_unique($array[1]);
            sort($array);

            foreach ($array as $k => $v)
                if (in_array(strtolower($v), array('self', 'parent', strtolower($class))))
                    unset($array[$k]);
                else
                    $array[$k] = trim($v);

            if ($array){
                $file = substr($file, (self::len($dir) + 1));
                $result[$file.': '.$class] = array_values($array);
            }
        }

        ksort($result);
        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

}