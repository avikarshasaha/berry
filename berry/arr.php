<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Arr {

////////////////////////////////////////////////////////////////////////////////

    static function html($item){
        if (is_array($item))
            return array_map(array('self', 'html'), $item);
        else
            return str::html($item);
    }

////////////////////////////////////////////////////////////////////////////////

    static function unhtml($item){
        if (is_array($item))
            return array_map(array('self', 'unhtml'), $item);
        else
            return str::unhtml($item);
    }

////////////////////////////////////////////////////////////////////////////////

    static function trim($item){
        if (is_array($item))
            return array_map(array('self', 'trim'), $item);
        else
            return trim($item);
    }

////////////////////////////////////////////////////////////////////////////////

    static function export($filename, $array, $name = ''){
        $contents .= "<?php\r\n";
        $contents .= ($name ? '$'.$name.' =' : 'return').' ';
        $contents .= var_export($array, true);
        $contents .= ";\r\n";

        return file_put_contents($filename, $contents);
    }

////////////////////////////////////////////////////////////////////////////////

    static function assoc($array){
        $array = self::flat($array);
        $keys  = array_keys($array);

        foreach ($keys as $k){
            $k2 = str_replace('\\.', piles::char('.'), $k);
            $k2 = str_replace('\.', '.', $k2);
            $k2 = str_replace(piles::char('.'), '.', $k2);
            $k  = str_replace('\\.', '\\\.', $k);

            $result .= piles::varname($k2, '$_').' = $array["'.$k.'"];';
        }

        if ($result and ($func = create_function('$array', $result.'return $_;')))
            return $func($array);

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    static function flat($array, $subk = '', $result = array()){
        foreach ($array as $k => $v){
            $k = str_replace('.', '\.', $k);

            if (is_array($v)){
                $result = self::flat($v, ($subk !== '' ? $subk.'.' : '').$k, $result);
            } else {
                $k = ($subk !== '' ? $subk.'.' : '').$k;
                $result[$k] = $v;
            }
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    static function tree($array, $level = 0, $result = array()){
        foreach ($array as $k => $items){
            $tmp = $items;
            unset($tmp['childNodes']);
            $result[$k] = array_merge($tmp, array((($pos = strpos($key = key($tmp), '.')) ? substr($key, 0, $pos + 1) : '').'#level' => $level));

            if (array_key_exists('childNodes', $items) and $items['childNodes']){
                $level++;
                $result = self::tree($items['childNodes'], $level, $result);
                $level--;
            }
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    static function json($value){
        $json = json_encode($value);
        $json = str_replace('\"', '¬', $json);
        $json = str_replace("'", '¬*', $json);
        $json = str_replace('"', "'", $json);
        $json = str_replace('¬*', "\'", $json);
        $json = str_replace('¬', '"', $json);

        return $json;
    }

////////////////////////////////////////////////////////////////////////////////

    static function merge(){

        foreach (array_reverse($args) as $array)
            $result += self::flat($array);

        return self::assoc($result);

////////////////////////////////////////////////////////////////////////////////

    static function filter($array, $allow){
    }

////////////////////////////////////////////////////////////////////////////////

    static function rand($array, $num = 1){
        if ($num == 1)
            return $array[array_rand($array)];

        $keys = array_rand($array, min($num, b::len($array)));
        $result = array();

        for ($i = 0, $c = b::len($keys); $i < $c; $i++){
            $result[$key] = $array[$key];
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    static function object($array, $class = 'stdClass'){
		$object = new $class;

		foreach ($array as $k => $v)

		return $object;
    }

////////////////////////////////////////////////////////////////////////////////
