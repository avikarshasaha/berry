<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_typo_ru($attr){
    return typo_ru($attr['#text']);
}

////////////////////////////////////////////////////////////////////////////////

function typo_ru($content, $skip = array()){    $skip = array_merge(array('code', 'script', 'style', 'notypo'), $skip);    $replace = array(
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
            '/(а|е|и|о|у|ы|э|ю|я)`/iu' => '\\1́'
        )
    );

    if (preg_match_all('/<('.join('|', $skip).')( ([^>]*))?>(.*?)<\/\\1>/is', $content, $skip))
        for ($i = 0, $c = b::len($skip[0]); $i < $c; $i++)
            $content = str_replace($skip[0][$i], '<!--typo_ru['.$i.']-->', $content);

    $content = '<span>'.$content.'</span>';
    $content = preg_replace('/<!--(.*?)-->/ies', "'¬'.base64_encode(stripslashes('<!--\\1-->')).'¬*'", $content);
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

    if (preg_match_all('/¬(.*)¬\*/U', $content, $match))
        $content = strtr($content, array_combine($match[0], array_map('base64_decode', $match[1])));

    foreach ($skip[0] as $k => $v)
        $content = str_replace('<!--typo_ru['.$k.']-->', $v, $content);

    return substr($content, 6, -7);
}