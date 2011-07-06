<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Markdown {
    protected static $instance;
    protected $text;

////////////////////////////////////////////////////////////////////////////////

    function __construct($text){
        if (!self::$instance)
            self::$instance = new Markdown_Parser;

        $this->text = $text;
    }

////////////////////////////////////////////////////////////////////////////////

    function transform(){
        return self::$instance->transform($this->text);
    }

////////////////////////////////////////////////////////////////////////////////

    function __toString(){
        return $this->transform();
    }

////////////////////////////////////////////////////////////////////////////////

}