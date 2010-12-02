<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function attr_if($attr){    $token = token_get_all('<?php '.$attr['if']);
    $then = preg_split('/<else( ([^>]*))?\/>/i', $attr['#text'], 2);

    for ($i = 1, $c = b::len($token); $i < $c; $i++){        if (is_array($token[$i]) and $token[$i][0] == T_STRING)
            $result .= '"'.$token[$i][1].'"';
        else
            $result .= (is_array($token[$i]) ? $token[$i][1] : $token[$i]);
    }

    if ($func = create_function('', 'return '.$result.';'))
        $attr['#skip'] = !$func();

    if (isset($then[1])){
        $attr['#text'] = ($attr['#skip'] ? $then[1] : $then[0]);
        unset($attr['#skip']);
    }

    unset($attr['if']);
    return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_check_for($attr){
	$attr['#skip'] = !check::$errors[piles::name2var($attr['check_for'])];
    unset($attr['check_for']);
	return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_check_re($attr){
	$attr = array_merge(array(
	    'check_type' => 'e'
	), $attr);

	$_SESSION['attr']['check'][piles::name2var($attr['name'])] = array(
	    $attr['check_re'],
	    str::format($attr['check_need'], $attr),
	    $attr['check_type']
	);

    unset($attr['check_re'], $attr['check_need'], $attr['check_type']);
	return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_period_stop($attr){
	if (date::time($attr['period_stop']) <= time())
	    $attr['#skip'] = true;

    unset($attr['period_stop']);
	return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_period_start($attr){
	if (date::time($attr['period_start']) >= time())
	    $attr['#skip'] = true;

    unset($attr['period_start']);
	return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_confirm($attr){
    $quote = (strpos($attr['confirm'], '"') !== false ? '"' : "'");
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
        check::$errors[piles::name2var($attr['name'])] and
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

	if (!isset($array[$attr['every']]))
	    $array[$attr['every']] = 1;

	$every = (!is_numeric($attr['every'][0]) ? substr($attr['every'], 1) : $attr['every']);
	$attr['#skip'] = ($array[$attr['every']]%$every != 0);
	$array[$attr['every']]++;

	unset($attr['every']);
	return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_router($attr){    $group = piles::attr_group('router', $attr);
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
    $attr['#text'] = ((!$attr['#text'] or $attr['#text'] == $attr['mailto']) ? $result : $attr['#text']);

    unset($attr['mailto']);
	return $attr;
}