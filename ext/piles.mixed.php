<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function var_q($string){
    return b::call('*b::q', explode('.', $string, 3));
}

////////////////////////////////////////////////////////////////////////////////

function var_config($string){
    return ($string ? b::config($string) : b::config());
}

////////////////////////////////////////////////////////////////////////////////

function var_lang($string){
    return ($string ? b::lang($string) : b::lang());
}

////////////////////////////////////////////////////////////////////////////////

function method_to_a($value){
    return (array)$value;
}

////////////////////////////////////////////////////////////////////////////////

function method_to_b($value){
    return ($value ? 1 : 0);
}

////////////////////////////////////////////////////////////////////////////////

function method_to_f($value){
    return (float)$value;
}

////////////////////////////////////////////////////////////////////////////////

function method_to_i($value){
    return (int)$value;
}

////////////////////////////////////////////////////////////////////////////////

function method_to_s($value){
    return str::unhtml((string)$value);
}

////////////////////////////////////////////////////////////////////////////////

function method_to_html($value){
    return str::html($value);
}