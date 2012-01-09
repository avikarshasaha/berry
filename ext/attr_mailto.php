<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function attr_mailto($attr){
	for ($i = 0, $c = count($attr['mailto']); $i < $c; $i++)
	    $result .= '&#'.ord($attr['mailto'][$i]).';';

    $attr['href'] = '&#109;&#97;&#105;&#108;&#116;&#111;&#58;'.$result;
    $attr['#text'] = ((!$attr['#text'] or $attr['#text'] == $attr['mailto']) ? $result : $attr['#text']);

    unset($attr['mailto']);
	return $attr;
}