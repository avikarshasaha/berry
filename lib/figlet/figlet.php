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

    function __construct($text, $font = 'slant.flf', $german = true){        static $figlet;

        if (!$figlet)
            $figlet = new Text_FiGlet;

        $this->figlet = $figlet;
        $this->text = $text;
        $this->font = $font;
        $this->german = $german;    }

////////////////////////////////////////////////////////////////////////////////

    function show($html = false){
        $this->figlet->LoadFont(dirname(__file__).'/font/'.$this->font, $this->german);
        return $this->figlet->LineEcho($this->text, $html);
    }

////////////////////////////////////////////////////////////////////////////////

    function __toString(){
        return $this->show();
    }

////////////////////////////////////////////////////////////////////////////////
}