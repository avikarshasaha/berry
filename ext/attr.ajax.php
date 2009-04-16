<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function attr_ajax($attr){

	unset($attr['ajax']);

////////////////////////////////////////////////////////////////////////////////

function attr_ajax_call($attr){
	    'ajax_on'   => 'click',
	    'ajax_msg'  => false,
	    'ajax_post' => 'this.form'
	), $attr);

    if ($attr['ajax_call'][0] == '/'){
        $url = 'ajax/'.substr($attr['ajax_call'], 1);
        unset($attr['ajax_call']);
    } else {
    	$url = 'ajax/'.b::q(1, 0);
    }

	if ($attr['ajax_call']){

	    $url .= '&call='.$attr['ajax_call'];
	}

    if ($attr['ajax_get'])
        $url .= '&'.http_build_query(str::json($attr['ajax_get']));

    if ($attr['ajax_msg'])
        $url .= '&msg=1';

    if (!$attr['ajax_post'])
        $attr['ajax_post'] = '{}';

    $id = ($attr['ajax_id'] ? $attr['ajax_id'] : $attr['ajax_call']);
    $loader = ($attr['ajax_loader'] ? $attr['ajax_loader'] : "''");
    $on = 'on'.strtolower($attr['ajax_on']);
    $attr[$on] .= ($attr[$on] ? '; ' : '')."attr_ajax_call('".$url."', '".$id."', ".$loader.", this, ".$attr['ajax_post']."); return false;";

    if (!$attr['ajax_loader'])
        $attr['#before'] .= '<img src="~/attr/ajax.gif" alt="" border="0" style="display: none;" align="absmiddle" />';

    html::block('head', html::js('
        function attr_ajax_call(url, id, loader, that, post){
            var loader = ($("ajax[" + loader + "]") || loader || that.previousSibling);

        	new Ajax.Blueberry("'.b::q(0).'/" + url, {
                onLoading: function(){
                    $(that).disabled = true;
                    $(loader).show();
                },
                onComplete: function(request){
                    $(that).disabled = false;
                    $(loader).hide();
                    $(id).innerHTML = request.responseText;
                },
                onFailure: function(request){
                    $(loader).hide();
                    $(id).innerHTML = request.responseText;
                },
                parameters: post
        	});
        }
    '));
    unset($attr['ajax_id'], $attr['ajax_on'], $attr['ajax_call'], $attr['ajax_post'], $attr['ajax_get'], $attr['ajax_msg'], $attr['ajax_info'], $attr['ajax_loader']);
	return $attr;
}