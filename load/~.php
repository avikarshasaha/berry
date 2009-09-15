<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
$file = '/show/'.b::q(2, 0);

if (is_file(b::$path[1].$file))
    $file = basename(b::$path[1]).$file;
else
    $file = basename(b::$path[0]).$file;

////////////////////////////////////////////////////////////////////////////////

http::go($file);