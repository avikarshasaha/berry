<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function attr_oddeven($attr){	static $i = 0;

	$oddeven = explode(' ', $attr['oddeven']);
    $attr['class'] .= ($attr['class'] ? $attr['class'].' ' : '').$oddeven[$i++];

    if ($i == count($oddeven))
        $i = 0;

    unset($attr['oddeven']);
	return $attr;
}