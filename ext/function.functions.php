<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function echo_r($var, $return = false){    if ($return)
        return debug::print_r($var, $return);

    debug::print_r($var);
}

////////////////////////////////////////////////////////////////////////////////

// Суть: http://spectator.ru/technology/web-building/tags2null
// Прикрутить синонимы надобно.
function keywords($mixed){
    if (is_array($mixed)){
        $array = _keywords($mixed);
        natsort($array);
        return $array;
    }

    $lines = explode("\r\n", preg_replace('/\s+\/\s+/', ' / ', $mixed));
    $replace = array();

    foreach ($lines as $i => $line){
        $line = trim($line);

        if (!$line or $line[0] == '#'){
            unset($lines[$i]);
            continue;
        }

        foreach (explode(' / ', $line) as $word)
            if ($path = preg_grep('/\/ '.preg_quote($word, '/').'/i', $lines)){
                $word = '/^'.preg_quote($word, '/').'/i';

                if (!$replace[$word])
                    $replace[$word] = preg_replace(array_keys($replace), array_values($replace), end($path));
            }
    }

    foreach ($lines as $line){
        $line = preg_replace(array_keys($replace), array_values($replace), trim($line));
        $line = array_map('trim', explode(' / ', $line));

        $result .= '$array';
        $result .= '["'.join('"]["', $line).'"]';
        $result .= ' = array();';
    }

    if ($func = create_function('', $result.'; return $array;'))
        return $func();
}

////////////////////////////////////////////////////////////////////////////////

function _keywords($array, $parent = '', $result = array()){
    foreach ($array as $k => $v){
        if ($v)
            $result = _keywords($v, $parent.($parent ? ' / ' : '').$k, $result);
        else
            $result[] = $parent.($parent ? ' / ' : '').$k;
    }

    return $result;
}