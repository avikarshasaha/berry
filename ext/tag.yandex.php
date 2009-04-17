<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_yandex_map($attr){    static $i = 0;
    $attr = array_merge(array(
        'id'     => 'tag_yandex_map['.$i++.']',
        'width'  => 100,
        'height' => 100
    ), (array)c('tag.yandex.map'), $attr);

    html::block('head',
        html::js('http://api-maps.yandex.ru/1.0/index.xml?key='.$attr['key'].'&.js').
        html::js('
            function YandexMap(id, addr){
                var map = new YMaps.Map($(id));
                var geocoder = new YMaps.Geocoder(addr);

                YMaps.Events.observe(geocoder, geocoder.Events.Load, function(){
                    if (this.length()){
                        var geoResult = this.get(0);
                        map.addOverlay(geoResult);
                        map.setBounds(geoResult.boundedBy);
                        map.setBounds(geoResult.getBounds());
                    }
                });

                map.addControl(new YMaps.Zoom());
                map.addControl(new YMaps.TypeControl());
                map.addControl(new YMaps.ToolBar());
            }
        ')
    );

    $attr['style'] .= ($attr['style'] ? ';' : '').'width: '.$attr['width'].'; height: '.$attr['height'].';';

    unset($attr['key'], $attr['addr'], $attr['width'], $attr['height']);
    return tags::fill('div', $attr).html::js('YandexMap("'.$attr['id'].'", "'.$attr['addr'].'");');}