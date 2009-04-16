<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
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
    return str::html($value);
}

////////////////////////////////////////////////////////////////////////////////

function type_u($var, $value){
    return urlencode($value);
}

////////////////////////////////////////////////////////////////////////////////

function type_h($var, $value){
    return supadupa_htmlize(array('#text' => $value));
}