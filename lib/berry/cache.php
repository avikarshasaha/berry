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
    static $md5;

////////////////////////////////////////////////////////////////////////////////

    function get($key, $check = array()){
        foreach ($check as $func => $value)
            if ($value and method_exists('cache', 'check_'.$func))
                $time += call_user_func(array('cache', 'check_'.$func), $value);

        self::$file = file::path('cache/').$key.'.md5';
        self::$md5  = md5($time);

        if (($file = self::exists($key)) and file_get_contents(self::$file) == self::$md5)
            return $file;
    }

////////////////////////////////////////////////////////////////////////////////

    function set($value){        file::mkdir(dirname(self::$file));

        $func = (!is_scalar($value) ? 'arr::export' : 'file_put_contents');

        b::call($func, substr(self::$file, 0, -4), $value);
        file_put_contents(self::$file, self::$md5);
        return self::$file;
    }

////////////////////////////////////////////////////////////////////////////////

    function remove($key){
        if (!$file = self::exists($key))
            return;

        unlink($file);
        unlink($file.'.md5');
    }

////////////////////////////////////////////////////////////////////////////////

    function exists($key){        $dir = file::path('cache');

        if (is_file($file = $dir.'/'.$key) and is_file($dir.'/'.$key.'.md5'))
            return $file;
    }

////////////////////////////////////////////////////////////////////////////////

    function check_file($check){        foreach ((array)$check as $file)
            if (file_exists($file))
                $time += filemtime($file);
        return $time;
    }

////////////////////////////////////////////////////////////////////////////////

    function check_DB($check){
        foreach ((array)$check as $table)
            if ($query = sql::getRow('show table status like "?_"', $table))
                $time += strtotime($query['Update_time']);

        return $time;
    }

////////////////////////////////////////////////////////////////////////////////

    function check_URL($check){
        foreach ((array)$check as $url)
            if ($headers = get_headers($url, true))
                $time += strtotime($headers['Last-Modified']);

        return $time;
    }

////////////////////////////////////////////////////////////////////////////////

}