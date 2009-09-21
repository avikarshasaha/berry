<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
$q = b::q(2, 0);
$qs = http_build_query($_GET);

////////////////////////////////////////////////////////////////////////////////

foreach (array('show', 'load', 'data') as $dir)
    if ($path = file::path($dir = $dir.'/'.$q))
        http::go(basename(str_replace($dir, '', $path)).'/'.$dir.($qs ? '?'.$qs : ''));