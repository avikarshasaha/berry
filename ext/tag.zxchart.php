<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
// http://ankord.com/zxchart.html
// Ещё один повод поблагодарить и за PHP Expert Editor!
function tag_zxchart($attr){    $titles = array('');

    file::mkdir($dir = file::path('tmp').'/tag/zxchart');
    if (preg_match_all('/<optgroup( ([^>]*))?>(.*?)<\/optgroup>/is', $attr['#text'], $match1))
        for ($i1 = 0, $c1 = b::len($match1[0]); $i1 < $c1; $i1++){
        	preg_match('/ label=("|\')(.*?)\\1/i', $match1[1][$i1], $tmp);
            $titles[] = $tmp[2];

            if (preg_match_all('/<option( ([^>]*))?>(.*?)<\/option>/is', $match1[3][$i1], $match2))
                for ($i2 = 0, $c2 = b::len($match2[0]); $i2 < $c2; $i2++){                	preg_match('/ value=("|\')(.*?)\\1/i', $match2[1][$i2], $tmp);

                	if (!$i1)
                	    $items[$i2][] = $match2[3][$i2];

                    $items[$i2][] = $tmp[2];                }
        }

    foreach ($items as $k => $v)
        $data[$k + 1] = join('; ', $v).';';

    $data['tTText'] = $attr['title'];
    $data['tLText'] = $attr['left'];
	$data['tRText'] = $attr['right'];
	$data['tBText'] = $attr['bottom'];
	$data['title']  = join('; ', $titles).';';

	$key = md5($data = http_build_query($data, 'data'));
	$attr = array_merge(array(
	    'file'   => 'tag/zxchart/'.$key,
	    'style'  => 'column6',
	    'width'  => 400,
	    'height' => 300
	), $attr);

	$attr['src'] .= '~/ext/tag/zxchart/zxchart.swf';
	$attr['src'] .= '?datafile='.$attr['file'];
	$attr['src'] .= '&RefreshPeriod='.$attr['refresh'];
	$attr['src'] .= '&stylefile=~/ext/tag/zxchart/styles/'.$attr['style'].'.stl';

	if (!is_file($file = $dir.'/'.$key))
	    file_put_contents($file, iconv('utf-8', 'cp1251', $data));

	return b::call('tag_flash', $attr);
}

////////////////////////////////////////////////////////////////////////////////

if (b::q(1, 2) == 'ext/tag/zxchart')	exit(file_get_contents(file::path('tmp').'/tag/zxchart/'.b::q(3)));