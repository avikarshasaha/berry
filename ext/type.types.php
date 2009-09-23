<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function type_a($var, $value){    return tags::serialize((array)$value);}

////////////////////////////////////////////////////////////////////////////////

function type_b($var, $value){
    return (bool)$value;
}

////////////////////////////////////////////////////////////////////////////////

function type_e($var, $value){
    return mysql_real_escape_string($value);
}

////////////////////////////////////////////////////////////////////////////////

function type_f($var, $value){
    return (float)$value;
}

////////////////////////////////////////////////////////////////////////////////

function type_i($var, $value){
    return (int)$value;
}

////////////////////////////////////////////////////////////////////////////////

function type_s($var, $value){
    return tags::unhtml($value);
}

////////////////////////////////////////////////////////////////////////////////

function type_u($var, $value){
    return urlencode($value);
}

////////////////////////////////////////////////////////////////////////////////

function type_h($var, $value){
    return supadupa_htmlize(array('#text' => $value));
}