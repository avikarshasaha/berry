<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    ˸�� zloy � �������� <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
$q = b::q(2, 0);
unset($_GET['berry']);
$qs = http_build_query($_GET);

////////////////////////////////////////////////////////////////////////////////

foreach (array('show', 'load', 'data', 'cache') as $dir)
    if (file_exists($path = file::path($dir = $dir.'/'.$q)))
        http::go(basename(str_replace($dir, '', $path)).'/'.$dir.($qs ? '?'.$qs : ''));