<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_img2($attr){    $attr = array_merge(array(
        'filter' => 'default'
    ), $attr);
    $image = new image(reset(explode('?', $attr['src'])));
    $query = parse_url($attr['src']);
    $query = ($query['query'] ? '?' : '').$query['query'];

    if ($filter = b::config('tag.img2.'.$attr['filter']))
        $attr += $filter;

    if ($image->file['dir'] != '.' and $image->file['dir'] != '..')
        $dir = $image->file['dir'];

    $file = array(file::path('cache'));
    $file[] = 'tag/img2';
    $file[] = ($dir[0] == '.' ? substr($dir, strpos($dir, '/')) : $dir);
    $file[] = $image->file['name'].'.'.$image->file['ext'];

    if (is_file($tmp = join('/', $file)) and !$attr['nocache']){        foreach ($filter as $k => $v)
            unset($attr[$k]);

        $tmp = getimagesize($tmp);
        $attr['width'] = $tmp[0];
        $attr['height'] = $tmp[1];        $attr['src'] = '~/'.$file[1].'/'.$file[2].'/'.$file[3].$query;

        $image->close();
        return piles::fill('img', $attr);    }

    if (!$attr['text'] and $attr['#text'])
        $attr['text'] = $attr['#text'];

    foreach (array_keys($attr) as $k)
        if ($k == 'text'){            $image->text($attr['text'], $attr);
            $save = true;
        } elseif (in_array($k, array('width', 'height'))){            $image->resize($attr['width'], $attr['height']);
            $save = true;        }

    if ($save){        foreach ($filter as $k => $v)
            unset($attr[$k]);

        file::mkdir($file[0].'/'.$file[1].'/'.$file[2]);

        $tmp = getimagesize(join('/', $file));
        $attr['width'] = $tmp[0];
        $attr['height'] = $tmp[1];
        $attr['src'] = str_replace($file[0], '~', $image->save(join('/', $file), 80)).$query;
    }

    $image->close();
    return piles::fill('img', $attr);}