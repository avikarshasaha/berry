<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_typo_ru($attr){
    $content = '<span>'.$attr['#text'].'</span>';
    $content = typo_ru($content);
    $content = substr($content, 6, -7);

    return $content;
}

////////////////////////////////////////////////////////////////////////////////

function typo_ru($content){    $replace = array(
        'string' => array(
            '(c)'  => '©',
            '(C)'  => '©',
            '(r)'  => '®',
            '(R)'  => '®',
            '(tm)' => '™',
            '(TM)' => '™',

            '+-'   => '±',
            '+/-'  => '±',
            '==='  => '≡',
            '~='   => '≈',
            '!='   => '≠',
            '<='   => '≤',
            '>='   => '≥',

            '%%'   => '‰',
            '1/2'  => '½',
            '1/4'  => '¼',
            '3/4'  => '¾',

            '<->'  => '↔',
            '<-'   => '←',
            '->'   => '→',

            '...'  => '…',
            "'"    => '’'
        ),

        'regexp' => array(
            '/\-([0-9]+)/i' => '–\\1',
            '/(\W)(\s+)?-\s/' => '\\1&nbsp;— ',
            '/(\d+)\s{0,}[x|х]+\s{0,}(\d+)/i' => '\\1×\\2',
            '/([а-я]+)\-([а-я]+)/iu' => '<nobr>\\1-\\2</nobr>',
            '/([а-я])!([а-я])/iu' => '\\1́\\2'
        )
    );

    $content = preg_replace('/<(code|script|style|notypo)( ([^>]*))?>(.*?)<\/\\1>/ies', "'¬'.base64_encode(stripslashes('<\\1\\2>'.'\\4'.'</\\1>')).'¬*'", $content);
    $content = preg_replace('/<([a-z]+([a-z\.:-]+)?[^>]*)>/ie', "'¬'.base64_encode(stripslashes('<\\1>')).'¬*'", $content);
    $content = preg_replace('/(\$|\#|\%)(\w+)?\{(.*)\}/Use', "'¬'.base64_encode(stripslashes('\\1\\2{'.'\\3'.'}')).'¬*'", $content);
    $content = preg_replace('/¬([^¬]*)¬\*"([^"]*)"<\/([^>]*)>/s', '"¬\\1¬*\\2</\\3>"', $content);
    $content = preg_replace('/([>(\s\W])(")([^"]*)([^\s"(])(")/', '\\1«\\3\\4»', $content);

    if (preg_match_all('/"(.*)"/U', $content, $match)){
        for ($i = 0, $c = b::len($match[0]); $i < $c; $i++)
            $match[2][$i] = '«'.str_replace(array('«', '»'), array('„', '“'), $match[1][$i]).'»';

        $content = str_replace($match[0], $match[2], $content);
    }

    $content = strtr($content, $replace['string']);
    $content = preg_replace(array_keys($replace['regexp']), array_values($replace['regexp']), $content);

    if (preg_match_all('/¬(.*)¬\*/U', $content, $match))        $content = strtr($content, array_combine($match[0], array_map('base64_decode', $match[1])));

    return $content;
}