<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_img2($attr){
    $attr = array_merge(array(
        'filter' => 'default'
    ), $attr);

    $info = parse_url($attr['src']);
    $path = ($info['path'][0] == '/' ? '.' : '').$info['path'];
    $query = ($info['query'] ? '?' : '').$info['query'];

    $info = pathinfo($path);
    $mask = sprintf('%%s/%s-%s.%s', $info['filename'], $attr['filter'], $info['extension']);

    $dir = file::path('tmp').'/tag_img2';
    $file = sprintf($mask, $dir);

    $url  = b::q(0).'/';
    $url .= str_replace(dirname(realpath($path)), '', $dir);
    $url  = str_replace('/./', '/', $url);
    $attr['src']  = sprintf($mask, $url).$query;

    if ($filter = b::config('tag_img2.'.$attr['filter']))
        $attr += $filter;

    if (!is_file($cache_file)){
        $image = new Image($path);

        foreach (array_keys($attr) as $k)
            if ($k == 'text'){
                $image->text($attr['text'], $attr);
                $save = true;
            } elseif (in_array($k, array('width', 'height'))){
                $image->resize($attr['width'], $attr['height']);
                $save = true;
            }

        if ($save){

            file::mkdir($dir);
            $image->save($file, 80);
        }

        $image->close();
    }

    foreach ($filter as $k => $v)
        unset($attr[$k]);

    unset($attr['filter']);
    return piles::fill('a', array(
        'href' => $path,
        '#text' => piles::fill('img', $attr)
    ));
}
