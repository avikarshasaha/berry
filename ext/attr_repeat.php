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
        $tag = piles::fill($tag['#tag'], $tag);
        
        if (!is_array($attr['repeat'])){
            $attr['#skip'] = piles::show($tag);  
        } else {
            foreach ($attr['repeat'] as $k => $v)
                $attr['#skip'] .= piles::show($tag, compact('k', 'v'));
        }
    } catch (Piles_Except $e){
        $attr['#skip'] = $e;
    }

    unset($attr['repeat']);
    return $attr;
}
