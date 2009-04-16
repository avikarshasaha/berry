<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Service_LastFM {
////////////////////////////////////////////////////////////////////////////////

    function __call($method, $params){        list($type, $that) = explode('_', strtolower($method), 2);
        $url .= 'http://ws.audioscrobbler.com/1.0/';
        $url .= $type.'/'.urlencode($params[0]).'/';
        $url .= (is_string($params[1]) ? urlencode($params[1]).'/' : '');
        $url .= $that.'.xml';
        $url .= (is_array($query = end($params)) ? '?'.http_build_query($query) : '');

        $status = reset(get_headers($url));
        $status = trim(strstr($status, ' '));

        unset($this->error);

        if ($status == '200 OK')
            return simplexml_load_file($url);

        $this->error = $status;
    }

////////////////////////////////////////////////////////////////////////////////
}