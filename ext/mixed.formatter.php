<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_formatter($attr){
    foreach ($attr as $k => $v)
        if (b::function_exists($func = 'formatter_'.$k))
            $attr['#text'] = b::call($func, $attr['#text']);

    return $attr['#text'];
}

////////////////////////////////////////////////////////////////////////////////

function formatter_br($text){
    return nl2br($text);
}

////////////////////////////////////////////////////////////////////////////////

function formatter_text($text){
    return str::unhtml($text);
}

////////////////////////////////////////////////////////////////////////////////

function formatter_markdown($text){
    return new Markdown($text);
}