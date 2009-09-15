<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
if ($_POST['test'] and $_SESSION['attr']['check']){
    foreach (check::is_valid($_SESSION['attr']['check'], $_POST) as $error)
        html::msg($error['type'], $error['need']);

    if (!check::is_valid_post('test'))
        html::msg('e', 'форм не прошла валидацию.', 1);
    else
        html::msg('i', 'форм прошла валидацию.');
}

////////////////////////////////////////////////////////////////////////////////

echo b::show('home');