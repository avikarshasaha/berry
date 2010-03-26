<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Check {
    static $errors = array();

////////////////////////////////////////////////////////////////////////////////

    static function is_valid($name, $re = array(), $data = '_post'){        if (is_array($name)){            foreach ($name as $k => $v){                $v = (array)$v;

                if (!self::is_valid($k, $v[0], $re))
                    $error = true;
            }

            return !$error;        }

        $data = (!is_array($data) ? b::l($data) : $data);
        $array = arr::flat($data);
        $name = piles::name2var($name);
        $value = $array[$name];
        $check = array();

        if (
            !array_key_exists($name, $data) and
            !array_key_exists($name, $array) and
            !array_key_exists(preg_replace('/^([^\.]*)\./', '\\1.name.', $name), $array)
        )
            return true;

        if (preg_match('/\W?\/(.*)\/([imsxeuADSUXJ]+)?(\s|$)/', $re, $match)){
            $check['regexp'] = preg_match($match[0], $value);
            $re = str_replace($match[0], '', $re);
        }

        if (preg_match_all('/(or\s+empty|!?\w+)(\((.*?)\))?(\s+)?/is', $re, $match))
            for ($i = 0, $c = b::len($match[0]); $i < $c; $i++){
                $func = strtolower($match[1][$i]);
                $args = array($value, self::_params($match[3][$i]), $name, $array, $data);

                if ($func[0] == '!'){
                    $func = substr($func, 1);
                    $not[$func] = true;
                }

                if (b::function_exists($call = 'check_'.$func)){
                    $check[$func] = b::call('*'.$call, $args);
                    continue;
                } elseif (method_exists('check', $func) and substr($func, 0, 3) != 'is_'){
                    $check[$func] = call_user_func_array(array('check', $func), $args);
                    continue;
                }

                if ($func == 'or empty'){                    $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.name\\2', $name);
                    $check = ((!$value and !$array[$tmp]) ? array($func => true) : $check);
                }
            }

        foreach ($check as $k => $v)
            $check[$k] = ($not[$k] ? !$check[$k] : (bool)$check[$k]);

        if (!$key = array_search(false, $check))
            return true;

        self::$errors[$name] = $key;
        return false;
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _params($params = ''){        if (!$params)
            return array();
        $params = str_replace('\"', '"', preg_replace('/(\'|")(.*?)\\1/es', "'\\1'.str_replace(',', '¬', '\\2').'\\1'", $params));
        $params = explode(',', $params);

        foreach ($params as &$v){
            $v = trim(str_replace('¬', ',', $v));

            if (($v[0] == "'" and substr($v, -1) == "'") or ($v[0] == '"' and substr($v, -1) == '"'))
                $v = substr($v, 1, -1);
        }

        return $params;    }

////////////////////////////////////////////////////////////////////////////////

    static function is_valid_session($array = null){        if (!is_array($array))
            $array = b::l('_session'.($array ? '.'.$array : ''));

        if ($array and $_SESSION['berry'])
            return $array;

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_valid_get($array = null){
        if (!is_array($array))
            $array = b::l('_get'.($array ? '.'.$array : ''));

        if (
            $array and
            //self::is_valid_session() and
            (!self::$errors or !array_intersect_key(self::$errors, arr::flat($array)))
        )
            return $array;

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_valid_post($array = null){        if (!is_array($array))
            $array = b::l('_post'.($array ? '.'.$array : ''));

        if (
            $array and
            //self::is_valid_session() and
            (!self::$errors or !array_intersect_key(self::$errors, arr::flat($array)))
        )
            return $array;

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_valid_files($array = null){        if (!is_array($array))
            $array = b::l('_files'.($array ? '.'.$array : ''));

        if ($array /*and self::is_valid_session()*/){            foreach (array_keys(arr::flat(arr::files($array))) as $k)
                $files[substr($k, 0, strrpos($k, '.'))] = $k;

            if (!self::$errors or !array_intersect_key(self::$errors, $files))
                return $array;
        }

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    static function int($value, $params = array()){
        return (filter_var($value, FILTER_VALIDATE_INT) !== false and (!$params or ($value >= $params[0] and $value <= $params[1])));
    }

////////////////////////////////////////////////////////////////////////////////

    static function float($value, $params = array()){
        return (filter_var($value, FILTER_VALIDATE_FLOAT) !== false and (!$params or ($value >= $params[0] and $value <= $params[1])));
    }

////////////////////////////////////////////////////////////////////////////////

    static function number($value){
        return preg_match('/^[+-]\d+([\s.,]\d+)?([\s.,]\d+)?$/', $value);
    }

////////////////////////////////////////////////////////////////////////////////

    static function datetime($value, $params = array()){
        $time = strtotime($value);
        return (
            preg_match('/^(\d{4})-(\d{2})-(\d{2}) \d{2}:\d{2}:\d{2}$/', $value, $m) and
            $time and checkdate($m[2], $m[3], $m[1]) and
            (!$params or ($time >= strtotime($params[0]) and $time <= strtotime($params[1])))
        );
    }

////////////////////////////////////////////////////////////////////////////////

    static function date($value, $params = array()){        $time = strtotime($value);
        return (
            preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) and
            $time and checkdate($m[2], $m[3], $m[1]) and
            (!$params or ($time >= strtotime($params[0]) and $time <= strtotime($params[1])))
        );
    }

////////////////////////////////////////////////////////////////////////////////

    static function time($value, $params = array()){
        $time = strtotime($value);
        return (
            preg_match('/^\d{2}:\d{2}:\d{2}$/', $value) and $time and
            (!$params or ($time >= strtotime($params[0]) and $time <= strtotime($params[1])))
        );
    }

////////////////////////////////////////////////////////////////////////////////

    static function string($value, $params = array()){        if ($params)
            return (b::len($value) >= $params[0] and b::len($value) <= $params[1]);

        return ($value !== '');
    }

////////////////////////////////////////////////////////////////////////////////

    static function mail($value){
        return (filter_var($value, FILTER_VALIDATE_EMAIL) !== false);
    }

////////////////////////////////////////////////////////////////////////////////

    static function url($value, $params = array()){
        return (filter_var($value, FILTER_VALIDATE_URL) !== false and (!$params or preg_match('/^('.join('|', $params).')\:\/\//i', $value)));
    }

////////////////////////////////////////////////////////////////////////////////

    static function aid($value){
        return (preg_match('/^[^_\-][a-z0-9_\-]+[^_\-]$/', $value) and !is_numeric($value));
    }

////////////////////////////////////////////////////////////////////////////////

    static function ip($value){
        return (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false);
    }

////////////////////////////////////////////////////////////////////////////////

    static function checker($value, $params){
        return (bool)self::call($params[0], $value);
    }

////////////////////////////////////////////////////////////////////////////////

    static function compare($value){
        return version_compare($value, $params[1], $params[0]);
    }

////////////////////////////////////////////////////////////////////////////////

    static function unique($value, $params = array()){        $tmp = explode('.', $params[0]);
        list($table, $field) = (b::len($tmp) == 3 ? array($tmp[0].'.'.$tmp[1], $tmp[2]) : $tmp);

        return !sql::query(
            'select 1 from ?_ where lower(?#) = ? { and ? } limit 1',
            $table, $field, strtolower($value), ($params[1] ? sql::raw($params[1]) : sql::SKIP)
        )->fetch();
    }

////////////////////////////////////////////////////////////////////////////////

    static function in($value, $params){
        return in_array($value, $params);
    }

////////////////////////////////////////////////////////////////////////////////

    static function mime($value, $params, $name, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.type\\2', $name);
        return preg_match('{('.str_replace('*', '(.*)', join('|', $params)).')}i', $array[$tmp]);
    }

////////////////////////////////////////////////////////////////////////////////

    static function ext($value, $params, $name, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.name\\2', $name);
        $ext = strtolower(pathinfo($array[$tmp], PATHINFO_EXTENSION));

        return in_array($ext, array_map('strtolower', $params));
    }

////////////////////////////////////////////////////////////////////////////////

    static function size($value, $params, $name, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.size\\2', $name);
        $tmp = $array[$tmp];

        if (b::len($params) == 2)
            return ($tmp >= int::bytes($params[0]) and $tmp <= int::bytes($params[1]));
        elseif ($params)
            return ($tmp <= int::bytes($params[0]));
    }

////////////////////////////////////////////////////////////////////////////////

    static function width($value, $params, $name, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.tmp_name\\2', $name);
        $tmp = getimagesize($array[$tmp]);
        $tmp = $tmp[0];

        if (b::len($params) == 2)
            return ($tmp >= $params[0] and $tmp <= $params[1]);
        elseif ($params)
            return ($tmp <= $params[0]);
    }

////////////////////////////////////////////////////////////////////////////////

    static function height($value, $params, $name, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.tmp_name\\2', $name);
        $tmp = getimagesize($array[$tmp]);
        $tmp = $tmp[1];

        if (b::len($params) == 2)
            return ($tmp >= $params[0] and $tmp <= $params[1]);
        elseif ($params)
            return ($tmp <= $params[0]);
    }

////////////////////////////////////////////////////////////////////////////////

    static function phone($value, $params = array()){
        return in_array(b::len(preg_replace('/\D/', '', $value)), ($params ? $params : array(7, 10, 11)));
    }

////////////////////////////////////////////////////////////////////////////////

    static function isbn($value){        $number = strtolower(str_replace('-', '', $value));
        $len = b::len($number);
        $sum = 0;

        if ($len == 10 and preg_match('/\d+-\d+-\d+-(\d{1}|x)/', $value)){            for ($i = 0; $i < ($len - 1); $i++)
                $sum += ($number[$i] * ($i + 1));

            return (($sum % 11) == ($number[$i] == 'x' ? 10 : $number[$i]));        }

        if ($len == 13 and preg_match('/\d+-\d+-\d+-\d+-\d{1}/', $value)){
            for ($i = 0; $i < $len; $i += 2)
                $sum += ($number[$i] * 3 + $number[$i + 1]);

            return (($sum % 10) == 0);
        }
    }

////////////////////////////////////////////////////////////////////////////////

    function barcode($value){
        $number = strrev(str_replace(' ', '', $value));
        $sum = 0;

        if (!is_numeric($number))
            return;

        for ($i = 1, $c = b::len($number); $i < $c; $i += 2)
            $sum += ($number[$i] * 3 + $number[$i + 1]);

        $mod = ($sum % 10);
        return ($number[0] == ($mod ? (10 - $mod) : 0));
    }

////////////////////////////////////////////////////////////////////////////////

    static function same_as($value, $params, $name, $array){
        return ($value == $array[$params[0]]);    }

////////////////////////////////////////////////////////////////////////////////

}