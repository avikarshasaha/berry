<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Node extends SQL {////////////////////////////////////////////////////////////////////////////////

    function __construct($type = '', $id = 0){
        parent::__construct($id);

        $config = b::config('lib.node');
        $this->relations = array_merge($this->relations, self::deep_throat(array($this->table => $config)));

    }

////////////////////////////////////////////////////////////////////////////////
}