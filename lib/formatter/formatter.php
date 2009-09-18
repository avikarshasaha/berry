<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Formatter {    protected static $instance = array();////////////////////////////////////////////////////////////////////////////////

    // http://www.hobix.com/textile/
    static function textile($output, $params = array()){
        if (!isset(self::$instance['textile']))
            self::$instance['textile'] = new Textile;

        return self::$instance['textile']->TextileThis(trim($output), $params['lite'], $params['encode'], $params['noimage'], $params['strict'], $params['rel']);
    }

////////////////////////////////////////////////////////////////////////////////

    // http://daringfireball.net/projects/markdown/syntax/
    static function markdown($output){
        if (!isset(self::$instance['markdown']))
            self::$instance['markdown'] = new Markdown_Parser;

        return self::$instance['markdown']->transform(trim($output));
    }

////////////////////////////////////////////////////////////////////////////////

    static function bbcode($output){
        if (!isset(self::$instance['bbcode']))
            self::$instance['bbcode'] = new BBCode;

        return nl2br(self::$instance['bbcode']->parse(trim($output)));
    }

////////////////////////////////////////////////////////////////////////////////

    // http://wackowiki.com/WackoDocumentation/WackoFormatting/
    static function wacko($output){
        if (!isset(self::$instance['wacko']))
            self::$instance['wacko'] = new WackoFormatter;

        return unhtml(self::$instance['wacko']->format($output));
    }

////////////////////////////////////////////////////////////////////////////////

    static function jevix($output){        if (!isset(self::$instance['jevix'])){
            $jevix = new Jevix;

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

            self::$instance['jevix'] = $jevix;
        }

        return substr(self::$instance['jevix']->parse('<p>'.$output.'</p>'), 3, -4);
    }

////////////////////////////////////////////////////////////////////////////////
}