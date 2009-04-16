<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
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