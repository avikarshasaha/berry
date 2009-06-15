<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function supadupa_htmlize($attr){
    $rand = rand();
    $tagO = '<htmlize_'.$rand.'>';
    $tagC = '</htmlize_'.$rand.'>';
    $html = $tagO.$attr['#text'].$tagC;
    $html = preg_replace('/<([^\s\/]*)/se', "'<'.str_replace(':', '_', stripslashes('\\1'))", $html);
    $html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$html;
    $dom  = DOMDocument::loadHTML($html);

    $html = str_replace('&#13;', '', $dom->saveXML());
    $html = str_replace(array('<![CDATA[', ']]>'), array('<cdata>', '</cdata>'), $html);
    $html = substr($html, (strpos($html, $tagO) + b::len($tagO)));
    $html = substr($html, 0, strrpos($html, $tagC));

    return ($attr['#is_final'] ? $html : '<htmlize>'.$html.'</htmlize>');
}

////////////////////////////////////////////////////////////////////////////////

function supadupa_skip($attr){
    static $i;

    $i++;
    $_SESSION['supadupa']['skip'][$i] = $attr['#text'];
    return '<!--supadupa[skip]['.$i.']-->';
}

////////////////////////////////////////////////////////////////////////////////

function output_supadupa_skip($output){
    if (preg_match_all('/<!--supadupa\[skip\]\[(.*)\]-->/', $output, $match))
        for ($i = 0, $c = b::len($match[0]); $i < $c; $i++)
            $output = str_replace($match[0][$i], $_SESSION['supadupa']['skip'][$match[1][$i]], $output);

    return $output;
}