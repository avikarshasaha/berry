<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function container_opml($attr){
    $array  = array();
    $output = file_get_contents($attr['src']);
    $parser = xml_parser_create();

    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parse_into_struct($parser, $output, $values);
    xml_parser_free($parser);

    for ($i = 0, $c = b::len($values); $i < $c; $i++)
        if ($values[$i]['level'] > 3)
            $array[$i] = $values[$i]['attributes'];

    if ($attr['order']){
        $order = arr::trim(explode(' ', $attr['order']));
        $order[0] = strtolower($order[0]);
        $order[1] = strtolower($order[1] ? $order[1] : 'asc');

        if (
            in_array($order[0], array('type', 'text', 'title', 'description', 'xmlurl', 'htmlurl')) and
            in_array($order[1], array('asc', 'desc'))
        ){
            foreach ($array as $row)
                $tmp[] = $row[$order[0]];

            array_multisort($tmp, constant(strtoupper('sort_'.$order[1])), $array);
        }
    }

    return tags::parse_vars($attr, arr::unhtml($array), true);
}

////////////////////////////////////////////////////////////////////////////////

function container_xml($attr){
	$xml = simplexml_load_file($attr['src']);

	if ($attr['xpath'])
	    $xml = $xml->xpath($attr['xpath']);

	return tags::parse_vars($attr, simplexml2array($xml), true);
}

////////////////////////////////////////////////////////////////////////////////

// http://php.net/manual/ref.simplexml.php#83781
function simplexml2array($xml){
	if (is_object($xml)){
		if (!$xml->children())
		    return (string)$xml;

        foreach ($xml->children() as $name => $child){            foreach($child->attributes() as $k => $v)
                $attr[$k] = (string)$v;

            if (len($xml->$name) == 1){
                $element[$name] = simplexml2array($child);
                $element[$name]['@attr'] = $attr;
            } else {
                $element[$name][] = simplexml2array($child);
                $element[$name][end(array_keys($element[$name]))]['@attr'] = $attr;
            }
        }
	} else {
        foreach ($xml as $name => $child)
            if (len($child) == 1)
                $element = simplexml2array($child);
            else
                $element[$name] = simplexml2array($child);
    }

    return $element;
}