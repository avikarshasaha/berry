<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class FormPersistent {////////////////////////////////////////////////////////////////////////////////

    function __construct($output){        $persistent = new HTML_FormPersister;
        $this->output = $persistent->process($output);    }

////////////////////////////////////////////////////////////////////////////////

    function __toString(){
        return $this->output;
    }

////////////////////////////////////////////////////////////////////////////////
}