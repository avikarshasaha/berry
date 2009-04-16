<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Attr {

////////////////////////////////////////////////////////////////////////////////

    function merge(){
        $allow = array_shift($args);

        foreach ($args as $attr)
            $allow = array_merge($allow, array_intersect_key($attr, $allow));


////////////////////////////////////////////////////////////////////////////////

    function filter($attr, $allow = array()){
        foreach ($attr as $k => $v)
            if ($k[0] == '#')
                unset($attr[$k]);

        if ($allow)
            $attr = array_intersect_key($attr, array_flip($allow));

        return $attr;
    }

////////////////////////////////////////////////////////////////////////////////

    function group($group, $attr){
        if (is_array($attr[$group]))
            return $attr[$group];

        $result = array();

        foreach ($attr as $k => $v)
            if (substr($k, 0, (strlen($group) + 1)) == $group.'_')
                $result[substr($k, (strlen($group) + 1))] = $v;

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function normalize($attr, $symbols = true){
        if (!is_array($attr))
            return array();

        foreach ($attr as $k => $v){
            unset($attr[$k]);
            $n = str_replace(array(':', '-', '.'), '_', $k);
            $orig[$n] = $k;
            $attr[$n] = tags::unsux($v);
        }

        foreach ($attr as $k => $v)
            if (function_exists($func = 'attr_'.$k))
                $attr = call_user_func($func, $attr);

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