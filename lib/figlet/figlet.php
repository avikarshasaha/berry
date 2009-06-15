<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class FiGlet {
////////////////////////////////////////////////////////////////////////////////

    function __construct($text, $font = 'slant.flf'){        static $figlet;

        if (!$figlet)
            $figlet = new Text_FiGlet;

        $this->figlet = $figlet;
        $this->text = $text;
        $this->font = $font;    }

////////////////////////////////////////////////////////////////////////////////

    function __toString(){
        $error = $this->figlet->LoadFont(dirname(__file__).'/font/'.$this->font); // $error['message']
        return $this->figlet->LineEcho($this->text);
    }

////////////////////////////////////////////////////////////////////////////////
}