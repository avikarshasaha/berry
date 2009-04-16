<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
tags::skip('data');
function tag_data($attr){    static $cache = array();

    preg_match_all('/%(\w+)?{'.$attr['#tag'].'(\.([^}]*))?}/', $attr['#text'], $match);
	    $md5 .= $from = reset(explode('.', $match[3][0]));

    for ($i = 0, $c = b::len($match[0]); $i < $c; $i++)
        $select[] = $match[2][$i];

    if ($select)
        $attr['select'] = join(', ', array_unique($select)).($attr['select'] ? ', '.$attr['select'] : '');

    foreach (array('select', 'where', 'order', 'group') as $v)
        if ($attr[$v])
            $md5 .= $attr[$v] = preg_replace('/([\w]+)\.([\w\.]+)(\s+)?/', '`\\1.\\2`\\3', $attr[$v]);

    $md5 = md5($md5.$attr['count']);

    if (!$data = $cache[$md5]){
        $data = data::get($from, $attr);

        if ($attr['count'])
            $data = array(array($from => $data));

        $cache[$md5] = $data;
    }

    return tags::parse_lvars($attr, $data, true);
}