<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_block($attr){
    if (!isset($attr['#text']))
        return join('', html::block($attr['id']));

    html::block($attr['id'], $attr['#text'], (is_numeric($attr['sort']) ? $attr['sort'] : 50));
}