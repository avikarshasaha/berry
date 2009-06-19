<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Service {

////////////////////////////////////////////////////////////////////////////////

    static function LJ($username, $password, $journal = ''){
    	return new Service_LJ($username, $password, $journal);
    }

////////////////////////////////////////////////////////////////////////////////

    static function LastFM(){    	static $lastfm;

    	if (!$lastfm)
    	    $lastfm = new Service_LastFM;

    	return $lastfm;
    }

////////////////////////////////////////////////////////////////////////////////

}