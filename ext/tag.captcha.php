<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function tag_captcha($attr){
	    'img'    => 'hight',
	    'len'    => 4,
	    'border' => 0,
	    'align'  => 'absmiddle'
	), $attr);

	$_SESSION['captcha'] = md5(rand().microtime());
	$_SESSION['tag']['captcha'] = $attr;

	$attr['src'] = 'tag/captcha/'.$attr['img'].'?'.rand();
	    <div id="ajax[tag_captcha]">
	        <a href="#" ajax:call="tag_captcha" ajax:post="%post">%img</a>
	    </div>
	', array_merge($attr, array('post' => arr::json($attr), 'img' => tags::fill('img', $attr)))));
}

////////////////////////////////////////////////////////////////////////////////

if (b::q(1, 2) == 'tag/captcha'){

    if ($_SESSION['tag']['captcha']['symbols'])
        $attr[] = $_SESSION['tag']['captcha'];

    exit(call_user_func_array(array(new captcha, $img), $attr));
}