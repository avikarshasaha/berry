<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Check {
    static $error = array();

////////////////////////////////////////////////////////////////////////////////

    function is_valid($name, $re, $data = '_post'){
        $data = (!is_array($data) ? b::l($data) : $data);
        $array = arr::flat($data);
        $name = tags::elmname_parse($name);
        $value = $array[$name];
        $check = array();

        if (
            !array_key_exists($name, $data) and
            !array_key_exists($name, $array) and
            !array_key_exists(preg_replace('/^([^\.]*)\./', '\\1.name.', $name), $array)
        )
            return true;

        if (preg_match('/\W?\/(.*?)\/(i|m|s|x|e|u|A|D|S|U|X|J)?(\s|$)/', $re, $match)){
            $check['regexp'] = preg_match($match[0], $value);
            $re = str_replace($match[0], '', $re);
        }

        if (preg_match_all('/(or\s+empty|!?\w+)(\((.*?)\))?(\s+)?/is', $re, $match))
            for ($i = 0, $c = b::len($match[0]); $i < $c; $i++){
                $func = strtolower($match[1][$i]);
                $params = array();

                if ($func[0] == '!'){
                    $func = substr($func, 1);
                    $not[$func] = true;
                }

                if ($match[3][$i])
                    $params = array_map(create_function('$item', '
                        if (
                            ($item[0] == "\'" and substr($item, -1) == "\'") or
                            ($item[0] == \'"\' and substr($item, -1) == \'"\')
                        )
                            return substr($item, 1, -1);

                        return $item;
                    '), arr::trim(explode(',', $match[3][$i])));

                $args = array($name, $value, $params, $array, $data);

                if (function_exists($call = 'check_'.$func)){
                    $check[$func] = call_user_func_array($call, $args);
                    continue;
                }

                if (method_exists('check', $func) and substr($func, 0, 3) != 'is_'){
                    $check[$func] = call_user_func_array(array('check', $func), $args);
                    continue;
                }

                if ($func == 'or empty')
                    $check = array($func => !$value);
            }

        foreach ($check as $k => $v)
            $check[$k] = ($not[$k] ? !$check[$k] : (bool)$check[$k]);

        if (!$key = array_search(false, $check))
            return true;

        self::$error[$name] = $key;
        return false;
    }

////////////////////////////////////////////////////////////////////////////////

    function is_valid_session($array = null){        if (!is_array($array))
            $array = b::l('_session'.($array ? '.'.$array : ''));

        if ($array and $_SESSION['berry'])
            return $array;

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    function is_valid_get($array = null){
        if (!is_array($array))
            $array = b::l('_get'.($array ? '.'.$array : ''));

        if (
            $array and
            //self::is_valid_session() and
            (!self::$error or !array_intersect_key(self::$error, arr::flat($_GET)))
        )
            return $array;

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    function is_valid_post($array = null){        if (!is_array($array))
            $array = b::l('_post'.($array ? '.'.$array : ''));

        if (
            $array and
            //self::is_valid_session() and
            (!self::$error or !array_intersect_key(self::$error, arr::flat($_POST)))
        )
            return $array;

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    function is_valid_files($array = null){        if (!is_array($array))
            $array = b::l('_files'.($array ? '.'.$array : ''));

        if ($array /*and self::is_valid_session()*/){            foreach (array_keys(arr::flat(arr::files($_FILES))) as $k)
                $files[substr($k, 0, strrpos($k, '.'))] = $k;

            if (!self::$error or !array_intersect_key(self::$error, $files))
                return $array;
        }

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    function int($name, $value, $params){
        return (filter_var($value, FILTER_VALIDATE_INT) !== false and (!$params or ($value >= $params[0] and $value <= $params[1])));
    }

////////////////////////////////////////////////////////////////////////////////

    function float($name, $value, $params){
        return (filter_var($value, FILTER_VALIDATE_FLOAT) !== false and (!$params or ($value >= $params[0] and $value <= $params[1])));
    }

////////////////////////////////////////////////////////////////////////////////

    function number($name, $value, $params){
        return preg_match('/^[+-]\d+([\s.,]\d+)?([\s.,]\d+)?$/', $value);
    }

////////////////////////////////////////////////////////////////////////////////

    function datetime($name, $value, $params){
        $time = strtotime($value);
        return (preg_match('/^[12]\d{3}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value) and (!$params or ($time >= strtotime($params[0]) and $time <= strtotime($params[1]))));
    }

////////////////////////////////////////////////////////////////////////////////

    function date($name, $value, $params){
        $time = strtotime($value);
        return (preg_match('/^[12]\d{3}-\d{2}-\d{2}$/', $value) and (!$params or ($time >= strtotime($params[0]) and $time <= strtotime($params[1]))));
    }

////////////////////////////////////////////////////////////////////////////////

    function time($name, $value, $params){
        $time = strtotime($value);
        return (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value) and (!$params or ($time >= strtotime($params[0]) and $time <= strtotime($params[1]))));
    }

////////////////////////////////////////////////////////////////////////////////

    function string($name, $value, $params){
        return ($value !== '');
    }

////////////////////////////////////////////////////////////////////////////////

    function mail($name, $value, $params){
        return (filter_var($value, FILTER_VALIDATE_EMAIL) !== false);
    }

////////////////////////////////////////////////////////////////////////////////

    function url($name, $value, $params){
        $check = (filter_var($value, FILTER_VALIDATE_URL) !== false);

        if ($params)
            $check = ($check and preg_match('/^('.join('|', $params).')\:\/\//i', $value));

        return $check;
    }

////////////////////////////////////////////////////////////////////////////////

    function aid($name, $value, $params){
        return (preg_match('/^[^_\-][a-z0-9_\-]+[^_\-]$/', $value) and !is_numeric($value));
    }

////////////////////////////////////////////////////////////////////////////////

    function ip($name, $value, $params){
        return (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false);
    }

////////////////////////////////////////////////////////////////////////////////

    function checker($name, $value, $params){
        return self::call($params[0], $value);
    }

////////////////////////////////////////////////////////////////////////////////

    function compare($name, $value, $params){
        return version_compare($value, $params[1], $params[0]);
    }

////////////////////////////////////////////////////////////////////////////////

    function unique($name, $value, $params){        $tmp = explode('.', $params[0]);
        list($table, $field) = (b::len($tmp) == 3 ? array($tmp[0].'.'.$tmp[1], $tmp[2]) : $tmp);

        return !sql::query(
            'select 1 from ?_ where lower(?#) = ? { and ?} limit 1',
            $table, $field, strtolower($value), ($params[1] ? sql::raw($params[1]) : sql::SKIP)
        );
    }

////////////////////////////////////////////////////////////////////////////////

    function in($name, $value, $params, $array){
        return in_array($name, $value, $params);
    }

////////////////////////////////////////////////////////////////////////////////

    function mime($name, $value, $params, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.type\\2', $name);
        return preg_match('{('.str_replace('*', '(.*)', join('|', $params)).')}i', $array[$tmp]);
    }

////////////////////////////////////////////////////////////////////////////////

    function ext($name, $value, $params, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.name\\2', $name);
        $ext = strtolower(pathinfo($array[$tmp], PATHINFO_EXTENSION));

        return in_array($ext, array_map('strtolower', $params));
    }

////////////////////////////////////////////////////////////////////////////////

    function size($name, $value, $params, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.size\\2', $name);
        $tmp = $array[$tmp];

        if (b::len($params) == 2)
            return ($tmp >= int::bytes($params[0]) and $tmp <= int::bytes($params[1]));
        elseif ($params)
            return ($tmp <= int::bytes($params[0]));
    }

////////////////////////////////////////////////////////////////////////////////

    function width($name, $value, $params, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.tmp_name\\2', $name);
        $tmp = getimagesize($array[$tmp]);
        $tmp = $tmp[0];

        if (b::len($params) == 2)
            return ($tmp >= $params[0] and $tmp <= $params[1]);
        elseif ($params)
            return ($tmp <= $params[0]);
    }

////////////////////////////////////////////////////////////////////////////////

    function height($name, $value, $params, $array){
        $tmp = preg_replace('/^([^\.]*)(\.)?/', '\\1.tmp_name\\2', $name);
        $tmp = getimagesize($array[$tmp]);
        $tmp = $tmp[1];

        if (b::len($params) == 2)
            return ($tmp >= $params[0] and $tmp <= $params[1]);
        elseif ($params)
            return ($tmp <= $params[0]);
    }

////////////////////////////////////////////////////////////////////////////////

    function phone($name, $value, $params){
        return in_array(b::len(preg_replace('/\D+/', '', $value)), ($params ? $params : array(7, 10, 11)));
    }

////////////////////////////////////////////////////////////////////////////////

}