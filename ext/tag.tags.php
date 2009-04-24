<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_blueberry($attr){
    return $attr['#text'];
}

////////////////////////////////////////////////////////////////////////////////

function tag_str_replace($attr){    if ($attr['search'])
        $attr['#text'] = str_replace($attr['search'], $attr['replace'], $attr['#text']);
	if ($attr['pattern'])
	    $attr['#text'] = preg_replace($attr['pattern'], $attr['replace'], trim($attr['#text']));

	return $attr['#text'];
}

////////////////////////////////////////////////////////////////////////////////

function tag_str_repeat($attr){
    return str_repeat($attr['string'], $attr['int']);
}

////////////////////////////////////////////////////////////////////////////////

function tag_int_size($attr){
	return int::size($attr['int'], $attr);
}

////////////////////////////////////////////////////////////////////////////////

function tag_int_format($attr){
	return number_format($attr['int'], $attr['len'], $attr['point'], $attr['separator']);
}

////////////////////////////////////////////////////////////////////////////////

function tag_date($attr){    $func = (is_int(strpos($attr['format'], '%')) ? 'date::' : '').'date';	return b::call($func, $attr['format'], date::time($attr['timestamp']));
}

////////////////////////////////////////////////////////////////////////////////

function tag_int_plural($attr){
	return int::plural($attr['int'], $attr['string'], $attr['noint']);
}

////////////////////////////////////////////////////////////////////////////////

function tag_int_str($attr){
	return int::str($attr['int']);
}

////////////////////////////////////////////////////////////////////////////////

function tag_int_roman($attr){
	return int::roman($attr['int']);
}

////////////////////////////////////////////////////////////////////////////////

function tag_str_translit($attr){
	return (isset($attr['separator']) ? str::translit($attr['#text'], $attr['separator']) : str::translit($attr['#text']));
}

////////////////////////////////////////////////////////////////////////////////

tags::skip('loop');
function tag_loop($attr){
    if ($attr['each']){    	$explode = explode(' in ', $attr['each']);
    	$explode[1] = trim($explode[1]);
    	list($key, $value) = arr::trim(explode(',', $explode[0], 2));

    	if (tags::is_array($explode[1]))
    	    $array = tags::unserialize($explode[1]);
    	else
    	    $array = b::l($explode[1]);
    } elseif ($attr['range']){    	$explode = explode(' in ', $attr['range']);
    	$range = explode('-', trim($explode[1]), 3);
    	$array = call_user_func_array('range', arr::trim($range));
    	list($key, $value) = arr::trim(explode(',', $explode[0], 2));    } else {        return;    }

    $i = 0;
	foreach ($array as $k => $v){
		$k = str_replace('.', '\.', $k);
		$array[$k] = $v;

	    if ($key and $value)
	        $result .= preg_replace(
	            array(
	                '/%(\w+)?{'.preg_quote($key, '/').'/',
	                '/%(\w+)?{'.preg_quote($value, '/').'/',
	                '/%(\w+)?{loop/'
	            ),
	            array(
	                '$\\1{tag_loop.k.'.$i,
	                '$\\1{tag_loop.v.'.$k,
	                '$\\1{tag_loop.loop.'.$k
	            ),
	            $attr['#text']
	        );
	    else
	    	$result .= preg_replace(
	    	    array('/%(\w+)?{'.preg_quote($key, '/').'/', '/%(\w+)?{loop/'),
	    	    array('$\\1{tag_loop.v.'.$k, '$\\1{tag_loop.loop.'.$k),
	    	    $attr['#text']
	    	);

	    $i++;
	}

	$keys = array_keys($array);
    $loop[$keys[0]]['#is_first'] = true;
    $loop[$k]['#is_last'] = true;

	b::l('tag_loop', array('k' => $keys, 'v' => $array, 'loop' => $loop));
	$result = tags::parse($result);
	b::l('tag_loop', array());
	return $result;
}

////////////////////////////////////////////////////////////////////////////////

tags::skip('if');
function tag_if($attr){	if (!$attr['expr'])
	    return $attr['#text'];

	if ($func = create_function('', 'return ('.$attr['expr'].');'))
	    $skip = $func();

    return tags::parse(tags::parse_else($skip, $attr['#text']));
}

////////////////////////////////////////////////////////////////////////////////

tags::skip('show');
function tag_show($attr){
	$output = b::show($attr['src']);

    if (!$attr['#text'])
        return tags::parse($output);

    $result = tags::parse_else($output, $attr['#text']);
    return tags::parse_lvars($output, 'show', $result);
}

////////////////////////////////////////////////////////////////////////////////

function tag_open($attr){    $output = file_get_contents($attr['src']);
    return ($attr['escape'] ? str::unhtml($output) : $output);
}

////////////////////////////////////////////////////////////////////////////////

function tag_str_nl2br($attr){
	return nl2br($attr['#text']);
}

////////////////////////////////////////////////////////////////////////////////

function tag_help($attr){
	static $i = 0;

	$attr = array_merge(array(
	    'id'    => $i++,
	    'title' => '',
	    '#text' => ''
	), $attr);

	return tags::parse_lvars(b::show('tag.help'), 'help', $attr);
}

////////////////////////////////////////////////////////////////////////////////

// http://spectator.ru/technology/web-building/no_spam_2
function tag_mailto($attr){
	for ($i = 0, $c = b::len($attr['href']); $i < $c; $i++)
	    $result .= '&#'.ord($attr['href'][$i]).';';

	if (!$attr['#text'])
	    $attr['#text'] = $result;

    $attr['href'] = '&#109;&#97;&#105;&#108;&#116;&#111;&#58;'.$result;
	return tags::fill('a', $attr);
}

////////////////////////////////////////////////////////////////////////////////

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

function tag_js_popup($attr){
	static $i = 1;

	$attr = array_merge(array(
	    'width'  => 450,
	    'height' => 400,
	    'href'   => '',
	    'scroll' => 1,
	    'resize' => 1
	), $attr);

	return str::format('
	    <a href="%href" onclick="window.open(\'%href\', \'tag_popup['.$i++.']\', \'scrollbars=%scroll,resizable=%resize,width=%width,height=%height;\'); return false;">%#text</a>
	', $attr);
}

////////////////////////////////////////////////////////////////////////////////

function tag_gravatar($attr){
	$allow = attr::merge(array(
	    'size'    => 100,
	    'rating'  => '',
	    'default' => ''
	), $attr);

	$img['src'] = 'http://gravatar.com/avatar/'.md5($attr['id']).'/?'.http_build_query($allow);
	$img['alt'] = $attr['alt'];
	$img['border'] = (int)$attr['border'];

	return tags::fill('img', $img);
}

////////////////////////////////////////////////////////////////////////////////

function tag_header($attr){
    if (is_numeric($attr['string']))
        http::status($attr['string'], (bool)$attr['replace']);
    else
        header($attr['string'],  (bool)$attr['replace']);
}

////////////////////////////////////////////////////////////////////////////////

function tag_go($attr){	if ($attr['status'])
	    http::go($attr['href'], $attr['status']);
	else
        http::go($attr['href']);
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

////////////////////////////////////////////////////////////////////////////////

tags::skip('code');
function tag_code($attr){
	if ($attr['escape'])
	    $attr['#text'] = str::unhtml($attr['#text']);

	$pre = $attr['pre'];
	unset($attr['escape'], $attr['pre']);
	$result = tags::fill($attr);
    return ($pre ? '<pre>'.$result.'</pre>' : $result);
}

////////////////////////////////////////////////////////////////////////////////

function tag_showhide($attr){
	$attr = array_merge(array(
	    'id'   => '',
	    'show' => '+',
	    'hide' => '-'
	), $attr);

	return replace('
	    <a href="#" onclick="Element.toggle(\'%id\'); Element.toggle(\'show%id\'); Element.toggle(\'hide%id\'); return false;"><span id="show%id">%show</span><span id="hide%id" style="display: none;">%hide</span></a>
	', $attr);
}

////////////////////////////////////////////////////////////////////////////////

function tag_msg($attr){	if ($attr['#text']){		html::msg($attr['id'], $attr['#text']);		return;	}

    return tags::parse_lvars(b::show('tag.msg.'.str_replace('.', '\.', $attr['id'])), 'msg', html::msg($attr['id']));
}

////////////////////////////////////////////////////////////////////////////////

function tag_str_truncate($attr){
	return ($attr['len'] ? str::truncate($attr['#text'], $attr['len']) : str::truncate($attr['#text']));
}

////////////////////////////////////////////////////////////////////////////////

function tag_block($attr){
	html::block($attr['id'], $attr['#text'], (is_numeric($attr['sort']) ? $attr['sort'] : 50));
}

////////////////////////////////////////////////////////////////////////////////

function tag_breadcrumbs($attr){
	$attr = array_merge(array(
	    'separator' => ($attr['reverse'] ? ' &laquo; ' : ' &raquo; ')
	), $attr);

	$q = b::q();
	$uri[$q[0]] = b::config('site.name');

	for ($i = 1, $c = len($q); $i < $c; $i++){
		$tmp .= ($i == 1 ? $q[0] : '').'/'.$q[$i];
		$uri[$tmp] = $q[$i];
	}

	foreach ($uri as $link => $title)
	    $result[] = '<a href="'.$link.'">'.$title.'</a>';

	if ($attr['reverse'])
	    $result = array_reverse($result);

    $result = join($attr['separator'], $result);

    if ($attr['type'] == 'title')
        $result = strip_tags($result);

	return $result;
}

////////////////////////////////////////////////////////////////////////////////

function tag_int_ago($attr){	$attr = array_merge(array(
	    'len' => 3
	), $attr);

	return int::ago((time() - date::time($attr['date'])), $attr['len'], $attr);
}

////////////////////////////////////////////////////////////////////////////////

function tag_noindex($attr){    if ($attr['#is_final'])
        return '<noindex>'.str_replace('<a ', '<a rel="nofolow" ', $attr['#text']).'</noindex>';

    return '<noindex>'.$attr['#text'].'</noindex>';
}

////////////////////////////////////////////////////////////////////////////////

function tag_quote($attr){	$attr['class'] .= ($attr['class'] ? ' ' : '').'quote';
	if ($attr['cite']){	    $pos = strpos($attr['cite'], 'http://');
	    $href = substr($attr['cite'], $pos);
	    $cite = substr($attr['cite'], 0, $pos);
	    $cite = ($cite ? $cite : $href);

	    if (is_int($pos))
	        $attr['cite'] = array('href' => $href, 'text' => $cite);
	    else
	    	unset($attr['cite']);
	}

    return tags::parse_lvars(b::show('tag.quote'), 'quote', $attr);
}

////////////////////////////////////////////////////////////////////////////////

tags::skip('theme');
function tag_theme($attr){    if (is_file($file = file::path('show/theme.ini')))
        return tags::parse_lvars($attr, parse_ini_file($file, true));
}

////////////////////////////////////////////////////////////////////////////////

function tag_str_trim($attr){	$attr = array_merge(array(
	    'char' => ' '
	), $attr);

	return trim(trim($attr['#text']), $attr['char']);
}

////////////////////////////////////////////////////////////////////////////////

function tag_group($attr){
	static $i;

	$result = tags::parse_else(!$i[$attr['id']], $attr['#text']);
	$i[$attr['id']] = true;

	return $result;
}

////////////////////////////////////////////////////////////////////////////////

function tag_str_pad($attr){	$attr = array_merge(array(
	    'type' => 'right'
	), $attr);
	return str_pad(trim($attr['#text']), $attr['len'], $attr['string'], constant('STR_PAD_'.strtoupper($attr['type'])));
}

////////////////////////////////////////////////////////////////////////////////

function tag_toc($attr){
    if (preg_match_all('/<h(\d+)( (.*?))?>(.*)<\/h\\1>/i', $attr['#text'], $match)){
        for ($i = 0, $c = b::len($match[1]); $i < $c; $i++){
        	preg_match('/ id=("|\')(.*?)\\1/', $match[2][$i], $id);

        	$id = ($id[2] ? $id[2] : 'toc-'.($i + 1));
            $toc .= str_repeat('#', $match[1][$i]);
            $toc .= ' <a href="'.q(1, 0).'#'.$id.'">'.$match[4][$i].'</a>'."\r\n";
            $attr['#text'] = str_replace($match[0][$i], '<a name="'.$id.'"></a> '.$match[0][$i], $attr['#text']);
        }

        if ($toc)
            $toc = formatter::textile($toc);
    }

	return tags::parse_lvars($attr, $toc);
}

////////////////////////////////////////////////////////////////////////////////

function tag_wrapper($attr){    $wrapper = b::config('tag.wrapper.'.$attr['id']);
    if (is_file($file = file::path('show/'.$wrapper)))
        $wrapper = file_get_contents($file);

	return tags::parse_lvars($wrapper, 'wrapper', $attr);
}

////////////////////////////////////////////////////////////////////////////////

function tag_cdata($attr){    if (!$attr['#is_final'])
        return tags::fill($attr);

    return '<![CDATA['.$attr['#text'].']]>';
}

////////////////////////////////////////////////////////////////////////////////

tags::skip('glob');
function tag_glob($attr){
    $glob = glob($attr['pattern'], GLOB_BRACE);

	foreach ($glob as $file)
	    if (is_dir($file))
		    $files[] = array_merge(pathinfo($file), array('is_dir' => true));

	foreach ($glob as $file)
	    if (!is_dir($file)){
	    	$is = 'is_'.(is_link($file) ? 'link' : (is_file($file) ? 'file' : ''));
		    $files[] = array_merge(pathinfo($file), array('filesize' => filesize($file), $is => true));
	    }

    return tags::parse_lvars($attr, $files, true);
}

////////////////////////////////////////////////////////////////////////////////

tags::skip('tabs');
function tag_tabs($attr){
    if (preg_match_all('/<tab( (.*?))?>(.*?)<\/tab>/is', $attr['#text'], $match))
        for ($i = 0, $c = b::len($match[0]); $i < $c; $i++){
        	preg_match_all('/ (open|title)=("|\')(.*?)\\2/i', $match[1][$i], $tmp);
        	$tmp = array_combine($tmp[1], $tmp[3]);
            $tmp['#text'] = $match[3][$i];
            $tabs[] = $tmp;
        }

    foreach ($tabs as $k => $v){
    	$click[] = "$('tag_tabs[tab_".$k."]').hide()";
    	$click[] = "$('tag_tabs[menu_".$k."]').removeClassName('on').addClassName('off')";
    }

    $click = join(';', $click);

    foreach ($tabs as $k => $v)
    	$tabs[$k]['click'] = $click;

	return tags::parse_lvars(b::show('tag.tabs'), 'tabs', $tabs);
}