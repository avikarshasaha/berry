<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Markdown {
    protected $text;
    protected static $instance;

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
