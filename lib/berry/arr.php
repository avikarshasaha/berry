<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Arr {
////////////////////////////////////////////////////////////////////////////////

    function files($files){
        foreach ($files as $k1 => $v1)
            foreach ($v1 as $k2 => $v2){
                if (is_array($v2))
                    foreach ($v2 as $k3 => $v3){
                        $files[$k1][$k3][$k2] = $v3;
                        unset($files[$k1][$k2][$k3]);
                    }

                if ($files[$k1][$k2] === array())
                    unset($files[$k1][$k2]);
            }

        return $files;
    }

////////////////////////////////////////////////////////////////////////////////

    function html($item){
        if (is_array($item))
            return array_map(array('self', 'html'), $item);
        else
            return str::html($item);
    }

////////////////////////////////////////////////////////////////////////////////

    function unhtml($item){
        if (is_array($item))
            return array_map(array('self', 'unhtml'), $item);
        else
            return str::unhtml($item);
    }

////////////////////////////////////////////////////////////////////////////////

    function trim($item){
        if (is_array($item))
            return array_map(array('self', 'trim'), $item);
        else
            return trim($item);
    }

////////////////////////////////////////////////////////////////////////////////

    function export($filename, $array, $name = ''){
        $contents  = "<?\r\n";
        $contents .= ($name ? '$'.$name.' =' : 'return').' ';
        $contents .= var_export($array, true);
        $contents .= ";\r\n";
        $contents .= "\r\n?>";

        return file_put_contents($filename, $contents);
    }

////////////////////////////////////////////////////////////////////////////////

    function assoc($array){
        $array = self::flat($array);
        $keys  = array_keys($array);

        foreach ($keys as $k){
            $k2 = str_replace('\\.', tags::char('.'), $k);
            $k2 = str_replace('\.', '.', $k2);
            $k2 = str_replace(tags::char('.'), '.', $k2);
            $k  = str_replace('\\.', '\\\.', $k);

            $result .= tags::varname($k2, '$_').' = $array["'.$k.'"];';
        }

        if ($result and ($func = create_function('$array', $result.'return $_;')))
            return $func($array);

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    function flat($array, $subk = '', $result = array()){
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

    function tree($array, $level = 0, $result = array()){
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

    function json($value){
        $json = json_encode($value);
        $json = str_replace('\"', '¬', $json);
        $json = str_replace("'", '¬*', $json);
        $json = str_replace('"', "'", $json);
        $json = str_replace('¬*', "\'", $json);
        $json = str_replace('¬', '"', $json);

        return $json;
    }

////////////////////////////////////////////////////////////////////////////////

    function date($array, $timestamp = false){
        if ($array['year'])
            $date[] = $array['year'];
        if ($array['month'])
            $date[] = $array['month'];
        if ($array['day'])
            $date[] = $array['day'];

        if ($array['hour'])
            $time[] = $array['hour'];
        if ($array['minute'])
            $time[] = $array['minute'];
        if ($array['second'])
            $time[] = $array['second'];

        if ($date)
            $date = join('-', $date);
        if ($time)
            $time = join(':', $time);

        $result = ($date ? $date : '').(($date and $time) ? ' ' : '').($time ? $time : '');
        return ($timestamp ? date::time($result) : $result);
    }

////////////////////////////////////////////////////////////////////////////////

    function merge(){        $args = func_get_args();        $result = array();

        foreach (array_reverse($args) as $array)
            $result += self::flat($array);

        return self::assoc($result);    }

////////////////////////////////////////////////////////////////////////////////

    function filter($array, $allow){        return array_intersect_key($attr, array_flip($allow));
    }

////////////////////////////////////////////////////////////////////////////////
}