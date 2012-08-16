<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
Phar::mapPhar('berry.phar');
include 'phar://berry.phar/berry/b.php';

if (php_sapi_name() == 'cli'){
    printf('Berry Framework version %s', b::VERSION);
    exit;
}

__HALT_COMPILER();
?>