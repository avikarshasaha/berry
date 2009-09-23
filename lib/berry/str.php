<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Str {////////////////////////////////////////////////////////////////////////////////

    static function clean($chicken, $dick = '/'){
        $chicken = trim($chicken, $dick);
        $chicken = preg_replace('/['.preg_quote($dick, '/').']+/', $dick, $chicken);

        return $chicken;
    }

////////////////////////////////////////////////////////////////////////////////

    static function format($string, $array = array()){        $string = str_replace('\%', tags::char('%'), $string);

        if (is_array($array) and b::len($array)){
            $array = arr::flat($array);
            $func = create_function('$match', 'return trim($match[1]);');

            foreach ($array as $k => $v){
                $k2 = preg_quote($k, '/');

                if ($v){
                    $string = preg_replace_callback('/\%if:'.$k2.'(.*?)\%\/if:'.$k2.'/s', $func, $string);
                    $string = preg_replace('/\%if_not:'.$k2.'(.*?)\%\/if_not:'.$k2.'/s', '', $string);
                } else {
                    $string = preg_replace_callback('/\%if_not:'.$k2.'(.*?)\%\/if_not:'.$k2.'/s', $func, $string);
                    $string = preg_replace('/\%if:'.$k2.'(.*?)\%\/if:'.$k2.'/s', '', $string);
                }

                $string = str_replace('%'.$k, $v, $string);
            }

            if (strpos($string, '%call:') !== false and strpos($string, '%/call:') !== false)
                $string = preg_replace('/\%call:([\w\:]+)(.*?)\%\/call:\\1/se', "call_user_func_array(array('b', 'call'), arr::trim(explode(',', '\\1, \\2')))", $string);
        }

        return str_replace(tags::char('%'), '\%', $string);;
    }

////////////////////////////////////////////////////////////////////////////////

    static function unhtml($string, $quote_style = ENT_QUOTES){
        return htmlspecialchars($string, $quote_style);
    }

////////////////////////////////////////////////////////////////////////////////

    static function html($string, $quote_style = ENT_QUOTES){
        return htmlspecialchars_decode($string, $quote_style);
    }

////////////////////////////////////////////////////////////////////////////////

    static function md5($string){
        return md5(md5($string));
    }

////////////////////////////////////////////////////////////////////////////////

    static function translit($text, $that = '-'){        // http://textpattern.com/
        static $map = array('À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Ç' => 'C', 'Ć' => 'C', 'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ę' => 'E', 'Ě' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G', 'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I', 'İ' => 'I', 'Ĳ' => 'IJ', 'Ĵ' => 'J', 'Ķ' => 'K', 'Ľ' => 'K', 'Ĺ' => 'K', 'Ļ' => 'K', 'Ŀ' => 'K', 'Ł' => 'L', 'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N', 'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O', 'Ŏ' => 'O', 'Œ' => 'OE', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Ş' => 'S', 'Ŝ' => 'S', 'Ș' => 'S', 'Š' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T', 'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue', 'Ū' => 'U', 'Ů' => 'U', 'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U', 'Ŵ' => 'W', 'Ŷ' => 'Y', 'Ÿ' => 'Y', 'Ý' => 'Y', 'Ź' => 'Z', 'Ż' => 'Z', 'Ž' => 'Z', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c', 'ď' => 'd', 'đ' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ƒ' => 'f', 'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h', 'ħ' => 'h', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i', 'ĩ' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĳ' => 'ij', 'ĵ' => 'j', 'ķ' => 'k', 'ĸ' => 'k', 'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l', 'ŀ' => 'l', 'ñ' => 'n', 'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n', 'ŋ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o', 'ŏ' => 'o', 'œ' => 'oe', 'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'ś' => 's', 'š' => 's', 'ť' => 't', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ū' => 'u', 'ů' => 'u', 'ű' => 'u', 'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ÿ' => 'y', 'ý' => 'y', 'ŷ' => 'y', 'ż' => 'z', 'ź' => 'z', 'ž' => 'z', 'ß' => 'ss', 'ſ' => 'ss', 'Α' => 'A', 'Ά' => 'A', 'Ἀ' => 'A', 'Ἁ' => 'A', 'Ἂ' => 'A', 'Ἃ' => 'A', 'Ἄ' => 'A', 'Ἅ' => 'A', 'Ἆ' => 'A', 'Ἇ' => 'A', 'ᾈ' => 'A', 'ᾉ' => 'A', 'ᾊ' => 'A', 'ᾋ' => 'A', 'ᾌ' => 'A', 'ᾍ' => 'A', 'ᾎ' => 'A', 'ᾏ' => 'A', 'Ᾰ' => 'A', 'Ᾱ' => 'A', 'Ὰ' => 'A', 'Ά' => 'A', 'ᾼ' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Έ' => 'E', 'Ἐ' => 'E', 'Ἑ' => 'E', 'Ἒ' => 'E', 'Ἓ' => 'E', 'Ἔ' => 'E', 'Ἕ' => 'E', 'Έ' => 'E', 'Ὲ' => 'E', 'Ζ' => 'Z', 'Η' => 'I', 'Ή' => 'I', 'Ἠ' => 'I', 'Ἡ' => 'I', 'Ἢ' => 'I', 'Ἣ' => 'I', 'Ἤ' => 'I', 'Ἥ' => 'I', 'Ἦ' => 'I', 'Ἧ' => 'I', 'ᾘ' => 'I', 'ᾙ' => 'I', 'ᾚ' => 'I', 'ᾛ' => 'I', 'ᾜ' => 'I', 'ᾝ' => 'I', 'ᾞ' => 'I', 'ᾟ' => 'I', 'Ὴ' => 'I', 'Ή' => 'I', 'ῌ' => 'I', 'Θ' => 'TH', 'Ι' => 'I', 'Ί' => 'I', 'Ϊ' => 'I', 'Ἰ' => 'I', 'Ἱ' => 'I', 'Ἲ' => 'I', 'Ἳ' => 'I', 'Ἴ' => 'I', 'Ἵ' => 'I', 'Ἶ' => 'I', 'Ἷ' => 'I', 'Ῐ' => 'I', 'Ῑ' => 'I', 'Ὶ' => 'I', 'Ί' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => 'KS', 'Ο' => 'O', 'Ό' => 'O', 'Ὀ' => 'O', 'Ὁ' => 'O', 'Ὂ' => 'O', 'Ὃ' => 'O', 'Ὄ' => 'O', 'Ὅ' => 'O', 'Ὸ' => 'O', 'Ό' => 'O', 'Π' => 'P', 'Ρ' => 'R', 'Ῥ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Ύ' => 'Y', 'Ϋ' => 'Y', 'Ὑ' => 'Y', 'Ὓ' => 'Y', 'Ὕ' => 'Y', 'Ὗ' => 'Y', 'Ῠ' => 'Y', 'Ῡ' => 'Y', 'Ὺ' => 'Y', 'Ύ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'O', 'Ώ' => 'O', 'Ὠ' => 'O', 'Ὡ' => 'O', 'Ὢ' => 'O', 'Ὣ' => 'O', 'Ὤ' => 'O', 'Ὥ' => 'O', 'Ὦ' => 'O', 'Ὧ' => 'O', 'ᾨ' => 'O', 'ᾩ' => 'O', 'ᾪ' => 'O', 'ᾫ' => 'O', 'ᾬ' => 'O', 'ᾭ' => 'O', 'ᾮ' => 'O', 'ᾯ' => 'O', 'Ὼ' => 'O', 'Ώ' => 'O', 'ῼ' => 'O', 'α' => 'a', 'ά' => 'a', 'ἀ' => 'a', 'ἁ' => 'a', 'ἂ' => 'a', 'ἃ' => 'a', 'ἄ' => 'a', 'ἅ' => 'a', 'ἆ' => 'a', 'ἇ' => 'a', 'ᾀ' => 'a', 'ᾁ' => 'a', 'ᾂ' => 'a', 'ᾃ' => 'a', 'ᾄ' => 'a', 'ᾅ' => 'a', 'ᾆ' => 'a', 'ᾇ' => 'a', 'ὰ' => 'a', 'ά' => 'a', 'ᾰ' => 'a', 'ᾱ' => 'a', 'ᾲ' => 'a', 'ᾳ' => 'a', 'ᾴ' => 'a', 'ᾶ' => 'a', 'ᾷ' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'έ' => 'e', 'ἐ' => 'e', 'ἑ' => 'e', 'ἒ' => 'e', 'ἓ' => 'e', 'ἔ' => 'e', 'ἕ' => 'e', 'ὲ' => 'e', 'έ' => 'e', 'ζ' => 'z', 'η' => 'i', 'ή' => 'i', 'ἠ' => 'i', 'ἡ' => 'i', 'ἢ' => 'i', 'ἣ' => 'i', 'ἤ' => 'i', 'ἥ' => 'i', 'ἦ' => 'i', 'ἧ' => 'i', 'ᾐ' => 'i', 'ᾑ' => 'i', 'ᾒ' => 'i', 'ᾓ' => 'i', 'ᾔ' => 'i', 'ᾕ' => 'i', 'ᾖ' => 'i', 'ᾗ' => 'i', 'ὴ' => 'i', 'ή' => 'i', 'ῂ' => 'i', 'ῃ' => 'i', 'ῄ' => 'i', 'ῆ' => 'i', 'ῇ' => 'i', 'θ' => 'th', 'ι' => 'i', 'ί' => 'i', 'ϊ' => 'i', 'ΐ' => 'i', 'ἰ' => 'i', 'ἱ' => 'i', 'ἲ' => 'i', 'ἳ' => 'i', 'ἴ' => 'i', 'ἵ' => 'i', 'ἶ' => 'i', 'ἷ' => 'i', 'ὶ' => 'i', 'ί' => 'i', 'ῐ' => 'i', 'ῑ' => 'i', 'ῒ' => 'i', 'ΐ' => 'i', 'ῖ' => 'i', 'ῗ' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'ks', 'ο' => 'o', 'ό' => 'o', 'ὀ' => 'o', 'ὁ' => 'o', 'ὂ' => 'o', 'ὃ' => 'o', 'ὄ' => 'o', 'ὅ' => 'o', 'ὸ' => 'o', 'ό' => 'o', 'π' => 'p', 'ρ' => 'r', 'ῤ' => 'r', 'ῥ' => 'r', 'σ' => 's', 'ς' => 's', 'τ' => 't', 'υ' => 'y', 'ύ' => 'y', 'ϋ' => 'y', 'ΰ' => 'y', 'ὐ' => 'y', 'ὑ' => 'y', 'ὒ' => 'y', 'ὓ' => 'y', 'ὔ' => 'y', 'ὕ' => 'y', 'ὖ' => 'y', 'ὗ' => 'y', 'ὺ' => 'y', 'ύ' => 'y', 'ῠ' => 'y', 'ῡ' => 'y', 'ῢ' => 'y', 'ΰ' => 'y', 'ῦ' => 'y', 'ῧ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'o', 'ώ' => 'o', 'ὠ' => 'o', 'ὡ' => 'o', 'ὢ' => 'o', 'ὣ' => 'o', 'ὤ' => 'o', 'ὥ' => 'o', 'ὦ' => 'o', 'ὧ' => 'o', 'ᾠ' => 'o', 'ᾡ' => 'o', 'ᾢ' => 'o', 'ᾣ' => 'o', 'ᾤ' => 'o', 'ᾥ' => 'o', 'ᾦ' => 'o', 'ᾧ' => 'o', 'ὼ' => 'o', 'ώ' => 'o', 'ῲ' => 'o', 'ῳ' => 'o', 'ῴ' => 'o', 'ῶ' => 'o', 'ῷ' => 'o', '¨' => '', '΅' => '', '᾿' => '', '῾' => '', '῍' => '', '῝' => '', '῎' => '', '῞' => '', '῏' => '', '῟' => '', '῀' => '', '῁' => '', '΄' => '', '΅' => '', '`' => '', '῭' => '', 'ͺ' => '', '᾽' => '', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I', 'Й' => 'I', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'KH', 'Ц' => 'TS', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SHCH', 'Ы' => 'Y', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA', 'Ъ' => '', 'Ь' => '', 'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ы' => 'y', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'ъ' => '', 'ь' => '', 'ð' => 'd', 'Ð' => 'D', 'þ' => 'th', 'Þ' => 'TH', 'ა' => 'a', 'ბ' => 'b', 'გ' => 'g', 'დ' => 'd', 'ე' => 'e', 'ვ' => 'v', 'ზ' => 'z', 'თ' => 't', 'ი' => 'i', 'კ' => 'k', 'ლ' => 'l', 'მ' => 'm', 'ნ' => 'n', 'ო' => 'o', 'პ' => 'p', 'ჟ' => 'zh', 'რ' => 'r', 'ს' => 's', 'ტ' => 't', 'უ' => 'u', 'ფ' => 'p', 'ქ' => 'k', 'ღ' => 'gh', 'ყ' => 'q', 'შ' => 'sh', 'ჩ' => 'ch', 'ც' => 'ts', 'ძ' => 'dz', 'წ' => 'ts', 'ჭ' => 'ch', 'ხ' => 'kh', 'ჯ' => 'j', 'ჰ' => 'h', '&' => 'and', '`' => '', "'" => '');

        //$text = html_entity_decode($text);
        $text = strip_tags($text);
        $text = preg_replace('/\&\w+\;/', '', $text);
        $text = strtr($text, $map);
        $text = preg_replace('/(\W|_|-)/', ' ', $text);
        $text = self::clean($text, ' ');

        if ($that == '^')
            $text = str_replace(' ', '', ucwords($text));
        else
            $text = str_replace(' ', $that, $text);

        return $text;
    }

////////////////////////////////////////////////////////////////////////////////

    static function iconv($string, $from = ''){
        return iconv($from, 'utf-8', $string);
    }

////////////////////////////////////////////////////////////////////////////////

    // http://nudnik.ru/entry/1125
    static function truncate($string, $len = 150){
        preg_match('/.{1,'.$len.'}[^.!;?]*[.!;?]/si', trim(strip_tags($string)).'. ', $match);
        return $match[0];
    }

////////////////////////////////////////////////////////////////////////////////

    // Откуда спиздил?
    static function gzip($output, $compress = 3){
        if (function_exists('ob_gzhandler'))
            return array('output' => ob_gzhandler($output, $compress));

        if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false)
            $encoding = 'x-gzip';
        elseif (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)
            $encoding = 'gzip';
        else
            return compact('output');

        $size = strlen($output);
        $crc  = crc32($output);
        $output  = gzcompress($output, $compress);
        $output  = substr($output, 0, (strlen($output) - 4));
        $output  = "\x1f\x8b\x08\x00\x00\x00\x00\x00".$output;
        $output .= pack('V', $crc);
        $output .= pack('V', $size);

        return compact('output', 'encoding');
    }

////////////////////////////////////////////////////////////////////////////////

    // http://spectator.ru/technology/php/simple_XML
    static function untag($tag, $string, $open = '<', $close = '>'){
        while (true){
            //начало тэга
            if (($start = stripos($string, $open.$tag, $stop)) === false)
                break;

            //начало контента
            if (($start = stripos($string, $close, $start)) === false)
                break;

            //конец контента
            if (($stop = stripos($string, $open.'/'.$tag.$close, ++$start)) === false)
                break;

            //выкусить контент!
            $result[] = substr($string, $start, $stop - $start);
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    static function json($json, $assoc = true){
        if (!is_null($result = json_decode($json, $assoc)))
            return $result;

        $json = str_replace('"', '¬', $json);
        $json = str_replace("\'", '¬*', $json);
        $json = str_replace("'", '"', $json);
        $json = str_replace('¬*', "'", $json);
        $json = str_replace('¬', '\"', $json);

        return json_decode($json, $assoc);
    }

////////////////////////////////////////////////////////////////////////////////
}