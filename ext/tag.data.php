<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function container_data($attr){    preg_match_all('/%(\w+)?{'.$attr['#tag'].'.([^}]*)}/', $attr['#text'], $match);

    $from = reset(explode('.', $match[2][0]));
    $table = sql::table($from);
    $attr['#text'] = preg_replace('/(%(\w+)?{'.$attr['#tag'].').'.$from.'/', '\\1', $attr['#text']);

    for ($i = 0, $c = b::len($match[0]); $i < $c; $i++)
        $table->select(preg_replace('/^'.$from.'./', '', $match[2][$i]));

    foreach (attr::filter($attr) as $k => $v)        if (preg_match_all('/\((([^\(\)]+)|(?R))*\)/', $v, $match)){
            $match = array_map(create_function('$v', 'return substr($v, 1, -1);'), $match[0]);

            for ($i = 0, $c = b::len($match); $i < $c; $i++)
                call_user_method($k, $table, $match[$i]);
        } else {            $table->$k($v);        }

    return tags::parse_lvars($attr, $table->as_array(), true);}