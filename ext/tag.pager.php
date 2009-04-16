<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_pager($attr){
    $attr = array_merge(array(
        'this' => '<b>[%i]</b>',
        'list' => '[%i]',
        'type' => 1,
        'href' => '?page=%i',
        'prev' => '←',
        'next' => '→'
    ), $attr);

    if ($attr['count'] <= $attr['limit'])
        return;

    if (!$is_offset = (array_key_exists('offset', $attr) and !array_key_exists('page', $attr)))
        $attr['page'] = ($attr['page'] ? $attr['page'] : 1);

    for ($i = 1, $c = (ceil($attr['count'] / $attr['limit']) + 1); $i < $c; $i++){
        if ($attr['type'] == 1 or $attr['type'] == 3)
            $list = $i;
        elseif ($attr['type'] == 2)
            $list = ($i * $attr['limit'] - $attr['limit'] + 1).'-'.($i * $attr['limit']);

        if ($is_offset){
            $page = ($i == 1 ? 0 : ($i * $attr['limit'] - $attr['limit']));
            $if = ($page == $attr['offset']);
        } else {
            $page = $i;
            $if = ($i == ($attr['page'] ? $attr['page'] : 1));
        }

        $result[] = '<a href="'.str::format($attr['href'], array('i' => $page)).'">'.
                    str::format(($if ? $attr['this'] : $attr['list']), array('i' => $list)).
                    '</a>';
    }

    if (!$result)
        return;

    if (($len = b::len($result)) > $attr['limit'] and $attr['type'] == 3){
        $tmp   = ($is_offset ? ($attr['offset'] / $attr['limit'] + 1) : $attr['page']);
        $start = (($tmp - 4) > 1 ? ($tmp - 4) : 0);
        $stop  = (!$start ? 7 : 7);

        if (($start + $stop) >= $len)
            $start = ($len - $stop);

        $array = $result;
        $array = array_slice($array, $start, $stop);

        if ($start)
            array_unshift($array, reset($result), '...');

        if (($start + $stop + 1) == $len)
            array_push($array, end($result));
        elseif (($start + $stop) < $len)
            array_push($array, '...', end($result));

        $result = $array;
    }

    $prev = ($is_offset ? ($attr['offset'] - $attr['limit']) : ($attr['page'] - 1));
    $next = ($is_offset ? ($attr['offset'] + $attr['limit']) : ($attr['page'] + 1));

    array_unshift($result, ($prev >= 1 ? '<a href="'.str::format($attr['href'], array('i' => $prev)).'">'.$attr['prev'].'</a>' : $attr['prev']));
    array_push($result, ($next <= $page ? '<a href="'.str::format($attr['href'], array('i' => $next)).'">'.$attr['next'].'</a>' : $attr['next']));

    $result = join(($attr['separator'] ? $attr['separator'] : ' '), $result);

    if (!$attr['#text'])
        return $result;

    return tags::parse_lvars($attr, array(
        'pages'   => ceil($attr['count'] / $attr['limit']),
        'current' => max(1, ($is_offset ? ($attr['offset'] / $attr['limit'] + 1) : $attr['page'])),
        'pager'   => $result
    ));
}