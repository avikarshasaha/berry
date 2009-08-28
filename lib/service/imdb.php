<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Service_IMDb {

////////////////////////////////////////////////////////////////////////////////

    function __construct($title){
        $page = file_get_contents('http://www.imdb.com/find?s=tt&q='.urlencode($title));
        $page = urldecode(preg_replace('/&#x(\d+);/', '%\\1', $page));

        preg_match('/<b>Media from&nbsp;<a href="\/title\/tt(\d+)[^>]*>"(.*?)"<\/a>\s+\((\d+)\)<\/b>/', $page, $match);
        list($_, $this->id, $this->title, $this->year) = $match;

        $this->page = file_get_contents('http://www.imdb.com/title/tt'.$this->id.'/');
        $this->genre = $this->genre();
        $this->cast = $this->cast();
        $this->rating = $this->rating();
        $this->poster = $this->poster();
        $this->creators = $this->creators();
    }

////////////////////////////////////////////////////////////////////////////////

    function genre(){
        preg_match_all('/<a href="\/Sections\/Genres\/[^\/]*\/">([^<]*)<\/a>/i', $this->page, $match);

        return $match[1];
    }

////////////////////////////////////////////////////////////////////////////////

    function cast(){
        preg_match_all('/<td class="nm"><a href="\/name\/nm(\d+)\/">([^<]*)<\/a><\/td><td class="ddd"> ... <\/td><td class="char"><a href="\/character\/ch\d+\/">([^<]*)<\/a>/', $this->page, $match);

        $result = array();

        for ($i = 0, $c = b::len($match[0]); $i < $c; $i++)
            $result[] = array(
                'id' => $match[1][$i],
                'as' => $match[3][$i],
                'name' => $match[2][$i]
            );

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function rating(){
        preg_match('/<b>([^\/]*)\/10<\/b>\s+&nbsp;&nbsp;<a href="ratings" class="tn15more">([^\s]*) votes<\/a>/', $this->page, $match);
        return array(
            'total' => $match[1],
            'votes' => $match[2]
        );
    }

////////////////////////////////////////////////////////////////////////////////

    function poster(){
        preg_match('/src="http:\/\/ia.media-imdb.com\/images\/([^\.]*)._V1._SX\d+_SY\d+_.jpg/', $this->page, $match);
        return 'http://ia.media-imdb.com/images/'.$match[1].'._V1._SX%d_SY%d_.jpg';
    }

////////////////////////////////////////////////////////////////////////////////

    function creators(){
        preg_match('/<h5>Creator(s)?:<\/h5>(.*?)<\/div>/s', $this->page, $match);
        preg_match_all('/<a href="\/name\/nm(\d+)[^>]*>([^<]*)<\/a>/', $match[2], $match);

        $result = array();

        for ($i = 0, $c = b::len($match[0]); $i < $c; $i++)
            $result[] = array('id' => $match[1][$i], 'name' => $match[2][$i]);

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function episodes(){
        $page = file_get_contents('http://www.imdb.com/title/tt'.$this->id.'/episodes');
        $page = urldecode(preg_replace('/&#x(\d+);/', '%\\1', $page));

        preg_match_all('/<td valign="top"><h3>Season (\d+), Episode (\d+): <a href="[^"]*">(.*)<\/a><\/h3>/', $page, $match);

        $result = array();

        for ($i = 0, $c = b::len($match[0]); $i < $c; $i++)
            $result[$match[1][$i]][$match[2][$i]] = $match[3][$i];

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

}