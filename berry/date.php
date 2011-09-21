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

    static function now(){
        return date('Y-m-d H:i:s');
    }

////////////////////////////////////////////////////////////////////////////////

    static function format($format, $timestamp = 0, $simple = false){
        if (is_bool($timestamp))
            list($timestamp, $simple) = array(0, $timestamp);

        if ($simple)
            $date = date($format, self::time($timestamp));
        else
            $date = strftime($format, self::time($timestamp));

        return $date;
    }

////////////////////////////////////////////////////////////////////////////////

    static function time($timestamp = 0){
        if (!$timestamp)
            $timestamp = time();
        elseif (!is_numeric($timestamp) or strlen($timestamp) < 10)
            $timestamp = strtotime($timestamp);

        return $timestamp;
    }

////////////////////////////////////////////////////////////////////////////////

    // http://php.net/datetime/#78025
    static function ago($second, $len = 3, $plural = array()){
        if (strlen($second) == 10 or !is_numeric($second))
            $second = (time() - self::time($second));

        $plural = array_merge(b::lang('date.ago'), array('separator' => ','), $plural);
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

        if (count($array) > 1){
            if (count($array) == 2){
                $result = $array[0].($len != 1 ? ' '.$plural['and'].' '.$array[1] : '');
            } elseif (count($array) <= $len){
                $end = array_pop($array);
                $result = join($plural['separator'].' ', $array).' '.$plural['and'].' '.$end;
            } else {
                if ($len = count($result = array_slice($array, 0, $len - 1)))
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

    static function is_today($date){
        return (date('Ymd') == date('Ymd', self::time($date)));
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_yesterday($date){
        return (date('Ymd', strtotime('-1 day')) == date('Ymd', self::time($date)));
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_tomorrow($date){
        return (date('Ymd', strtotime('+1 day')) == date('Ymd', self::time($date)));
    }

////////////////////////////////////////////////////////////////////////////////

    static function sec($value){
        if (is_numeric($value))
            return $value;

        $value = strtolower($value);
        $map = array('m' => 60, 'h' => 3600, 'd' => 86400);

        return ($map[substr($value, -1)] * substr($value, 0, -1));
    }

////////////////////////////////////////////////////////////////////////////////

    static function calendar($year = '', $month = ''){
        $year = ($year ? $year : date('Y'));
        $result = $months = array();

        if (is_array($month))
            $months = array_fill($month[0], ($month[1] - 1), array());
        elseif ($month)
            $months[$month] = array();
        else
            $months = array_fill(1, 12, array());

        if (is_array($year)){
            for ($i = $year[0]; $i <= $year[1]; $i++)
                $result[$i] = $months;
        } else {
            $result[$year] = $months;
        }

        foreach ($result as $year => $months)
            foreach ($months as $month => $array)
                for ($i = 1, $c = date('t', strtotime($year.'-'.$month)); $i <= $c; $i++)
                    $result[$year][$month][$i] = $i;

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

}