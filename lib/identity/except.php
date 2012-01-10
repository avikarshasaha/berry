<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class IDentity_Except extends Except {

////////////////////////////////////////////////////////////////////////////////

    function __construct($string, $code){
        $this->string = $string;
        $this->code = $code;    }

////////////////////////////////////////////////////////////////////////////////

    function __toString(){
        return '<h1>'.$this->string.': '.$this->code.'</h1>';
    }

////////////////////////////////////////////////////////////////////////////////

}
