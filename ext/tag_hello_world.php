<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_hello_world($attr){    $attr = array_merge(array(
        'name'  => 'The Great Cornholio',
        '#text' => 'Fire! Fire!'
    ), $attr);

    return str::format('Hello World! My name is %name and I say "%#text".', $attr);
}
