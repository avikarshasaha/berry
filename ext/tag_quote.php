<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_quote($attr){
    $attr['class'] .= ($attr['class'] ? ' ' : '').'quote';

    if ($attr['cite']){
        $pos = strpos($attr['cite'], 'http://');
        $href = substr($attr['cite'], $pos);
        $text = substr($attr['cite'], 0, $pos);
        $text = ($text ? $text : $href);

        if (is_int($pos))
            $attr['cite'] = compact('href', 'text');
        else
            unset($attr['cite']);
    }

    return piles::show('ext.tag_quote', compact('attr'));
}
