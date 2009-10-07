<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Attr {

////////////////////////////////////////////////////////////////////////////////

    static function merge(){        $args  = func_get_args();
        $allow = array_shift($args);

        foreach ($args as $attr)
            $allow = array_merge($allow, array_intersect_key($attr, $allow));
        return $allow;    }

////////////////////////////////////////////////////////////////////////////////

    static function filter($attr, $allow = array()){
        foreach ($attr as $k => $v)
            if ($k[0] == '#')
                unset($attr[$k]);

        if ($allow)
            $attr = array_intersect_key($attr, array_flip($allow));

        return $attr;
    }

////////////////////////////////////////////////////////////////////////////////

    static function group($group, $attr){
        if (is_array($attr[$group]))
            return $attr[$group];

        $result = array();

        foreach ($attr as $k => $v)
            if (substr($k, 0, (strlen($group) + 1)) == $group.'_')
                $result[substr($k, (strlen($group) + 1))] = $v;

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    static function normalize($attr, $symbols = true){
        if (!is_array($attr))
            return array();

        foreach ($attr as $k => $v){
            unset($attr[$k]);
            $n = str_replace(array(':', '-', '.'), '_', $k);
            $orig[$n] = $k;
            $attr[$n] = tags::unsux($v);
        }

        foreach ($attr as $k => $v)
            if (b::function_exists($func = 'attr_'.$k))
                $attr = b::call($func, $attr);

        if ($symbols)
            foreach ($attr as $k => $v)
                if (($n = $orig[$k]) and $n != $k){
                    $attr[$n] = $v;
                    unset($attr[$k]);
                }

        return $attr;
    }

////////////////////////////////////////////////////////////////////////////////

}