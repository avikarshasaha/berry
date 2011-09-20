<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function piles_var_q($string){
    return b::call('*b::q', explode('.', $string, 3));
}

////////////////////////////////////////////////////////////////////////////////

function piles_var_config($string){
    return ($string ? b::config($string) : b::config());
}

////////////////////////////////////////////////////////////////////////////////

function piles_var_lang($string){
    return ($string ? b::lang($string) : b::lang());
}

////////////////////////////////////////////////////////////////////////////////

function piles_func_to_array($value){
    return (array)$value;
}

////////////////////////////////////////////////////////////////////////////////

function piles_func_to_bool($value){
    return ($value ? 1 : 0);
}

////////////////////////////////////////////////////////////////////////////////

function piles_func_to_float($value){
    return (float)$value;
}

////////////////////////////////////////////////////////////////////////////////

function piles_func_to_int($value){
    return (int)$value;
}

////////////////////////////////////////////////////////////////////////////////

function piles_func_to_s($value){
    return str::unhtml($value);
}

////////////////////////////////////////////////////////////////////////////////

function piles_func_to_html($value){
    return str::html($value);
}