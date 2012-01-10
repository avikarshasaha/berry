<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Hook {
    static $hook = array();

////////////////////////////////////////////////////////////////////////////////

    static function get($key, $value, $params = array()){
        if (!self::exists($key))
            return $value;

        asort(self::$hook[$key]);

        foreach (self::$hook[$key] as $func => $sort)
            $value = b::call($func, $value, $params, compact('key', 'sort'));

        return $value;
    }

////////////////////////////////////////////////////////////////////////////////

    static function set($key, $value, $sort = 50){
        self::$hook[$key][$value] = $sort;
        return $value;
    }

////////////////////////////////////////////////////////////////////////////////

    static function remove($key, $func = ''){        if (!$func and self::exists($key))
            unset(self::$hook[$key]);
        if ($func and self::exists($key, $func))
            unset(self::$hook[$key][$func]);
    }

////////////////////////////////////////////////////////////////////////////////

    static function exists($key, $func = ''){        if (!$func)
            return isset(self::$hook[$key]);

        return isset(self::$hook[$key][$func]);
    }

////////////////////////////////////////////////////////////////////////////////

}
