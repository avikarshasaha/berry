<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function attr_ajax($attr){	$attr['ajax_call'] = $attr['ajax'];

	unset($attr['ajax']);	return attr_ajax_call($attr);}

////////////////////////////////////////////////////////////////////////////////

function attr_ajax_call($attr){	$attr = array_merge(array(
	    'ajax_on' => 'click',
	    'ajax_nocache' => 'false'
	), $attr);
	$ajax = piles::attr_group('ajax', $attr);

    if ($ajax['call'][0] == '/'){
        $url = 'ajax/'.substr($ajax['call'], 1);
	} elseif ($ajax['call']){
        $_SESSION['ajax'][$ajax['call']] = true;
	    $url .= 'ajax/?call='.$ajax['call'];
	} else {
    	$url = 'ajax/'.b::q(1, 0);
    }

    if (!$ajax['post'])
        $ajax['post'] = 'null';

    $attr['#before'] .= piles::fill('img', array(
        'src' => '~/attr/ajax.gif',
        'alt' => '[*]',
        'style' => 'display: none;',
        'align' => 'absmiddle'
    ));

    $url .= ($ajax['get'] ? '&'.http_build_query(str::json($ajax['get'])) : '');
    $id = ($ajax['id'] ? $ajax['id'] : $ajax['call']);
    $on = 'on'.strtolower($ajax['on']);
    $attr[$on] .= ($attr[$on] ? '; ' : '')."attr_ajax_call('".$url."', '".$id."', this, ".$ajax['post'].", ".$ajax['nocache']."); return false;";
    html::block('head', html::js('
        function attr_ajax_call(url, id, that, data, nocache){
            that.disabled = true;
            that.previousSibling.style.display = "inline-block";

            JsHttpRequest.query(
                "'.b::q(0).'/" + url, data || that.form || {"#": ""},
                function(_, result){                    that.disabled = false;
                    that.previousSibling.style.display = "none";

                    var block = document.getElementById("ajax[" + id + "]") || document.getElementById(id);
                    block.innerHTML = result;
                }, nocache
            );
        }
    '));

    foreach ($ajax as $k => $v)
        unset($attr['ajax_'.$k]);

    return $attr;}