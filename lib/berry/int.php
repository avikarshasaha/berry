<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Int {
////////////////////////////////////////////////////////////////////////////////
    // http://cutephp.com
    static function size($file_size, $size = array()){        $size = array_merge(b::i18n('lib.int.size'), $size);

        if ($file_size >= 1073741824)
            $file_size = (round($file_size / 1073741824 * 100) / 100).' '.$size['gb'];
        elseif ($file_size >= 1048576)
            $file_size = (round($file_size / 1048576 * 100) / 100).' '.$size['mb'];
        elseif ($file_size >= 1024)
            $file_size = (round($file_size / 1024 * 100) / 100).' '.$size['kb'];
        else
            $file_size = $file_size.' '.$size['b'];

        return $file_size;
    }

////////////////////////////////////////////////////////////////////////////////

    static function plural($int, $array, $noint = false){        list($banan, $banana, $bananov) = (!is_array($array) ? explode('/', $array) : $array);

        $n1 = substr($int, -1);
        $n2 = substr($int, -2);
        $string = $bananov;

        if ($n2 >= 10 and $n2 <= 20)
            $string = $bananov;
        elseif ($n1 == 1)
            $string = $banan;
        elseif ($n1 >= 2 and $n1 <= 4)
            $string = $banana;

        return trim($noint ? $string : $int.' '.$string);
    }

////////////////////////////////////////////////////////////////////////////////

    // http://php.ru/forum/viewtopic.php?t=13399
    static function roman($num){
        $n = intval($num);

        $romans = array(
            'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
            'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
            'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
        );

        foreach ($romans as $roman => $value){
            $matches = intval($n / $value);
            $result .= str_repeat($roman, $matches);
            $n = ($n % $value);
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    static function bytes($value){
        if (is_numeric($value))
            return $value;

        $value = strtolower($value);
        $map = array('k' => 1, 'm' => 2, 'g' => 3);

        return (pow(1024, $map[substr($value, -1)]) * substr($value, 0, -1));
    }

////////////////////////////////////////////////////////////////////////////////

    static function roubleRate($currency, $date = ''){
        $date = date('d.m.Y', date::time($date));

        if (!$cache = cache::get('int/roublerate/'.$date.'.php')){
            $xml = simplexml_load_file('http://cbr.ru/scripts/XML_daily.asp?date_req='.$date);
            $array = array();

            foreach ($xml->xpath('//Valute') as $key => $xml){
                $id = (string)$xml->attributes()->ID;
                $name = (string)$xml->Name;
                $code = (int)$xml->NumCode;
                $value = (str_replace(',', '.', $xml->Value) / $xml->Nominal);
                $value = str_replace(',', '.', $value);

                $array[(string)$xml->CharCode] = compact('id', 'name', 'code', 'value');
            }

            cache::set($array);
        } else {
            $array = include $cache;
        }

        return $array[strtoupper($currency)]['value'];
    }

////////////////////////////////////////////////////////////////////////////////

    static function phone($number, $format = '[1] [(3)] 3-2-2'){        $plus = ($number[0] == '+');        $number = preg_replace('/\D/', '', $number);

        $len = array_sum(preg_split('/\D/', $format));
        $params = arr::merge(array_fill(0, $len, 0), array_reverse(str_split($number)));

        $format = strrev(preg_replace('/(\d)/e', "str_repeat('d%', '\\1')", $format));
        $format = call_user_func_array('sprintf', array_merge(array($format), $params));
        $format = ($plus ? '+' : '').strrev($format);

        if (preg_match_all('/\[(.*?)\]/', $format, $match))
            for ($i = 0, $c = b::len($match[0]); $i < $c; $i++)
                if (!(int)preg_replace('/\D/', '', $match[1][$i]))
                    $format = str_replace($match[0][$i], '', $format);

        return strtr(trim($format), array('[' => '', ']' => ''));
    }

////////////////////////////////////////////////////////////////////////////////
}