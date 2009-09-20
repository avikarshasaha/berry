<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_js_calendar($attr){	static $calendar;

	$attr = array_merge(array(
	    'lang'   => 'ru',
	    'style'  => 'style/system',
	    'img'    => '~/tag/js_calendar/calendar.png',
	    'format' => '%Y-%m-%d %H:%M:%S',
	    'timeFormat' => 24,
	    'showsTime'  => true,
	    'showOthers' => true
	), $attr);

	$md5 = md5(join('', $attr));

	if (!$calendar[$md5])
		$calendar[$md5] = new JS_Calendar('~/tag/js_calendar/', $attr['lang'], $attr['style'], true);

	foreach ($attr as $k => $v)
	    $calendar[$md5]->set_option($k, $v);

	$calendar[$md5]->set_option('ifFormat', $attr['format']);
	$calendar[$md5]->set_option('inputField', $attr['for']);
	$calendar[$md5]->set_option('button', 'trigger_for_'.$attr['for']);

	html::block('head', $calendar[$md5]->get_load_files_code());
	return '<a href="#" id="trigger_for_'.$attr['for'].'"><img align="middle" alt="" border="0" src="'.$attr['img'].'" /></a>'.$calendar[$md5]->make_calendar();
}

////////////////////////////////////////////////////////////////////////////////

function tag_js_color_picker($attr){
	$attr = array_merge(array(
	    'img' => '~/tag/js_color_picker/color_picker.jpg',
	    'for' => ''
	), $attr);

    html::block('head',
    	html::css('~/tag/js_color_picker/js_color_picker_v2.css').
    	html::js('~/tag/js_color_picker/color_functions.js').
    	html::js('~/tag/js_color_picker/js_color_picker_v2.js')
    );
	return str::format('
	    <a href="#" onclick="showColorPicker(this, $(%for)); return false;"><img align="middle" border="0" src="%img" /></a>
	', $attr);
}