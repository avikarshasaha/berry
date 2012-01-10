<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_msg($attr){
	if (isset($attr['#text'])){
        html::msg($attr['id'], $attr['#text']);
        return;
    }

    if ($messages = html::msg($attr['id']))
        return piles::show('ext.tag_msg.'.$attr['id'], compact('messages'));
}
