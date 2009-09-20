<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Cache {    static $file;
    protected static $tags;

////////////////////////////////////////////////////////////////////////////////

    static function get($key, $array = array(), $_array = array()){        self::$file = file::path('cache/').$key;

        if (!isset(self::$tags)){            if (!is_file($file = file::path('cache/').'cache.php'))
                arr::export($file, array());

            self::$tags = include $file;
        }

        if (is_object($array))
            return new self($key, $array, $_array);

        $is_array = (substr($key, -4) == '.php');

        if (self::expired($key, $array))
            return ($is_array ? array() : null);

        return ($is_array ? include self::$file : file_get_contents(self::$file));
    }

////////////////////////////////////////////////////////////////////////////////

    static function set($value, $tags = array()){        $name = str_replace(file::path('cache/'), '', self::$file);
        $array = (isset(self::$tags[$name]) ? self::$tags[$name] : array());        self::$tags[$name] = array_merge($array, $tags);

        arr::export(file::path('cache/').'cache.php', self::$tags);
        file::mkdir(dirname(self::$file));
        b::call((is_array($value) ? 'arr::export' : 'file_put_contents'), self::$file, $value);

        return self::$file;
    }

////////////////////////////////////////////////////////////////////////////////

    static function remove($key){        if (!is_array($key)){
            if ($file = self::exists($key))
                return unlink($file);

            return;
        }

        $result = array();

        foreach (self::$tags as $k => $v)
            if (array_intersect($v, $key) and is_file($file = file::path('cache/').$k)){                unset(self::$tags[$k]);
                $result[$k] = unlink($file);
            }

        arr::export(file::path('cache/').'cache.php', self::$tags);
        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    static function exists($key){        if (is_file($file = file::path('cache/').$key))
            return $file;
    }

////////////////////////////////////////////////////////////////////////////////

    static function expired($key, $array){        if (!$file = self::exists($key))
            return true;
        $mtime = filemtime($file);        $result = array();
        foreach ($array as $k => $tmp){            if (!$tmp)
                continue;

            foreach ((array)$tmp as $v){
                if ($k == 'file' and file_exists($v))
                    $result[] = (filemtime($v) > $mtime);

                if ($k == 'db'){                    $tmp = SQL::SKIP;
                    if ($pos = strpos($v, '.')){
                        $tmp = substr($v, 0, $pos);
                        $v = substr($v, ($pos + 1));
                    }

                    $query = sql::row('show table status { from ?_ } like "?_"', $tmp, $v);
                    $result[] = (strtotime($query['Update_time']) > $mtime);
                }

                if ($k == 'url' and ($headers = get_headers($v, true)))
                    $result[] = (strtotime($headers['Last-Modified']) > $mtime);

                if ($k == 'time')
                    $result[] = ((date::time($v) - time()) < (time() - $mtime));
            }
        }

        return in_array(true, $result);    }

////////////////////////////////////////////////////////////////////////////////

    function __construct($key, $object, $array){        $this->key = $key;
        $this->object = $object;
        $this->array = $array;    }

////////////////////////////////////////////////////////////////////////////////

    function __call($method, $params){        if (!self::expired($this->key, $this->array))
            return include self::$file;

        $tags = array();

        if (isset($this->array['tags'])){
            $tags = $this->array['tags'];
        } else {            foreach ($this->array as $k => $v)
                if (is_int($k))
                    $tags[] = $v;        }

        $data = call_user_method_array($method, $this->object, $params);
        self::set($data, $tags);

        return $data;    }

////////////////////////////////////////////////////////////////////////////////

}