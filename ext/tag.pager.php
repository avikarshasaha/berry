<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_pager($attr){
    $attr = array_merge(array(
        'href' => 'page=%i',
    ), $attr);

    if ($array = _tag_pager($attr))
        return piles::show('ext.tag.pager.index', $array);
}

////////////////////////////////////////////////////////////////////////////////

function tag_pager_blocks($attr){
    $attr = array_merge(array(
        'href' => 'page=%i',
    ), $attr);

    if ($array = _tag_pager($attr))
        return piles::show('ext.tag.pager.blocks', $array);
}

////////////////////////////////////////////////////////////////////////////////

function tag_pager_simple($attr){
    $attr = array_merge(array(
        'href' => 'page=%i',
    ), $attr);

    if (!$array = _tag_pager($attr))
        return;

    if (($len = b::len($array['pages'])) > $attr['limit']){
        $tmp = (array_key_exists('offset', $attr) ? ($attr['offset'] / $attr['limit'] + 1) : $attr['page']);
        $limit = 7;
        $offset = (($tmp - 4) > 1 ? ($tmp - 4) : 0);
        $offset = (($offset + $limit) >= $len ? ($len - $limit) : $offset);

        $pages = $array['pages'];
        $array['pages'] = array(
            array(),
            array_slice($pages, $offset, $limit, true),
            array()
        );

        if (!$offset)
            $array['pages'][1] = (array_slice($pages, 0, 2, true) + array_slice($pages, $offset, ($limit + 1), true));
        if ($offset <= 2)
            $array['pages'][1] = (array_slice($pages, 0, 2, true) + $array['pages'][1]);
        elseif ($offset)
            $array['pages'][0] += array_slice($pages, 0, 1, true);

        if (($offset + $limit + 2) >= $len){
            $array['pages'][1] += array_slice($pages, -2, $limit, true);
        } elseif (($offset + $limit) < $len){
            $value = end($pages);
            $array['pages'][2][key($pages)] = $value;
        }
    } else {
        $array['pages'] = array(1 => $array['pages']);
    }

    return piles::show('ext.tag.pager.simple', $array);
}

////////////////////////////////////////////////////////////////////////////////

function _tag_pager($attr){    if ($attr['count'] < 1 or $attr['limit'] < 1 or $attr['count'] <= $attr['limit'])
        return;

    if (!array_key_exists('offset', $attr))
        $attr['page'] = ($attr['page'] ? $attr['page'] : 1);

    parse_str($_SERVER['QUERY_STRING'].'&'.$attr['href'], $query);
    array_walk_recursive($query, create_function('&$v', 'if ($v === "") $v = null;'));
    array_pop($query);
    $query = http_build_query($query);
    $query = '?'.($query ? $query.'&' : $query);

    for ($i = 1, $c = (ceil($attr['count'] / $attr['limit']) + 1); $i < $c; $i++){
        if (array_key_exists('offset', $attr)){
            $page = ($i == 1 ? 0 : ($i * $attr['limit'] - $attr['limit']));

            if ($page == $attr['offset'])
                $current = $i;
        } else {
            $page = $i;

            if ($i == ($attr['page'] ? $attr['page'] : 1))
                $current = $i;
        }

        $pages[$i] = $query.str::format($attr['href'], array('i' => $page));
    }

    $prev = (array_key_exists('offset', $attr) ? ($attr['offset'] - $attr['limit']) : ($attr['page'] - 1));
    $prev = ($prev >= 1 ? $query.str::format($attr['href'], array('i' => $prev)) : '');

    $next = (array_key_exists('offset', $attr) ? ($attr['offset'] + $attr['limit']) : ($attr['page'] + 1));
    $next = ($next <= $page ? $query.str::format($attr['href'], array('i' => $next)) : '');

    return compact('prev', 'current', 'pages', 'next', 'attr');
}