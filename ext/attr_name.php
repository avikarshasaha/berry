<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function attr_name($attr){
    if (
        check::$errors[piles::name2var($attr['name'])] and
        strpos($attr['class'], 'check_error') === false
    )
        $attr['class'] .= ($attr['class'] ? ' ' : '').'check_error';

    if (!array_key_exists('id', $attr))
        $attr['id'] = $attr['name'];

    return $attr;
}
