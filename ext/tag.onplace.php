<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_onplace($attr){
    $_SESSION['tag']['onplace'] = $attr;
    $attr['#text'] = preg_replace('/<!--(.*?)-->/s', '', $attr['#text']);
    return piles::show(piles::fill('span', array_merge($attr, array('id' => 'ajax[onplace]'))));
}

////////////////////////////////////////////////////////////////////////////////

function onplace($data){
    if ($attr = $_SESSION['tag']['onplace']){        if ($_SESSION['attr']['check'])
            check::is_valid($_SESSION['attr']['check'], $data);
        $attr['#text'] = strtr($attr['#text'], array('<!--' => '', '-->'  => ''));
        return piles::show(tag_onplace($attr));
    }
}