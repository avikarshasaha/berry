<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function container_onplace($attr){
	$attr = array_merge(array(
	    'id' => 'onplace'
	), $attr);

	b::l('_session.tag.onplace', $attr);
	return tags::parse(tags::fill('span', array_merge($attr, array('id' => 'ajax['.$attr['id'].']'))));
}

////////////////////////////////////////////////////////////////////////////////

function onplace(){
	if ($attr = b::l('_session.tag.onplace'))
	    return container_onplace($attr);
}

////////////////////////////////////////////////////////////////////////////////

function container_onplace_1($attr){	return container_onplace($attr);}

////////////////////////////////////////////////////////////////////////////////

function container_onplace_2($attr){
    $attr = array_merge(array(
        'id' => 'onplace2'
    ), $attr);

    b::l('_session.tag.onplace_2', $attr);
    return tags::parse(tags::fill('span', array_merge($attr, array('id' => 'ajax['.$attr['id'].']'))));
}

////////////////////////////////////////////////////////////////////////////////

function onplace2(){
    if ($attr = b::l('_session.tag.onplace_2'))
        return container_onplace_2($attr);
}

////////////////////////////////////////////////////////////////////////////////

function container_onplace_3($attr){
    $attr = array_merge(array(
        'id' => 'onplace3'
    ), $attr);

    b::l('_session.tag.onplace_3', $attr);
    return tags::parse(tags::fill('span', array_merge($attr, array('id' => 'ajax['.$attr['id'].']'))));
}

////////////////////////////////////////////////////////////////////////////////

function onplace3(){
    if ($attr = b::l('_session.tag.onplace_3'))
        return container_onplace_3($attr);
}

////////////////////////////////////////////////////////////////////////////////

function container_onplace_4($attr){
    $attr = array_merge(array(
        'id' => 'onplace4'
    ), $attr);

    b::l('_session.tag.onplace_4', $attr);
    return tags::parse(tags::fill('span', array_merge($attr, array('id' => 'ajax['.$attr['id'].']'))));
}

////////////////////////////////////////////////////////////////////////////////

function onplace4(){
    if ($attr = b::l('_session.tag.onplace_4'))
        return container_onplace_4($attr);
}

////////////////////////////////////////////////////////////////////////////////

function container_onplace_5($attr){
    $attr = array_merge(array(
        'id' => 'onplace5'
    ), $attr);

    b::l('_session.tag.onplace_5', $attr);
    return tags::parse(tags::fill('span', array_merge($attr, array('id' => 'ajax['.$attr['id'].']'))));
}

////////////////////////////////////////////////////////////////////////////////

function onplace5(){
    if ($attr = b::l('_session.tag.onplace_5'))
        return container_onplace_5($attr);
}