<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class YAML {////////////////////////////////////////////////////////////////////////////////

    function load($input){
        if (!$input or (is_file($input) and !filesize($input)))
            return array();
        return Spyc::YAMLload($input);    }

////////////////////////////////////////////////////////////////////////////////

    function dump($array, $indent = 4, $wordwrap = false){
        if (!$array or !is_array($array))
            return '';

        return Spyc::YAMLdump($array, $indent, $wordwrap);
    }

////////////////////////////////////////////////////////////////////////////////
}