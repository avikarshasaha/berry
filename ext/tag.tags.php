<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_berry($attr){
    return $attr['#text'];
}

////////////////////////////////////////////////////////////////////////////////

function container_loop($attr){
    if ($attr['each']){    	$explode = explode(' in ', $attr['each']);
    	$explode[1] = trim($explode[1]);
    	list($key, $value) = arr::trim(explode(',', $explode[0], 2));

    	if (tags::is_array($explode[1]))
    	    $array = tags::unserialize($explode[1]);
    	else
    	    $array = b::l($explode[1]);
    } elseif ($attr['range']){    	$explode = explode(' in ', $attr['range']);
    	$range = explode('-', trim($explode[1]), 3);
    	$array = b::call('*range', arr::trim($range));
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

function container_if($attr){	if (!$attr['expr'])
	    return $attr['#text'];

	if ($func = create_function('', 'return ('.$attr['expr'].');'))
	    $skip = $func();

    return tags::parse(tags::parse_else($skip, $attr['#text']));
}

////////////////////////////////////////////////////////////////////////////////

function container_show($attr){
	$output = b::show($attr['src']);

    if (!$attr['#text'])
        return tags::parse($output);

    $result = tags::parse_else($output, $attr['#text']);
    return tags::parse_vars($output, 'show', $result);
}

////////////////////////////////////////////////////////////////////////////////

function container_code($attr){
	if ($attr['escape'])
	    $attr['#text'] = str::unhtml($attr['#text']);

	$pre = $attr['pre'];
	unset($attr['escape'], $attr['pre']);
	$result = tags::fill($attr);
    return ($pre ? '<pre>'.$result.'</pre>' : $result);
}

////////////////////////////////////////////////////////////////////////////////

function tag_msg($attr){	if ($attr['#text']){		html::msg($attr['id'], $attr['#text']);		return;	}

	if ($messages = html::msg($attr['id']))
        return b::show('tag.msg.'.$attr['id'], compact('messages'));
}

////////////////////////////////////////////////////////////////////////////////

function tag_block($attr){
	html::block($attr['id'], $attr['#text'], (is_numeric($attr['sort']) ? $attr['sort'] : 50));
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
	    $text = substr($attr['cite'], 0, $pos);
	    $text = ($text ? $text : $href);

	    if (is_int($pos))
	        $attr['cite'] = compact('href', 'text');
	    else
	    	unset($attr['cite']);
	}

    return b::show('tag.quote', compact('attr'));
}

////////////////////////////////////////////////////////////////////////////////

function container_theme($attr){    if (is_file($file = file::path('show/theme.ini')))
        return tags::parse_vars($attr, parse_ini_file($file, true));
}

////////////////////////////////////////////////////////////////////////////////

function tag_group($attr){
	static $group = array();

	$result = tags::parse_else(!$group[$attr['id']], $attr['#text']);
	$group[$attr['id']] = true;

	return $result;
}

////////////////////////////////////////////////////////////////////////////////

function tag_toc($attr){    $uri = b::q(1, 0);
    $string = '<a name="%s"></a><a href="'.$uri.'#%s">%s</a>';
    if (preg_match_all('/<ref>(.*?)<\/ref>/i', $attr['#text'], $match)){
        for ($i = 0, $c = b::len($match[1]); $i < $c; $i++){            $id = 'ref-'.($i + 1);            $ref[] = sprintf($string, $id, '_'.$id, '↑').' '.$match[1][$i];
            $attr['#text'] = str_replace($match[0][$i], sprintf($string, '_'.$id, $id, '<sup>[?]</sup>'), $attr['#text']);        }

        if ($ref)
            $ref = '<li>'.join('</li><li>', $ref).'</li>';
    }
    if (preg_match_all('/<h(\d+)( (.*?))?>(.*)<\/h\\1>/i', $attr['#text'], $match)){
        for ($i = 0, $c = b::len($match[1]); $i < $c; $i++){
        	preg_match('/ id=("|\')(.*?)\\1/', $match[2][$i], $id);

        	$id = ($id[2] ? $id[2] : 'toc-'.($i + 1));
            $toc .= str_repeat('#', $match[1][$i]);
            $toc .= ' <a href="'.$uri.'#'.$id.'">'.$match[4][$i].'</a>'."\r\n";
            $attr['#text'] = str_replace($match[0][$i], '<a name="'.$id.'"></a> '.$match[0][$i], $attr['#text']);
        }

        if ($toc)
            $toc = formatter::textile($toc);
    }

	return tags::parse_vars($attr, compact('toc', 'ref'));
}

////////////////////////////////////////////////////////////////////////////////

function tag_cdata($attr){    if (!$attr['#is_final'])
        return tags::fill($attr);

    return '<![CDATA['.$attr['#text'].']]>';
}