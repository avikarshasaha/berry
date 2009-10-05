<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
$config = b::config();
//$member = b::call('auth', http::cookie('aid'), http::cookie('password'));

////////////////////////////////////////////////////////////////////////////////

ob_start();
    (!sql::is_valid() and b::load('offline')) or
    b::load(b::q(1, 2, '.')) or b::load(b::q(1)) or
    b::load();
html::block('body', ob_get_clean());

////////////////////////////////////////////////////////////////////////////////

$output = preg_replace('/<block(_|\.|:|-)body(\s+)?\/>/ie', "tags::parse(join('', html::block('body')))", b::show());
$output = hook::get('output', tags::parse($output, true));
$output = preg_replace('/<block(_|\.|:|-)(.*?)(\s+)?\/>/ie', "tags::parse(join('', html::block('\\2')), true)", $output);

////////////////////////////////////////////////////////////////////////////////

if (stripos($output, '</form>'))
    $output = new FormPersistent($output);

////////////////////////////////////////////////////////////////////////////////

$_SESSION['berry'] = array('addr' => $_SERVER['REMOTE_ADDR'], 'agent' => $_SERVER['HTTP_USER_AGENT']);

////////////////////////////////////////////////////////////////////////////////

return $output;