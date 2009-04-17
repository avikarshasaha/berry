<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class service {

////////////////////////////////////////////////////////////////////////////////

    function LJ($username, $password, $journal = ''){
    	return new Service_LJ($username, $password, $journal);
    }

////////////////////////////////////////////////////////////////////////////////

    function LastFM(){    	static $lastfm;

    	if (!$lastfm)
    	    $lastfm = new Service_LastFM;

    	return $lastfm;
    }

////////////////////////////////////////////////////////////////////////////////

}