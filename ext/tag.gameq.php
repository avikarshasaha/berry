<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
tags::skip('gameq');
function tag_gameq($attr){	static $gameq, $games;

	list($ip, $port) = explode(':', $attr['server'], 2);

    if (!$gameq)
        $gameq = new GameQ;

	if (!$games)
	    foreach (parse_ini_file(GAMEQ_BASE.'/games.ini', true) as $k => $v)
	        $games[$k] = $games[$v['name']] = $k;

    $server[] = $games[$attr['game']];
    $server[] = $ip;

	if ($port)
	    $server[] = $port;

    try {    	$gameq->addServers(array($server));
        $data = reset($gameq->requestData());

        if ($data['players'])
            $data['players'] = arr::html($data['players']);

    	return tags::parse_lvars($attr, $data);
    } catch (GameQ_Exception $e){
    }
}