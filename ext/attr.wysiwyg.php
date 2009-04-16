<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
function attr_wysiwyg($attr){
    if (function_exists($func = 'attr_wysiwyg_'.$attr['wysiwyg']))
        return call_user_func($func, $attr);

    unset($attr['wysiwyg']);
    return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_wysiwyg_markitup($attr){
    $attr['id'] = ($attr['id'] ? $attr['id'] : $attr['name']);
    html::block('head',
        html::css('~/attr/wysiwyg/markitup/style/skin.css').
        html::css('~/attr/wysiwyg/markitup/style/toolbar.css').
        html::js('~/attr/wysiwyg/markitup/jquery.markitup.pack.js')
    );
    html::block('head',
        html::js('~/attr/wysiwyg/markitup/settings/'.$attr['wysiwyg_markitup'].'.js').
        html::js('
            $J(document).ready(function(){
                $J("#'.$attr['id'].'").markItUp(settings);
            });
        ')
    );

    unset($attr['wysiwyg_markitup']);
    return $attr;
}

////////////////////////////////////////////////////////////////////////////////

function attr_wysiwyg_spaw($attr){    unset($attr['wysiwyg_spaw']);

    include file::path('lib/spaw2/spaw.inc.php');

    $spaw = new SpawEditor($attr['name']);
    $attr['#skip'] = supadupa_skip(array('#text' => $spaw->getHTML()));

    return $attr;
}

if (is_file(file::path('lib/spaw2/spaw.inc.php')) and b::q(-4, 0) == 'lib/spaw2/js/spaw.js.php'){
    include file::path('lib/spaw2/js/spaw.js.php');
    exit;
}

////////////////////////////////////////////////////////////////////////////////

function attr_wysiwyg_fckeditor($attr){    unset($attr['wysiwyg_fckeditor']);

    $fckeditor = new FCKeditor($attr['name']);
    $fckeditor->BasePath = '~/lib/fckeditor/';
    $attr['#skip'] = $fckeditor->createHTML();

    return $attr;}