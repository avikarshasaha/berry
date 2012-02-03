<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_flash($attr){
    $attr = array_merge(array(
        'id' => '',
        'width' => 640,
        'height' => 390,
        'params_movie' => $attr['src'],
        'params_allowFullScreen' => 'true',
        'params_allowScriptAccess' => 'always'
    ), $attr);

    if ($attr['swfobject'])
       return _tag_flash_swfobject($attr);

    foreach (piles::attr_group('params', $attr) as $name => $value)
        $params .= piles::fill('param', compact('name', 'value'));

    return preg_replace("/(\s{2,}|\r\n)/", '', str::format('
        <object width="%width" height="%height" %if:id id="%id" %/if:id>
            %params
            %if:src
                <embed type="application/x-shockwave-flash" src="%src" width="%width" height="%height"></embed>
            %/if:src
        </object>
    ', array_merge($attr, compact('params'))));
}

////////////////////////////////////////////////////////////////////////////////

function _tag_flash_swfobject($attr){
    static $i = 0;

    $attr = array_merge(array(
        '#text' => '',
        'version' => 9,
        'flashvars' => '{}'
    ), $attr);

    $attr['id'] = 'tag_flash['.$i++.']';
    $attr['params'] = arr::json(piles::attr_group('params', $attr));
    $attr['flashvars'] = arr::json(piles::attr_group('flashvars', $attr));

    return str::format(
        piles::fill('span', array('id' => '%id', '#text' => '%#text')).
        html::js('swfobject.embedSWF("%src", "%id", "%width", "%height", "%version", null, %flashvars, %params);')
    , $attr);
}
