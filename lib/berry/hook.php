<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Hook {
    static $hook = array();

////////////////////////////////////////////////////////////////////////////////

    function get($key, $value, $params = array()){
        if (!self::exists($key))
            return $value;

        asort(self::$hook[$key]);

        foreach (self::$hook[$key] as $hook => $sort)
            $value = b::call($hook, $value, $params, compact('key', 'sort'));

        return $value;
    }

////////////////////////////////////////////////////////////////////////////////

    function set($key, $value, $sort = 50){
        self::$hook[$key][$value] = $sort;
        return $value;
    }

////////////////////////////////////////////////////////////////////////////////

    function remove($key){
        if (self::exists($key))
            unset(self::$hook[$key]);
    }

////////////////////////////////////////////////////////////////////////////////

    function exists($key){
        return isset(self::$hook[$key]);
    }

////////////////////////////////////////////////////////////////////////////////

}