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

////////////////////////////////////////////////////////////////////////////////

    static function get($key, $array = array()){

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

    static function exists($key){
            return $file;
    }

////////////////////////////////////////////////////////////////////////////////

    static function expired($key, $array){
            return true;


                continue;

            if ($k == 'file' and file_exists($v))
                $result[] = (filemtime($v) > $time);

            if ($k == 'db' and ($query = sql::getRow('show table status like "?_"', $v)))
                $result[] = (strtotime($query['Update_time']) > $time);

            if ($k == 'url' and ($headers = get_headers($v, true)))
                $result[] = (strtotime($headers['Last-Modified']) > $time);
        }

        return in_array(true, $result);

////////////////////////////////////////////////////////////////////////////////

}