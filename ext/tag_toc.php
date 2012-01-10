<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_toc($attr){
    $ref = '';
    $string = '<a name="%s"></a><a href="#%s">%s</a>';

    if (preg_match_all('/<ref>(.*?)<\/ref>/i', $attr['#text'], $match)){
        for ($i = 0, $c = count($match[1]); $i < $c; $i++){
            $id = 'ref-'.($i + 1);
            $ref[] = sprintf($string, $id, '_'.$id, '↑').' '.$match[1][$i];
            $attr['#text'] = str_replace($match[0][$i], sprintf($string, '_'.$id, $id, '<sup>[?]</sup>'), $attr['#text']);
        }

        if ($ref)
            $ref = '<li>'.join('</li><li>', $ref).'</li>';
    }

    $toc = array('*' => '', '#' => '');
    $result = array();
    $last = 0;
    $string = '';
    if (preg_match_all('/<h(\d+)( (.*?))?>(.*)<\/h\\1>/i', $attr['#text'], $match)){
        for ($i = 0, $c = count($match[1]); $i < $c; $i++){
            preg_match('/ id=("|\')(.*?)\\1/', $match[2][$i], $id);

            if (!$last)
                $string = 'h'.$match[1][$i];
            elseif ($last < $match[1][$i])
                $string .= '.h'.$match[1][$i];
            elseif ($last > $match[1][$i])
                $string = substr($string, 0, strrpos($string, '.')).'-h'.$match[1][$i];

            $id = ($id[2] ? $id[2] : 'toc-'.($i + 1));
            $result[$string][] = '<a href="#'.$id.'">'.$match[4][$i].'</a>';
            $attr['#text'] = str_replace($match[0][$i], '<a name="'.$id.'"></a> '.$match[0][$i], $attr['#text']);
            $last = $match[1][$i];
        }

        if ($result){
            $result = arr::assoc($result);

            if (strpos($attr['#text'], '%toc.*') !== false)
                $toc['*'] = _tag_toc($result, 'ul');

            if (strpos($attr['#text'], '%toc.#') !== false)
                $toc['#'] = _tag_toc($result, 'ol');
        }
    }

    return str::format($attr['#text'], compact('toc', 'ref'));
}

////////////////////////////////////////////////////////////////////////////////

function _tag_toc($items, $tag){
    foreach ($items as $item)
        if (is_array($item))
            $result .= '<'.$tag.'>'._tag_toc($item, $tag).'</'.$tag.'>';
        else
            $result .= '<li>'.$item.'</li>';

    return $result;
}
