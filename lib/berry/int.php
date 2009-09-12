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

    static function size($filesize, $size = array()){        $size = array_merge(b::i18n('lib.int.size'), $size);
        $map = array('gb' => 3, 'mb' => 2, 'kb' => 1);

        foreach ($map as $k => $v)
            if ($filesize >= ($v = pow(1024, $v)))
                return (round($filesize / $v * 100) / 100).' '.$size[$k];

        return $filesize.' '.$size['b'];
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

    static function phone($number, $format = '[1] [(3)] 3-2-2'){        $plus = ($number[0] == '+');        $number = preg_replace('/\D/', '', $number);
        $len = array_sum(preg_split('/\D/', $format));
        $params = arr::merge(array_fill(0, $len, 0), array_reverse(str_split($number)));

        if ($plus)
            $params[$len - 1] .= '+';

        $format = strrev(preg_replace('/(\d)/e', "str_repeat('s%', '\\1')", $format));
        $format = b::call('*sprintf', array_merge(array($format), $params));
        $format = strrev($format);

        if (preg_match_all('/\[(.*?)\]/', $format, $match))
            for ($i = 0, $c = b::len($match[0]); $i < $c; $i++)
                if (!(int)preg_replace('/\D/', '', $match[1][$i]))
                    $format = str_replace($match[0][$i], '', $format);

        return trim(strtr($format, array('[' => '', ']' => '')));
    }

////////////////////////////////////////////////////////////////////////////////
}