<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Check {
    static $errors = array();

////////////////////////////////////////////////////////////////////////////////

    static function is_valid($re, $data = array()){
        $data = ($data ? $data : $_POST);
        $error = false;

        foreach ($re as $k => $v){
            $v = (array)$v;

            if (!self::_validate($k, $v[0], $data))
                $error = true;
        }

        return !$error;
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _validate($name, $re, $data){
        $array = arr::flat($data);
        $name = piles::name2var($name);
        $value = $array[$name];
        $check = array();

        if (
            !array_key_exists($name, $data) and
            !array_key_exists($name, $array) and
            !array_key_exists(preg_replace('/^([^\.]*)\./', '\\1.name.', $name), $array)
        )
            $array[$name] = '';

        if (preg_match('/\W?\/(.*)\/([imsxeuADSUXJ]+)?(\s|$)/', $re, $match)){
            $check['regexp'] = preg_match($match[0], $value);
            $re = str_replace($match[0], '', $re);
        }

        if (preg_match_all('/(!?\w+)(\((.*?)\))?(\s+)?/is', $re, $match))
            for ($i = 0, $c = count($match[0]); $i < $c; $i++){
                $func = strtolower($match[1][$i]);
                $args = array($value, self::_params($match[3][$i]), $name, $array, $data);

                if ($func == 'or'){
                    if (array_search(false, $check))
                        $check = array();
                    else
                        break;
                } elseif ($func[0] == '!'){
                    $func = substr($func, 1);
                    $not[$func] = true;
                }

                if (b::function_exists($call = 'check_'.$func)){
                    $check[$func] = b::call('*'.$call, $args);
                    continue;
                }

                foreach (array('', 'is_') as $prefix)
                    if (method_exists('check', $prefix.$func))
                        $check[$func] = call_user_func_array(array('check', $prefix.$func), $args);
            }

        foreach ($check as $k => $v)
            $check[$k] = ($not[$k] ? !$check[$k] : (bool)$check[$k]);

        if (!$key = array_search(false, $check))
            return true;

        self::$errors[$name] = $key;
        return false;
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _params($params = ''){
        if (!$params)
            return array();

        $params = str_replace('\"', '"', preg_replace('/(\'|")(.*?)\\1/es', "'\\1'.str_replace(',', '¬', '\\2').'\\1'", $params));
        $params = explode(',', $params);

        foreach ($params as &$v){
            $v = trim(str_replace('¬', ',', $v));

            if (($v[0] == "'" and substr($v, -1) == "'") or ($v[0] == '"' and substr($v, -1) == '"'))
                $v = substr($v, 1, -1);
        }

        return $params;
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_int($value, $params = array()){
        return (filter_var($value, FILTER_VALIDATE_INT) !== false and (!$params or ($value >= $params[0] and $value <= $params[1])));
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_float($value, $params = array()){
        return (filter_var($value, FILTER_VALIDATE_FLOAT) !== false and (!$params or ($value >= $params[0] and $value <= $params[1])));
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_number($value){
        return preg_match('/^[+-]\d+([\s.,]\d+)?([\s.,]\d+)?$/', $value);
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_numeric($value){
        return self::is_number($value);
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_datetime($value, $params = array()){
        $time = strtotime($value);
        return (
            preg_match('/^(\d{4})-(\d{2})-(\d{2}) \d{2}:\d{2}:\d{2}$/', $value, $m) and
            $time and checkdate($m[2], $m[3], $m[1]) and
            (!$params or ($time >= strtotime($params[0]) and $time <= strtotime($params[1])))
        );
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_date($value, $params = array()){
        $time = strtotime($value);
        return (
            preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) and
            $time and checkdate($m[2], $m[3], $m[1]) and
            (!$params or ($time >= strtotime($params[0]) and $time <= strtotime($params[1])))
        );
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_time($value, $params = array()){
        $time = strtotime($value);
        return (
            preg_match('/^\d{2}:\d{2}:\d{2}$/', $value) and $time and
            (!$params or ($time >= strtotime($params[0]) and $time <= strtotime($params[1])))
        );
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_string($value, $params = array()){
        $len = strlen($value);

        if ($params)
            return ($len >= $params[0] and $len <= $params[1]);

        return $len;
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_mail($value){
        return (filter_var($value, FILTER_VALIDATE_EMAIL) !== false);
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_url($value, $params = array()){
        return (filter_var($value, FILTER_VALIDATE_URL) !== false and (!$params or preg_match('/^('.join('|', $params).')\:\/\//i', $value)));
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_aid($value){
        $tmp = array_intersect(array($value[0], substr($value, -1)), array('-', '_'));
        return (!$tmp and preg_match('/^[a-z0-9_\-]+$/i', $value) and !is_numeric($value));
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_ip($value){
        return (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false);
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_unique($value, $params = array()){
        $tmp = explode('.', $params[0]);
        list($table, $field) = (count($tmp) == 3 ? array($tmp[0].'.'.$tmp[1], $tmp[2]) : $tmp);

        $class = sql::table($table);
        $class->where('lower(?f) = ?', $field, strtolower($value));

        if ($params[1])
            $class->where('?', sql::raw($params[1]));

        return !$class->limit(1)->exists();
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_phone($value, $params = array()){
        return in_array(strlen(preg_replace('/\D/', '', $value)), ($params ? $params : array(7, 10, 11)));
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_isbn($value){
        $number = strtolower(str_replace('-', '', $value));
        $len = strlen($number);
        $sum = 0;

        if ($len == 10 and preg_match('/\d+-\d+-\d+-(\d{1}|x)/', $value)){
            for ($i = 0; $i < ($len - 1); $i++)
                $sum += ($number[$i] * ($i + 1));

            return (($sum % 11) == ($number[$i] == 'x' ? 10 : $number[$i]));
        }

        if ($len == 13 and preg_match('/\d+-\d+-\d+-\d+-\d{1}/', $value)){
            for ($i = 0; $i < $len; $i += 2)
                $sum += ($number[$i] * 3 + $number[$i + 1]);

            return (($sum % 10) == 0);
        }
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_barcode($value){
        $number = strrev(str_replace(' ', '', $value));
        $sum = 0;

        if (!is_numeric($number))
            return;

        for ($i = 1, $c = count($number); $i < $c; $i += 2)
            $sum += ($number[$i] * 3 + $number[$i + 1]);

        $mod = ($sum % 10);
        return ($number[0] == ($mod ? (10 - $mod) : 0));
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function checker($value, $params){
        return (bool)self::call($params[0], $value);
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function is_empty($value, $params, $name, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.name\\2', $name);
        return (!$value and !$array[$tmp]);
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function compare($value){
        return version_compare($value, $params[1], $params[0]);
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function same_as($value, $params, $name, $array){
        return ($value == $array[$params[0]]);
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function in($value, $params){
        return in_array($value, $params);
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function mime($value, $params, $name, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.type\\2', $name);
        return preg_match('{('.str_replace('*', '(.*)', join('|', $params)).')}i', $array[$tmp]);
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function ext($value, $params, $name, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.name\\2', $name);
        $ext = strtolower(pathinfo($array[$tmp], PATHINFO_EXTENSION));

        return in_array($ext, array_map('strtolower', $params));
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function size($value, $params, $name, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.size\\2', $name);
        $tmp = $array[$tmp];

        if (count($params) == 2)
            return ($tmp >= int::bytes($params[0]) and $tmp <= int::bytes($params[1]));
        elseif ($params)
            return ($tmp <= int::bytes($params[0]));
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function width($value, $params, $name, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.tmp_name\\2', $name);
        $tmp = getimagesize($array[$tmp]);
        $tmp = $tmp[0];

        if (count($params) == 2)
            return ($tmp >= $params[0] and $tmp <= $params[1]);
        elseif ($params)
            return ($tmp <= $params[0]);
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function height($value, $params, $name, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.tmp_name\\2', $name);
        $tmp = getimagesize($array[$tmp]);
        $tmp = $tmp[1];

        if (count($params) == 2)
            return ($tmp >= $params[0] and $tmp <= $params[1]);
        elseif ($params)
            return ($tmp <= $params[0]);
    }

////////////////////////////////////////////////////////////////////////////////

}
