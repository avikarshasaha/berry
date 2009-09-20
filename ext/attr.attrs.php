<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function attr_if($attr){    if (preg_match('/("|\'|)(.*)\\1 (==|===|!=|!==|<|<=|>|>=) ("|\'|)(.*)\\4/', $attr['if'], $match)){    	$l = $match[2];
    	$r = $match[5];

        switch (trim($match[3])){        	case '==':
        	    $skip = ($l == $r);
        	break;

        	case '===':
        	    $skip = ($l === $r);
        	break;

        	case '!=':
        	    $skip = ($l != $r);
        	break;

        	case '!==':
        	    $skip = ($l !== $r);
        	break;

        	case '<':
        	    $skip = ($l < $r);
        	break;

        	case '<=':
        	    $skip = ($l <= $r);
        	break;

        	case '>':
        	    $skip = ($l > $r);
        	break;

        	case '>=':
        	    $skip = ($l >= $r);
        	break;        }
    } elseif (preg_match('/(!)?("|\'|)(.*)\\2/', $attr['if'], $match)){        if (tags::is_array($match[3]))
            $match[3] = tags::unserialize($match[3]);

    	if ($match[1] == '!')
    	    $skip = !$match[3];
    	else
    	    $skip = $match[3];
    }

    $attr['#skip'] = !$skip;
    unset($attr['if']);
    return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_check_re($attr){
	$attr = array_merge(array(
	    'check_type' => 'e'
	), $attr);

	$_SESSION['attr']['check'][tags::elmname_parse($attr['name'])] = array(
	    $attr['check_re'],
	    str::format($attr['check_need'], $attr),
	    $attr['check_type']
	);

    unset($attr['check_re'], $attr['check_need'], $attr['check_type']);
	return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_period_stop($attr){
	if (strtotime($attr['period_stop']) <= time())
	    $attr['#skip'] = true;

    unset($attr['period_stop']);
	return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_period_start($attr){
	if (strtotime($attr['period_start']) >= time())
	    $attr['#skip'] = true;

    unset($attr['period_start']);
	return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_confirm($attr){
    $quote = (is_int(strpos($attr['confirm'], '"')) ? '"' : "'");
    $attr['onclick'] = 'if (!confirm('.$quote.$attr['confirm'].$quote.')) return false;'.$attr['onclick'];

    unset($attr['confirm']);
	return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_default($attr){
    if ($attr['default'] === '')
        unset($attr['default']);

	return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_name($attr){
    if (
        check::$error[tags::elmname_parse($attr['name'])] and
        strpos($attr['class'], 'check_error') === false
    )
        $attr['class'] .= ($attr['class'] ? ' ' : '').'check_error';

    if (!array_key_exists('id', $attr))
        $attr['id'] = $attr['name'];

	return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_oddeven($attr){	static $i = 0;

	$oddeven = explode(' ', $attr['oddeven']);
    $attr['class'] .= ($attr['class'] ? $attr['class'].' ' : '').$oddeven[$i++];

    if ($i == b::len($oddeven))
        $i = 0;

    unset($attr['oddeven']);
	return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_every($attr){	static $array = array();

	if (!array_key_exists($attr['every'], $array))
	    $array[$attr['every']] = 1;

	$every = (!is_numeric($attr['every'][0]) ? substr($attr['every'], 1) : $attr['every']);
	$attr['#skip'] = ($array[$attr['every']]%$every != 0);
	$array[$attr['every']]++;

	unset($attr['every']);
	return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_router($attr){    $group = attr::group('router', $attr);
	$attr['href'] = b::router($attr['router'], $group);

	foreach ($group as $k => $v)
	    unset($attr['router_'.$k]);

	unset($attr['router']);
	return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_mailto($attr){
	for ($i = 0, $c = b::len($attr['mailto']); $i < $c; $i++)
	    $result .= '&#'.ord($attr['mailto'][$i]).';';

    $attr['href'] = '&#109;&#97;&#105;&#108;&#116;&#111;&#58;'.$result;
    $attr['#text'] = ($attr['#text'] == $attr['mailto'] ? $result : $attr['#text']);

    unset($attr['mailto']);
	return $attr;
}