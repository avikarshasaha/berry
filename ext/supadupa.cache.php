<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function supadupa_cache($attr){
    $attr = array_merge(array(
        'lifetime' => '+1 day',
        'dir' => 'supadupa'
    ), $attr);

    if ($attr['dir']){
        $dir = $attr['dir'].'/';
        file::mkdir(file::path('cache/').$dir);
    }

    $id = $dir.md5($attr['id'].$attr['#text']);
    $lifetime = (is_numeric($attr['lifetime']) ? (time() + $attr['lifetime']) : strtotime($attr['lifetime']));
    $return = '<!--supadupa[cache]['.$id.']['.
              'update_after_file='.$attr['update_after_file'].
              '&update_after_db='.$attr['update_after_db'].
              '&update_after_url='.$attr['update_after_url'].
              ']-->';

    if (
        is_file($file = p('cache/').$id) and
        filemtime($file) < $lifetime and
        !_cache_update_after($id, $attr)
    )
        return $return;

    $output = tags::parse($attr['#text']);
    $output = tags::parse($output, true);
    $output = tags::unsux($output);

    file_put_contents($file, $output);
    file_put_contents($file.'.html', $attr['#text']);
    return $return;
}

////////////////////////////////////////////////////////////////////////////////

function output_supadupa_cache($output){
    if (preg_match_all('/<!--supadupa\[cache\]\[([^\]]*)\]\[([^\]]*)\]-->/', $output, $match))
        for ($i = 0, $c = b::len($match[0]); $i < $c; $i++){            parse_str($match[2][$i], $attr);
            $file = p('cache/').$match[1][$i];

            if (_cache_update_after($match[1][$i], $attr)){                $result = tags::parse(file_get_contents($file.'.html'));
                $result = tags::parse($result, true);
                $result = tags::unsux($result);

                file_put_contents($file, $result);            } else {                $result = file_get_contents($file);            }

            $output = str_replace($match[0][$i], $result, $output);
        }

    if (is_int(strpos($output, '<!--supadupa[cache]')))
        $output = output_cache($output);

    return $output;
}

////////////////////////////////////////////////////////////////////////////////

function _cache_update_after($id, $attr){    global $sql;

    $time = filemtime(p('cache/').$id);
    if (
        $attr['update_after_file'] and
        file_exists($attr['update_after_file'])
    )
        $check = ($time < filemtime($attr['update_after_file']));

    if (
        !$check and
        $attr['update_after_db'] and
        ($query = sql::getRow('show table status like "?_"', $attr['update_after_db']))
    )
        $check = ($time < strtotime($query['Update_time']));

    if (
        !$check and
        $attr['update_after_url'] and
        ($headers = get_headers($attr['update_after_url'], true))
    )
        $check = ($time < strtotime($headers['Last-Modified']));

    return $check;}