<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
try {    if (!check::is_valid($_SESSION['attr']['check'], arr::merge($_POST, $_FILES)))
        throw new Check_Except($_SESSION['attr']['check']);} catch (Check_Except $e){    $error = (string)$e;
    $errors = $e->message;}

////////////////////////////////////////////////////////////////////////////////

if (b::q(2) == 'ajax')
    echo $error;
else
    echo piles::show('home', compact('error', 'errors'));