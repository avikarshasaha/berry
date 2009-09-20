<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function supadupa_cache($attr){    if (!$attr['id'])
        return $attr['#text'];

    $id = 'supadupa/'.$attr['id'].'.html';
    $check = attr::group('update_after', $attr);

    if (!$output = cache::get($id, $check)){
        $output = tags::parse($attr['#text']);
        $output = tags::parse($output, true);
        $output = tags::unsux($output);

        cache::set($output);
    }

    return '<!--supadupa[cache]['.$id.']-->';
}

////////////////////////////////////////////////////////////////////////////////

hook::set('output', 'output_supadupa_cache');
function output_supadupa_cache($output){
    if (preg_match_all('/<!--supadupa\[cache\]\[(.*)\]-->/', $output, $match))
        for ($i = 0, $c = b::len($match[0]); $i < $c; $i++){            if ($file = cache::exists($match[1][$i]))                $output = str_replace($match[0][$i], file_get_contents($file), $output);
        }

    return $output;
}