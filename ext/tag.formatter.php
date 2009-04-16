<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_formatter($attr){
	switch (strtolower($attr['format'])){
		case 'html+br':
		    $attr['#text'] = nl2br($attr['#text']);
		break;

		case 'text':
		    $attr['#text'] = str::html($attr['#text']);
		break;

		case 'text+br':
		    $attr['#text'] = str::html($attr['#text']);
		    $attr['#text'] = nl2br($attr['#text']);
		break;

		case 'textile':
		    $attr['#text'] = formatter::textile($attr['#text'], $attr);
		break;

		case 'markdown':
		    $attr['#text'] = formatter::markdown($attr['#text']);
		break;

		case 'bbcode':
		    $attr['#text'] = formatter::bbcode($attr['#text']);
		break;

		case 'wacko':
		    $attr['#text'] = formatter::wacko($attr['#text']);
		break;

		case 'jevix':
		    $attr['#text'] = formatter::jevix($attr['#text']);
		break;	}

    return $attr['#text'];
}