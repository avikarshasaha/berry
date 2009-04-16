<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
// http://www.verot.net/php_class_upload_samples.htm
function tag_img2($attr){    $attr = array_merge(array(
        'filter' => 'default'
    ), $attr);
    if (!$src = strstr($attr['src'], '~/upload/'))
        return tags::fill('img', $attr);

    $file = file::path('data'.substr($src, 1));
    $upload = new upload($file);
    $new = $upload->file_src_name_body.'.'.$attr['filter'].'.'.$upload->file_src_name_ext;

    if (is_file(dirname($file).'/'.$new) and !$attr['nocache']){        foreach ($attr as $k => $v)
            if (property_exists($upload, $k))
                unset($attr[$k]);
        $attr['src'] = str_replace($upload->file_src_name, $new, $attr['src']);

        unset($attr['filter'], $attr['nocache']);
        return tags::fill('img', $attr);    }

    if ($filter = b::config('tag.img2.'.$attr['filter']))
        $attr = array_merge($filter, $attr);

    $upload->file_name_body_add = '.'.$attr['filter'];
    $upload->file_auto_rename = false;
    $upload->file_overwrite = true;
    $upload->file_safe_name = false;
    $upload->image_resize = true;

    if ($attr['width'] and $attr['height']){        $upload->image_x = $attr['width'];
        $upload->image_y = $attr['height'];
    } elseif ($attr['width']){
        $upload->image_x = $attr['width'];
        $upload->image_ratio_y = true;
    } elseif ($attr['height']){
        $upload->image_y = $attr['height'];
        $upload->image_ratio_x = true;
    } else {        $upload->image_ratio_y = true;    }

    foreach ($attr as $k => $v)
        if (property_exists($upload, $k)){
            $upload->$k = str_replace('\n', "\n", $v);
            unset($attr[$k]);
        }

    if ($upload->process(dirname($file)))
        $upload->clear();

    $attr['src'] = str_replace($upload->file_src_name, $upload->file_dst_name, $attr['src']);
    $attr['width'] = $upload->image_dst_x;
    $attr['height'] = $upload->image_dst_y;

    unset($attr['filter'], $attr['nocache']);
    return tags::fill('img', $attr);}