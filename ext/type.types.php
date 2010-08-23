<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function type_a($value){    return (array)$value;}

////////////////////////////////////////////////////////////////////////////////

function type_b($value){
    return ($value ? 1 : 0);
}

////////////////////////////////////////////////////////////////////////////////

function type_e($value){
    return mysql_real_escape_string($value);
}

////////////////////////////////////////////////////////////////////////////////

function type_f($value){
    return (float)$value;
}

////////////////////////////////////////////////////////////////////////////////

function type_i($value){
    return (int)$value;
}

////////////////////////////////////////////////////////////////////////////////

function type_s($value){
    return str::unhtml($value);
}

////////////////////////////////////////////////////////////////////////////////

function type_u($value){
    return urlencode($value);
}