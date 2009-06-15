<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Formatter {////////////////////////////////////////////////////////////////////////////////

    // http://www.hobix.com/textile/
    function textile($output, $params){
        static $textile;

        if (!$textile)
            $textile = new Textile;

        return $textile->TextileThis(trim($output), $params['lite'], $params['encode'], $params['noimage'], $params['strict'], $params['rel']);
    }

////////////////////////////////////////////////////////////////////////////////

    // http://daringfireball.net/projects/markdown/syntax
    function markdown($output){
        static $markdown;

        if (!$markdown)
            $markdown = new Markdown_Parser;

        return $markdown->transform(trim($output));
    }

////////////////////////////////////////////////////////////////////////////////

    function bbcode($output){
        static $bbcode;

        if (!$bbcode)
            $bbcode = new BBCode;

        return nl2br($bbcode->parse(trim($output)));
    }

////////////////////////////////////////////////////////////////////////////////

    // http://wackowiki.com/WackoDocumentation/WackoFormatting
    function wacko($output){
        static $wacko;

        if (!$wacko)
            $wacko = new WackoFormatter;

        return unhtml($wacko->format($output));
    }

////////////////////////////////////////////////////////////////////////////////

    function jevix($output){        static $jevix;

        if (!$jevix){            $jevix = new Jevix;

            // Разрешённые теги
            $jevix->cfgAllowTags(array('a', 'img', 'i', 'b', 'u', 'em', 'strong', 'li', 'ol', 'ul', 'sup', 'sub', 'pre', 'acronym', 'code', 'quote', 'blockquote', 'small', 'p', 'strike', 'del', 'br'));

            // Коротие теги типа
            $jevix->cfgSetTagShort(array('img', 'br'));

            // Преформатированные теги
            $jevix->cfgSetTagPreformatted(array('pre', 'code'));

            // Разрешённые параметры тегов
            $jevix->cfgAllowTagParams('code', array('escape', 'pre'));
            $jevix->cfgAllowTagParams('img', array('src', 'alt', 'title', 'width', 'height'));
            $jevix->cfgAllowTagParams('a', array('title', 'href'));
            $jevix->cfgAllowTagParams('p', 'align');
            $jevix->cfgAllowTagParams('quote', 'cite');
            $jevix->cfgAllowTagParams('blockquote', 'cite');
            $jevix->cfgAllowTagParams('acronym', 'title');

            // Параметры тегов являющиеся обязяательными
            $jevix->cfgSetTagParamsRequired('img', 'src');
            $jevix->cfgSetTagParamsRequired('a', 'href');
            $jevix->cfgSetTagParamsRequired('acronym', 'title');

            // Теги которые необходимо вырезать из текста вместе с контентом
            $jevix->cfgSetTagCutWithContent(array('script', 'object', 'iframe', 'style'));

            // Нахуй типографику
            $jevix->cfgSetTagNoTypography('p');
            $jevix->entities0 = array();
            $jevix->entities2 = array();
        }

        return substr($jevix->parse('<p>'.$output.'</p>'), 3, -4);
    }

////////////////////////////////////////////////////////////////////////////////
}