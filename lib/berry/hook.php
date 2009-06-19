<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Hook {
    static $hook = array();

////////////////////////////////////////////////////////////////////////////////

    static function get($key, $value, $params = array()){
        if (!self::exists($key))
            return $value;

        asort(self::$hook[$key]);

        foreach (self::$hook[$key] as $hook => $sort)
            $value = b::call($hook, $value, $params, compact('key', 'sort'));

        return $value;
    }

////////////////////////////////////////////////////////////////////////////////

    static function set($key, $value, $sort = 50){
        self::$hook[$key][$value] = $sort;
        return $value;
    }

////////////////////////////////////////////////////////////////////////////////

    static function remove($key){
        if (self::exists($key))
            unset(self::$hook[$key]);
    }

////////////////////////////////////////////////////////////////////////////////

    static function exists($key){
        return isset(self::$hook[$key]);
    }

////////////////////////////////////////////////////////////////////////////////

}