<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class int {
////////////////////////////////////////////////////////////////////////////////
    // http://cutephp.com
    function size($file_size, $size = array()){        $size = array_merge(str::text('lib.int.size'), $size);

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

    function plural($int, $array, $noint = false){        list($banan, $banana, $bananov) = (!is_array($array) ? explode('/', $array) : $array);

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
    function roman($num){
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

    function bytes($value){
        if (is_numeric($value))
            return $value;

        $value = strtolower($value);
        $map = array(
            'k' => 1024,
            'm' => (1024 * 1024),
            'g' => (1024 * 1024 * 1024)
        );

        return ($map[substr($value, -1)] * trim(substr($value, 0, -1)));
    }

////////////////////////////////////////////////////////////////////////////////
}