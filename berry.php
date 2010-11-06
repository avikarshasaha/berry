<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
ob_start();
    b::load(b::q(1, 2, '.')) or b::load(b::q(1)) or b::load();
html::block('body', ob_get_clean());

////////////////////////////////////////////////////////////////////////////////

$content_type = 'text/html';

foreach (headers_list() as $header)
    if (substr($header, 0, 13) == 'Content-Type:'){
        if ($pos = strpos($header, ';'))
            $header = substr($header, 0, $pos);

        $content_type = trim(strtolower(substr($header, 13)));
        break;
    }

////////////////////////////////////////////////////////////////////////////////

if ($content_type != 'text/html'){    $output = join('', html::block('body'));

    if (substr($content_type, 0, 5) != 'text/')        return $output;
} else {    $output = preg_replace('/<block_body(\s+)?\/>/i', join('', html::block('body')), piles::show());
}

////////////////////////////////////////////////////////////////////////////////

$output = hook::get('output', $output);
$output = preg_replace('/<block(_|:|-|\.)(.*?)(\s+)?\/>/ie', "join('', html::block('\\2'))", $output);
$output = preg_replace('/(href|src)=("|\'|)\~\/(.*)\\2/iU', '\\1=\\2'.b::q(0).'/~/\\3\\2', $output);
$output = preg_replace('/(\W)url(\s+)?\(("|\'|)\~\/(.*)\\3\)/i', '\\1url\\2(\\3'.b::q(0).'/~/\\4\\3)', $output);
$output = html::form_persister($output);

////////////////////////////////////////////////////////////////////////////////

return $output;