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

////////////////////////////////////////////////////////////////////////////////

    static function get($key, $array = array()){        self::$file = file::path('cache/').$key;

        if (!self::expired($key, $array))
            return self::$file;
    }

////////////////////////////////////////////////////////////////////////////////

    static function set($value){
        file::mkdir(dirname(self::$file));
        b::call((!is_scalar($value) ? 'arr::export' : 'file_put_contents'), self::$file, $value);

        return self::$file;
    }

////////////////////////////////////////////////////////////////////////////////

    static function remove($key){
        if ($file = self::exists($key))
            return unlink($file);
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

                if ($k == 'db' and ($query = sql::row('show table status like "?_"', $v)))
                    $result[] = (strtotime($query['Update_time']) > $mtime);

                if ($k == 'url' and ($headers = get_headers($v, true)))
                    $result[] = (strtotime($headers['Last-Modified']) > $mtime);

                if ($k == 'time')
                    $result[] = (date::time($v) > $mtime);
            }
        }

        return in_array(true, $result);    }

////////////////////////////////////////////////////////////////////////////////

}