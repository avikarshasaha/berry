<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class YAML {////////////////////////////////////////////////////////////////////////////////

    function load($input){
        if (!$input or (is_file($input) and !filesize($input)))
            return array();
        return spyc::YAMLload($input);    }

////////////////////////////////////////////////////////////////////////////////

    function dump($array, $indent = 4, $wordwrap = false){
        if (!$array or !is_array($array))
            return '';

        return spyc::YAMLdump($array, $indent, $wordwrap);
    }

////////////////////////////////////////////////////////////////////////////////
}