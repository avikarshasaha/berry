<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
if ($_POST['test'] and $_SESSION['attr']['check']){    if (!check::is_valid($_SESSION['attr']['check'], $_POST))
        html::msg('e', 'Форма не прошла валидацию.', 1);
    else
        html::msg('i', 'Форма прошла валидацию.');
}

////////////////////////////////////////////////////////////////////////////////

echo b::show('home');