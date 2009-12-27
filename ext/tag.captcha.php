<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_captcha($attr){	$attr = array_merge(array(
	    'img'    => 'hight',
	    'len'    => 4,
	    'border' => 0,
	    'align'  => 'absmiddle'
	), $attr);

	$_SESSION['lib']['captcha'] = md5(rand().microtime());
	$_SESSION['tag']['captcha'] = $attr;

	$attr['src'] = 'tag/captcha/'.$attr['img'].'?'.rand();	return tags::parse(str::format('
	    <div id="ajax[tag_captcha]">
	        <a href="#" ajax:call="tag_captcha" ajax:post="%post">%img</a>
	    </div>
	', array_merge($attr, array('post' => arr::json($attr), 'img' => tags::fill('img', $attr)))));
}

////////////////////////////////////////////////////////////////////////////////

if (b::q(1, 2) == 'tag/captcha'){    $img  = $_SESSION['tag']['captcha']['img'];    $attr = array($_SESSION['tag']['captcha']['len']);

    if ($_SESSION['tag']['captcha']['symbols'])
        $attr[] = $_SESSION['tag']['captcha'];

    exit(call_user_func_array(array(new captcha, $img), $attr));
}