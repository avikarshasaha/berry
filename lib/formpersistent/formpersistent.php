<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
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