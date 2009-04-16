<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_flash($attr){
	$attr = array_merge(array(
	    'width' => 450,
	    'height' => 350,
	    'version' => 7,
	    '#text' => '',
	    'params_quality' => 'high',
	    'params_menu' => 'false'
	), $attr);

	foreach (attr::group('params', $attr) as $k => $v)
	    $params .= '<param name="'.$k.'" value="'.$v.'" />';

    return preg_replace("/(\s{2,}|\r\n)/", '', str::format('
        <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=%version,0,0,0" width="%width" height="%height" id="%id" data="%src">
            %params
            %if:src <embed src="%src" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="%width" height="%height"></embed> %/if:src
        </object>
    ', array_merge($attr, compact('params'))));
}

////////////////////////////////////////////////////////////////////////////////

function tag_swfobject($attr){
	static $i = 0;

	$attr = array_merge(array(
	    'width' => 450,
	    'height' => 350,
	    'version' => 7,
	    'flashvars' => '{}',
	    '#text' => '',
	    'params_quality' => 'high',
	    'params_menu' => 'false'
	), $attr);

	html::block('head', html::js('~/tag/swfobject.js'));

	$attr['id'] = 'tag_swfobject['.$i++.']';
	$attr['params'] = arr::json(attr::group('params', $attr));
	$attr['flashvars'] = arr::json(attr::group('flashvars', $attr));

    return str::format(
        tags::fill('span', array('id' => '%id', '#text' => '%#text')).
        html::js('swfobject.embedSWF("%src", "%id", "%width", "%height", "%version", null, %flashvars, %params);')
    , $attr);
}