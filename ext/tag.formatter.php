<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_formatter($attr){
	$format = strtolower($attr['format']);

    if ($format == 'text+br')
      $attr['#text'] = nl2br(str::unhtml($attr['#text']));
    elseif ($format == 'text')
      $attr['#text'] = str::unhtml($attr['#text']);
	elseif ($format == 'html+br')
      $attr['#text'] = nl2br($attr['#text']);
    elseif ($format != 'html' and method_exists('formatter', $format))
        $attr['#text'] = b::call('formatter::'.$format, $attr['#text']);

    return $attr['#text'];
}