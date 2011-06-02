<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_player($attr){    $ext = strtolower(pathinfo($attr['src'], PATHINFO_EXTENSION));
    $func = 'tag_player_'.($ext == 'mp3' ? 'audio' : 'video');
    return $func($attr);}

////////////////////////////////////////////////////////////////////////////////

// http://wpaudioplayer.com/standalone
function tag_player_audio($attr){    static $i = 0;

    $attr = array_merge((array)b::config('tag.player_audio'), $attr);

    html::block('head', html::js('~/ext/tag/swfobject.js'));
    html::block('head',
        html::js('~/ext/tag/player/audio/player.js').
        html::js('AudioPlayer.setup("~/ext/tag/player/audio/player.swf", {width: 300, transparentpagebg: "yes", initialvolume: 100});')
    );

    $attr['id'] = 'tag_player_audio['.$i++.']';
    $params = $attr;
    $params['soundFile'] = $params['src'];

    unset($params['id'], $params['src'], $params['#is_final'], $params['#tag']);
    return piles::fill('span', array('id' => $attr['id'], '#text' => $attr['#text'])).
           html::js('AudioPlayer.embed("'.$attr['id'].'", '.arr::json($params).');');
}

////////////////////////////////////////////////////////////////////////////////

function tag_player_video($attr){
    $attr = array_merge(array(
    	'params_allowfullscreen' => true,
    	'params_allownetworking' => 'all',
    	'params_allowscriptaccess' => 'always',
        'flashvars_skin' => 'modieus',
        'flashvars_viral.onpause' => false,
        'flashvars_viral.functions' => false
    ),(array)b::config('tag.player_video'), $attr);

    $attr['flashvars_skin'] = '~/ext/tag/player/video/'.$attr['flashvars_skin'].'.swf';
	$attr['flashvars_file'] = $attr['src'];
	$attr['flashvars_image'] = $attr['image'];
	$attr['src'] = '~/ext/tag/player/video/player.swf';

    return b::call('tag_swfobject', $attr);
}