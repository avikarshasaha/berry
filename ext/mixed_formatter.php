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

function formatter_html($text){
    return str::html($text);
}

////////////////////////////////////////////////////////////////////////////////

function formatter_markdown($text){
    return new Markdown($text);
}

////////////////////////////////////////////////////////////////////////////////

function formatter_urls($text){
    if (preg_match_all('/(<|&lt;)((\w+):\/\/.*)(>|&gt;)/i', $text, $m))
        for ($i = 0, $c = count($m[0]); $i < $c; $i++){
            $url = $m[2][$i];
            $text = str_replace($m[0][$i], sprintf('<a href="%s" rel="nofollow">%s</a>', $url, $url), $text);
        }

    return $text;
}