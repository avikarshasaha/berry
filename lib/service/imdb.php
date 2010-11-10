<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Service_IMDb {    protected $url;
    protected $xpath;
////////////////////////////////////////////////////////////////////////////////

    function __construct($title){
        $page = file_get_contents('http://www.imdb.com/find?s=tt&q='.urlencode($title));
        $page = urldecode(preg_replace('/&#x(\d+);/', '%\\1', $page));

        preg_match('/<b>Media from&nbsp;<a href="\/title\/tt(\d+)[^>]*>"?(.*?)"?<\/a>\s+\((\d+)\)<\/b>/', $page, $match);
        list($_, $this->id, $this->title, $this->year) = $match;

        $doc = new DOMDocument;
        @$doc->loadHTMLFile($this->url = 'http://www.imdb.com/title/tt'.$this->id);
        $this->xpath = new DOMXpath($doc);

        $this->poster = $this->poster();
        $this->rating = $this->rating();
        $this->genre = $this->genre();
        $this->cast = $this->cast();
    }

////////////////////////////////////////////////////////////////////////////////

    protected function genre(){        $result = array();
        $elements = $this->xpath->query('//div[@class="infobar"]/a');

        foreach ($elements as $i => $element)
            $result[] = $element->nodeValue;

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function cast(){
        $result = array();
        $elements1 = $this->xpath->query('//td[@class="name"]/a');
        $elements2 = $this->xpath->query('//td[@class="character"]/div/a');

        foreach ($elements1 as $i => $element)
            $result[] = array(
                'id' => substr($element->getAttribute('href'), 8, -1),
                'name' => $element->nodeValue,
                'as' => $elements2->item($i)->nodeValue
            );

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function rating(){
        $element1 = $this->xpath->query('//span[@id="star-bar-user-rate"]/b');
        $element2 = $this->xpath->query('//a[@href="ratings"]');

        return array(
            'total' => $element1->item(0)->nodeValue,
            'votes' => str_replace(',', '.', trim(substr($element2->item(0)->nodeValue, 0, -5)))
        );
    }

////////////////////////////////////////////////////////////////////////////////

    protected function poster(){
        $element = $this->xpath->query('//td[@id="img_primary"]/a/img');

        if ($src = $element->item(0)->getAttribute('src')){
            $src = substr($src, 0, strpos($src, '@'));
            return $src.'@@._V1._SX%d_SY%d_.jpg';
        }
    }

////////////////////////////////////////////////////////////////////////////////

}