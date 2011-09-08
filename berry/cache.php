<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Cache {
    static $file;
    protected static $data;
    protected static $scope = array();

////////////////////////////////////////////////////////////////////////////////

    static function get($key, $array = array(), $_array = array()){
        if (!isset(self::$data)){
            if (!is_file($file = file::path('tmp/').'cache.php'))
                arr::export($file, array());

            self::$data = include $file;
        }
        
        if ($file = self::exists($key))
            self::$scope[$key] = self::$file = $file;
        else
            self::$scope[$key] = self::$file = file::path('tmp/').$key;

        if (is_object($array))
            return new self($key, $array, $_array);

        $is_array = (substr($key, -4) == '.php');

        if (self::expired($key, $array))
            return ($is_array ? array() : null);

        unset(self::$scope[$key]);
        return ($is_array ? include self::$file : file_get_contents(self::$file));
    }

////////////////////////////////////////////////////////////////////////////////

    static function get_path($key, $array = array(), $_array = array()){
        if (!isset(self::$data)){
            if (!is_file($file = file::path('tmp/').'cache.php'))
                arr::export($file, array());

            self::$data = include $file;
        }
        
        if ($file = self::exists($key))
            self::$scope[$key] = self::$file = $file;
        else
            self::$scope[$key] = self::$file = file::path('tmp/').$key;

        if (self::expired($key, $array))
            return;

        unset(self::$scope[$key]);
        return self::$file;
    }

////////////////////////////////////////////////////////////////////////////////

    static function set($value, $tags = array()){        $file = end(self::$scope);
        $name = str_replace(file::path('tmp/'), '', $file);
        $tags = array_merge((isset(self::$data[$name]['tags']) ? self::$data[$name]['tags'] : array()), $tags);
        self::$data[$name] = array(
            'file' => $file,
            'time' => time(),
            'tags' => $tags
        );

        arr::export(file::path('tmp/').'cache.php', self::$data);
        file::mkdir(dirname($dir = $file));
        b::call((is_array($value) ? 'arr::export' : 'file_put_contents'), $file, $value);
        array_pop(self::$scope);

        return $dir;
    }

////////////////////////////////////////////////////////////////////////////////

    static function remove($key){
        if (!is_array($key)){
            if ($file = self::exists($key))
                return unlink($file);

            return;
        }

        $result = array();

        foreach (self::$data as $k => $v)
            if (array_intersect($v, $key) and is_file($v['file'])){
                $result[$k] = unlink($v['file']);
                unset(self::$data[$k]);
            }

        arr::export(file::path('tmp/').'cache.php', self::$data);
        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    static function exists($key){
        if (self::$data[$key])
            return self::$data[$key]['file'];
    }

////////////////////////////////////////////////////////////////////////////////

    static function expired($key, $array){
        if (!self::exists($key))
            return true;

        $mtime = self::$data[$key]['time'];
        $result = array();

        foreach ($array as $k => $tmp){
            if (!$tmp)
                continue;

            foreach ((is_array($tmp) ? $tmp : array($tmp)) as $v){
                if ($k == 'file' and file_exists($v))
                    $result[] = (filemtime($v) > $mtime);

                if ($k == 'db'){                    if (is_object($v)){                        $class = get_class($v);
                        $vars = get_class_vars($class);
                        $v = ($vars['table'] ? $vars['table'] : inflector::tableize($class));
                    }

                    if ($pos = strpos($v, '.')){
                        $tmp = substr($v, 0, $pos);
                        $v = substr($v, ($pos + 1));
                    } else {                        $tmp = SQL::SKIP;
                    }

                    $query = sql::query('show table status { from ?_ } like "?_"', $tmp, $v)->fetch_row();
                    $result[] = (strtotime($query['Update_time']) > $mtime);
                }

                if ($k == 'url' and ($headers = get_headers($v, true)))
                    $result[] = (strtotime($headers['Last-Modified']) > $mtime);

                if ($k == 'time')
                    $result[] = ((date::time($v) - time()) < (time() - $mtime));
            }
        }

        return in_array(true, $result);
    }

////////////////////////////////////////////////////////////////////////////////

    function __construct($key, $object, $array){
        $this->key = $key;
        $this->object = $object;
        $this->array = (array)$array;
    }

////////////////////////////////////////////////////////////////////////////////

    function __call($method, $params){
        if (!self::expired($this->key, $this->array))
            return include self::$file;

        $tags = array();

        if (isset($this->array['tag'])){
            $tags = (array)$this->array['tag'];
        } else {
            foreach ($this->array as $k => $v)
                if (is_int($k))
                    $tags[] = $v;
        }

        $data = call_user_func_array(array($this->object, $method), $params);
        self::set($data, $tags);

        return $data;
    }

////////////////////////////////////////////////////////////////////////////////

}