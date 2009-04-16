<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
tags::skip('poll');
function tag_poll($attr){
	$attr = array_merge(array(
	    'static'   => false,
	    'multiple' => false
	), $attr);

    data::create('poll', array(
        'aid'      => array('type' => 'string'),
        'question' => array('type' => 'string'),
        'data'     => array('type' => 'text'),
        'total'    => array('type' => 'int'),
        'md5'      => array('type' => 'string')
    ));

    list($poll, $static) = _poll_array($attr['id']);

    if (preg_match_all('/<option( (.*?))?>(.*?)<\/option>/si', $attr['#text'], $match)){    	if (is_array($array = unserialize($poll['data'])))        	foreach ($array as $k => $v)
        	    $array[$k]['disabled'] = 1;

        for ($i = 0, $c = b::len($match[0]); $i < $c; $i++){        	preg_match('/ id=("|\')(.*?)\\1/i', $match[1][$i], $id);
        	preg_match('/ disabled=("|\')(.*?)\\1/i', $match[1][$i], $disabled);
        	preg_match('/ sort=("|\')(.*?)\\1/i', $match[1][$i], $sort);

        	$id = ($id[2] ? $id[2] : $i);        	$array[$id]['votes'] = (float)$array[$id]['votes'];
        	$array[$id]['answer'] = $match[3][$i];
        	$array[$id]['disabled'] = ($disabled[2] ? 1 : 0);
        	$array[$id]['sort'] = ($sort[2] ? $sort[2] : $i);
        }

        $data['aid'] = $attr['id'];
        $data['md5'] = md5($attr['question'].$attr['#text']);
        $data['data'] = serialize($array);

        if ($attr['question'])
            $data['question'] = $attr['question'];

        if (!$poll){
            sql::query('insert into [poll] set ?a', $data);
            list($poll, $static) = _poll_array($attr['id']);
        } elseif ($poll['md5'] != $data['md5']){        	$poll = array_merge($poll, $data);
        	sql::query('update [poll] set ?a where aid = ?', $poll, $attr['id']);
        }
    }

    if (!$poll = reset(arr::assoc($poll)))
        return;
	$poll['id'] = $attr['id'];
	$poll['data'] = unserialize($poll['data']);
	$poll['multiple'] = (bool)$attr['multiple'];
	$poll['order'] = $attr['order'];

	foreach ($poll['data'] as $k => $v)
	    $poll['data'][$k]['percent'] = ($poll['total'] ? round($v['votes'] / ($poll['total'] / 100)) : 0).'%';

    if ($attr['order']){        $order = arr::trim(explode(' ', $attr['order']));
        $order[0] = strtolower($order[0]);
        $order[1] = strtolower($order[1] ? $order[1] : 'asc');

    	if (
    	    in_array($order[0], array('votes', 'answer', 'disabled', 'sort', 'percent')) and
    	    in_array($order[1], array('asc', 'desc'))
    	){    		foreach ($poll['data'] as $row)
    		    $tmp[] = $row[$order[0]];

    	    array_multisort($tmp, constant(strtoupper('sort_'.$order[1])), $poll['data']);
    	}    }

    if ($attr['cookie'])
        $static = http::cookie('tag.poll.'.$attr['id']);

    if ($static or $attr['static'])
        $result = b::show('tag.poll.result');
    else
        $result = b::show('tag.poll.index');

	return tags::parse_lvars($result, 'poll', $poll);
}

////////////////////////////////////////////////////////////////////////////////

function poll_ajax($params){
	if (array_key_exists('vote', $params)){
	    if (!$query = sql::getRow('select id, data from [poll] where aid = ?', $params['id']))
	        return;

        if (sql::query('select 1 from [poll_user] where poll.id = ? and user_id = ?', $query['id'], b::l('member.id'))){        	$params['static'] = true;
        	return tag_poll($params);        }

	    $data = unserialize($query['data']);
	    $vote = (array)$params['vote'];
	    $votes = b::len($vote);

	    foreach ($vote as $k)
            $data[$k]['votes'] += round(1 / $votes, 2);

		sql::query('update [poll] set data = ?, total = (total + 1) where aid = ?', serialize($data), $params['id']);
		http::cookie('tag.poll.'.$params['id'], true, '+1 day');
		data::append('poll&user', $query['id'], b::l('member.id'), true);
	}

	$params['static'] = true;
	return tag_poll($params);
}

////////////////////////////////////////////////////////////////////////////////

function _poll_array($id){    if ($poll = sql::getRow('select * from [poll] where aid = ?', $id))
    	return array(
    	    $poll,
    	    sql::query('select 1 from [poll_user] where poll_id = ? and user_id = ?', $poll['id'], l('member.id'))
    	 );}