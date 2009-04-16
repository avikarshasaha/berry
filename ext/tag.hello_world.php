<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_hello_world($attr){	$attr = array_merge(array(
	    'name'  => 'The Great Cornholio',
	    '#text' => 'Fire! Fire!'
	), $attr);

	return replace('Hello World! My name is %name and I say "%#text".', $attr);
}