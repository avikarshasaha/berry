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
    (!sql::is_valid() and b::load('offline')) or
    b::load(b::q(1, 2, '.')) or b::load(b::q(1)) or
    b::load();
html::block('body', ob_get_clean());

////////////////////////////////////////////////////////////////////////////////

$output = preg_replace('/<block_body(\s+)?\/>/i', join('', html::block('body')), piles::show());
$output = hook::get('output', $output);
$output = preg_replace('/<block_(.*?)(\s+)?\/>/ie', "join('', html::block('\\1'))", $output);
$output = preg_replace('/(href|src)=("|\')\~\/(.*)\\2/iU', '\\1=\\2'.b::q(0).'/~/\\3\\2', $output);

////////////////////////////////////////////////////////////////////////////////

if (stripos($output, '</form>'))
    $output = new FormPersistent($output);

////////////////////////////////////////////////////////////////////////////////

$_SESSION['berry'] = array('addr' => $_SERVER['REMOTE_ADDR'], 'agent' => $_SERVER['HTTP_USER_AGENT']);

////////////////////////////////////////////////////////////////////////////////

return $output;