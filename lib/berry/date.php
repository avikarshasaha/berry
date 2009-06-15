<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Date {
////////////////////////////////////////////////////////////////////////////////

    function now(){        return date('Y-m-d H:i:s');    }
////////////////////////////////////////////////////////////////////////////////

    function arr($date = 0){
        $date = self::time($date);

        $array['year']   = date('Y', $date);
        $array['month']  = date('m', $date);
        $array['day']    = date('d', $date);
        $array['hour']   = date('H', $date);
        $array['minute'] = date('m', $date);
        $array['second'] = date('s', $date);

        return $array;
    }

////////////////////////////////////////////////////////////////////////////////

    function format($format, $timestamp = 0){
        return strftime($format, self::time($timestamp));
    }

////////////////////////////////////////////////////////////////////////////////

    function time($timestamp = 0){
        if (!$timestamp)
            $timestamp = time();
        elseif (!is_numeric($timestamp))
            $timestamp = strtotime($timestamp);

        return $timestamp;
    }

////////////////////////////////////////////////////////////////////////////////

    // http://php.net/datetime/#78025
    function ago($second, $len = 3, $plural = array()){        if (b::len($second) == 10 or !is_numeric($second))
            $second = (time() - self::time($second));

        $plural = array_merge(b::i18n('lib.date.ago'), array('separator' => ','), $plural);
        $period = array(
            'years'   => 31556926,
            'months'  => 2629743,
            'weeks'   => 604800,
            'days'    => 86400,
            'hours'   => 3600,
            'minutes' => 60,
            'seconds' => 1
        );

        foreach ($period as $k => $v)
            if ($second >= $v){
                $durations = floor($second / $v);
                $second -= ($durations * $v);
                $array[] = int::plural($durations, $plural[$k]);
            }

        if (b::len($array) > 1){
            if (b::len($array) == 2){
                $result = $array[0].($len != 1 ? ' '.$plural['and'].' '.$array[1] : '');
            } elseif (b::len($array) <= $len){
                $end = array_pop($array);
                $result = join($plural['separator'].' ', $array).' '.$plural['and'].' '.$end;
            } else {
                if ($len = b::len($result = array_slice($array, 0, $len - 1)))
                    $result = join($plural['separator'].' ', $result).' '.$plural['and'].' '.$array[$len];
                else
                    $result = $array[$len];
            }
        } else {
            $result = $array[0];
        }

        return trim($result.' '.$plural['ago']);
    }

////////////////////////////////////////////////////////////////////////////////
}