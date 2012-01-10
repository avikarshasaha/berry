<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function attr_if($attr){    $token = token_get_all('<?php '.$attr['if']);
    $then = preg_split('/<else( ([^>]*))?\/>/i', $attr['#text'], 2);

    for ($i = 1, $c = count($token); $i < $c; $i++){        if (is_array($token[$i]) and $token[$i][0] == T_STRING)
            $result .= '"'.$token[$i][1].'"';
        else
            $result .= (is_array($token[$i]) ? $token[$i][1] : $token[$i]);
    }

    if ($func = create_function('', 'return '.$result.';'))
        $attr['#skip'] = !$func();

    if (isset($then[1])){
        $attr['#text'] = ($attr['#skip'] ? $then[1] : $then[0]);
        unset($attr['#skip']);
    }

    unset($attr['if']);
    return $attr;
}
