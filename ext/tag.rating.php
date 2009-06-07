<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_rating($attr){
	$attr = array_merge(array(
	    'items'  => 5,
	    'static' => false,
	    'img'    => 'star',
	    'rel'    => ''
	), $attr);

	$_SESSION['tag']['rating'][$attr['id']] = $attr;
	return '<!--tag[rating]['.$attr['id'].']-->';
}

////////////////////////////////////////////////////////////////////////////////

function output_tag_rating($output){
    if (!is_array($_SESSION['tag']['rating']))
        return $output;

    $css = file_get_contents(file::path('show/tag/rating/style.css'));
    $rating = sql::query('select votes, total, aid as array_key from [rating] where aid in (?a)', array_keys($_SESSION['tag']['rating']));

    foreach ($_SESSION['tag']['rating'] as $id => $attr){
        if (!is_file($img = file::path('show/tag/rating/'.$attr['img'].'.gif')))
            continue;

        $s = reset(getimagesize($img));
        $w = ($attr['items'] * $s);
        $r = 0;

        if ($rating[$id])
            $r = (int)(number_format($rating[$id]['total'] / $rating[$id]['votes'], 2) * $s);

        $result = $style = '';
        for ($i = 1; $i <= $attr['items']; $i++){
            $result .= '<li>';
            $result .= tags::fill('a', array(
                'class' => 'r'.$i,
                'href'  => '#',
                '#text' => $i,
                'ajax'  => array(
                    'id'     => 'rating_'.$id,
                    'call'   => 'rating_ajax',
                    'msg'    => 'rating_'.$id,
                    'post'   => arr::json(array('id' => $id, 'value' => $i)),
                    'loader' => 'tag_rating_'.str::translit($id, '_')
                )
            ));
            $result .= '</li>';
            $style  .= '
                .rating-'.$attr['img'].' a.r'.$i.' {
                    left: '.($i > 1 ? $h : 0).'px;
                }

                .rating-'.$attr['img'].' a.r'.$i.':hover {
                    width: '.($h = ($i > 1 ? ($s * $i) : $s)).'px;
                }
            ';
        }

        block('head', str::format(html::css($css.$style), array_merge($attr, array('size' => $s.'px'))));

        if (http::cookie('tag.rating.'.$id) or $attr['static'])
            $result = '
                <ul class="rating-'.$attr['img'].'" style="width: '.$w.'px;">
                    <li class="current" style="width: '.$r.'px;"></li>
                </ul>
            ';
        else
            $result = '
                <div id="tag_rating_'.str::translit($id, '_').'"></div>
    	        <ul class="rating-'.$attr['img'].'" style="width: '.$w.'px;" id="ajax[rating_'.$id.']">
    	            <li class="current" style="width: '.$r.'px;"></li>
    	            '.$result.'
    	        </ul>
        	';

        if ($attr['#text']){            $rating[$id]['rating'] = $result;
            $rating[$id]['ratio'] = ($r / $s);
            $rating[$id]['percent'] = $r.'%';
            $result = tags::parse_lvars($attr, $rating[$id]);        }

        $output = str_replace('<!--tag[rating]['.$id.']-->', str_replace("\r\n", '', $result), $output);
    }

    return $output;
}

////////////////////////////////////////////////////////////////////////////////

function rating_ajax($params){    data::create('rating', array(
        'aid'   => array('type' => 'string'),
        'votes' => array('type' => 'int'),
        'total' => array('type' => 'int')
    ));

    if ((!$check = $_SESSION['tag']['rating'][$params['id']]) or $params['static'])
        return;

    $params = array_merge($params, $check);
    $params['value'] = max(1, min($params['value'], $params['value']));
	if ($id = sql::getCell('select id from [rating] where aid = ?', $params['id'])){
	    sql::query('update [rating] set total = (total + ?), votes = (votes + 1) where id = ?', $params['value'], $id);
	} else {
	    $id = sql::query('insert into [rating] set total = ?, votes = 1, aid = ? ', $params['value'], $params['id']);

	    if ($params['rel']){	    	list($table, $rel) = explode('.', $params['id'], 2);
	        data::append($table.'&rating', $rel, $id);
	    }
	}

	$params['static'] = true;
    http::cookie('tag.rating.'.$params['id'], true, '+1 day');
    return output_tag_rating(tag_rating($params));
}