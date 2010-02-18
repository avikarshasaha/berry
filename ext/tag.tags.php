<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_msg($attr){	if ($attr['#text']){		html::msg($attr['id'], $attr['#text']);		return;	}

	if ($messages = html::msg($attr['id']))
        return piles::show('tag.msg.'.$attr['id'], compact('messages'));
}

////////////////////////////////////////////////////////////////////////////////

function tag_block($attr){
	html::block($attr['id'], $attr['#text'], (is_numeric($attr['sort']) ? $attr['sort'] : 50));
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

    return piles::show('tag.quote', compact('attr'));
}

////////////////////////////////////////////////////////////////////////////////

function tag_group($attr){
	static $group = array();

	if (isset($group[$attr['id']]))
	    return;

	$group[$attr['id']] = true;
	return $attr['#text'];
}

////////////////////////////////////////////////////////////////////////////////

function tag_toc($attr){    $uri = b::q(1, 0);
    $toc = $ref = '';
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

	return str::format($attr['#text'], compact('toc', 'ref'));
}