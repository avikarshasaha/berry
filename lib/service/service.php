<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Service {    protected static $instance = array();

////////////////////////////////////////////////////////////////////////////////

    static function LJ($username, $password, $journal = ''){
    	return new Service_LJ($username, $password, $journal);
    }

////////////////////////////////////////////////////////////////////////////////

    static function LastFM(){    	if (!isset(self::$instance['lastfm']))
    	    self::$instance['lastfm'] = new Service_LastFM;

    	return self::$instance['lastfm'];
    }

////////////////////////////////////////////////////////////////////////////////

    static function IMDb($title){        if (!isset(self::$instance['imdb'][$title]))
            self::$instance['imdb'][$title] = new Service_IMDb($title);

    	return self::$instance['imdb'][$title];
    }

////////////////////////////////////////////////////////////////////////////////

    static function CBR($currency, $date = ''){
        $date = date('d.m.Y', date::time($date));

        if (!$array = cache::get('service/cbr/'.$date.'.php')){
            $xml = simplexml_load_file('http://cbr.ru/scripts/XML_daily.asp?date_req='.$date);
            $array = array();

            foreach ($xml->xpath('//Valute') as $key => $xml){
                $id = (string)$xml->attributes()->ID;
                $name = (string)$xml->Name;
                $code = (int)$xml->NumCode;
                $value = (str_replace(',', '.', $xml->Value) / $xml->Nominal);

                $array[(string)$xml->CharCode] = compact('id', 'name', 'code', 'value');
            }

            cache::set($array);
        }

        return $array[strtoupper($currency)]['value'];
    }

////////////////////////////////////////////////////////////////////////////////

}
