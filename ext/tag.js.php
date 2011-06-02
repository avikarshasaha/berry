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
	    'img'    => '~/ext/tag/js_calendar/calendar.png',
	    'format' => '%Y-%m-%d %H:%M:%S',
	    'timeFormat' => 24,
	    'showsTime'  => true,
	    'showOthers' => true
	), $attr);

	$md5 = md5(join('', $attr));

	if (!$calendar[$md5])
		$calendar[$md5] = new JS_Calendar('~/ext/tag/js_calendar/', $attr['lang'], $attr['style'], true);

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
	    'img' => '~/ext/tag/js_color_picker/color_picker.jpg',
	    'for' => ''
	), $attr);

    html::block('head',
    	html::css('~/ext/tag/js_color_picker/js_color_picker_v2.css').
    	html::js('~/ext/tag/js_color_picker/color_functions.js').
    	html::js('~/ext/tag/js_color_picker/js_color_picker_v2.js')
    );
	return str::format('
	    <a href="#" onclick="showColorPicker(this, \'%for\'); return false;"><img align="middle" border="0" src="%img" /></a>
	', $attr);
}

////////////////////////////////////////////////////////////////////////////////

/**
 *  File: calendar.php | (c) dynarch.com 2004
 *  Distributed as part of "The Coolest DHTML Calendar"
 *  under the same terms.
 *  -----------------------------------------------------------------
 *  This file implements a simple PHP wrapper for the calendar.  It
 *  allows you to easily include all the calendar files and setup the
 *  calendar by instantiating and calling a PHP object.
 */

class JS_Calendar {
    var $calendar_lib_path;

    var $calendar_file;
    var $calendar_lang_file;
    var $calendar_setup_file;
    var $calendar_theme_file;
    var $calendar_options;

    function JS_Calendar($calendar_lib_path = '/calendar/',
                            $lang              = 'en',
                            $theme             = 'calendar-win2k-1',
                            $stripped          = true) {
        if ($stripped) {
            $this->calendar_file = 'calendar_stripped.js';
            $this->calendar_setup_file = 'setup_stripped.js';
        } else {
            $this->calendar_file = 'calendar.js';
            $this->calendar_setup_file = 'setup.js';
        }
        $this->calendar_lang_file = 'lang/' . $lang . '.js';
        $this->calendar_theme_file = $theme.'.css';
        $this->calendar_lib_path = preg_replace('/\/+$/', '/', $calendar_lib_path);
        $this->calendar_options = array('ifFormat' => '%Y/%m/%d',
                                        'daFormat' => '%Y/%m/%d');
    }

    function set_option($name, $value) {
        $this->calendar_options[$name] = $value;
    }

    function load_files() {
        echo $this->get_load_files_code();
    }

    function get_load_files_code() {
        $code  = ( '<link rel="stylesheet" type="text/css" media="all" href="' .
                   $this->calendar_lib_path . $this->calendar_theme_file .
                   '" />' . "\n" );
        $code .= ( '<script type="text/javascript" src="' .
                   $this->calendar_lib_path . $this->calendar_file .
                   '"></script>' . "\n" );
        $code .= ( '<script type="text/javascript" src="' .
                   $this->calendar_lib_path . $this->calendar_lang_file .
                   '"></script>' . "\n" );
        $code .= ( '<script type="text/javascript" src="' .
                   $this->calendar_lib_path . $this->calendar_setup_file .
                   '"></script>' );
        return $code;
    }

    function make_calendar($other_options = array()) {
        $js_options = $this->_make_js_hash(array_merge($this->calendar_options, $other_options));
        $code  = ( '<script type="text/javascript">Calendar.setup({' .
                   $js_options .
                   '});</script>' );
        return $code;
    }

    function make_input_field($cal_options = array(), $field_attributes = array()) {
        $id = $this->_gen_id();
        $attrstr = $this->_make_html_attr(array_merge($field_attributes,
                                                      array('id'   => $this->_field_id($id),
                                                            'type' => 'text')));
        echo '<input ' . $attrstr .'/>';
        echo '<a href="#" id="'. $this->_trigger_id($id) . '">' .
            '<img align="middle" border="0" src="' . $this->calendar_lib_path . 'img.gif" alt="" /></a>';

        $options = array_merge($cal_options,
                               array('inputField' => $this->_field_id($id),
                                     'button'     => $this->_trigger_id($id)));
        return $this->make_calendar($options);
    }

    /// PRIVATE SECTION

    function _field_id($id) { return 'f-calendar-field-' . $id; }
    function _trigger_id($id) { return 'f-calendar-trigger-' . $id; }
    function _gen_id() { static $id = 0; return ++$id; }

    function _make_js_hash($array) {
        $jstr = '';
        reset($array);
        while (list($key, $val) = each($array)) {
            if (is_bool($val))
                $val = $val ? 'true' : 'false';
            else if (!is_numeric($val))
                $val = '"'.$val.'"';
            if ($jstr) $jstr .= ',';
            $jstr .= '"' . $key . '":' . $val;
        }
        return $jstr;
    }

    function _make_html_attr($array) {
        $attrstr = '';
        reset($array);
        while (list($key, $val) = each($array)) {
            $attrstr .= $key . '="' . $val . '" ';
        }
        return $attrstr;
    }
};