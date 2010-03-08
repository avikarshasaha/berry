<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
new JsHttpRequest('utf-8');
$_GET['berry'] = substr($_GET['berry'], 5);

////////////////////////////////////////////////////////////////////////////////

if (
    ($_GET['call'] and !isset($_SESSION['ajax'][$_GET['call']])) or
    (!check::is_valid_post() and !check::is_valid_files())
)
    exit;

////////////////////////////////////////////////////////////////////////////////

ob_start();
    if ($_GET['call'])
        echo b::call($_GET['call'], $_POST);
    else
        b::load(b::q(1, 2, '.')) or b::load(b::q(1));
$output = ob_get_clean();

////////////////////////////////////////////////////////////////////////////////

$output = hook::get('output', $output);
$output = preg_replace('/<block_(.*?)(\s+)?\/>/ie', "join('', html::block('\\1'))", $output);
$output = new FormPersistent($output);

////////////////////////////////////////////////////////////////////////////////

echo $output;
exit;