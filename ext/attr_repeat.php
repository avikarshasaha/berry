<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function attr_repeat($attr){
    try {
        $tag = $attr;
        unset($tag['repeat']);

        $html .= '<? foreach ($array as $k => $v){ ?>';
        $html .= piles::fill($tag['#tag'], $tag);
        $html .= '<? } ?>';

        if (is_array($attr['repeat']))
            $attr['#skip'] = piles::show($html, array(
                'array' => $attr['repeat']
            ));
        else
            $attr['#skip'] = '';
    } catch (Piles_Except $e){
        $attr['#skip'] = $e;
    }

    unset($attr['repeat']);
    return $attr;
}
