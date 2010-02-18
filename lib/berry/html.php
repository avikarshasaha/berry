<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class HTML {
////////////////////////////////////////////////////////////////////////////////

    static function block($key, $value = null, $sort = 50){
        static $block, $sort;

        if ($value !== null and !in_array($value, (array)$block[$key])){
            $sort[$key][] = $sort;
            return $block[$key][] = $value;
        } elseif ($value === null and $block[$key]){
            array_multisort($sort[$key], SORT_ASC, $block[$key]);
            return $block[$key];
        }

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    static function msg($key, $value = null, $sort = 50){        $msg = &$_SESSION['lib']['html']['msg'];

        if ($value !== null and !in_array($value, (array)$msg['block'][$key])){
            $msg['#'.$key][] = $sort;
            return $msg[$key][] = $value;
        } elseif ($value === null and $msg[$key]){            $array = $msg[$key];

            array_multisort($msg['#'.$key], SORT_ASC, $array);
            unset($msg[$key], $msg['#'.$key]);

            return $array;
        }

        return array();
    }////////////////////////////////////////////////////////////////////////////////

    static function dropdown($name, $array, $selected = array()){
        if (!is_array($name))
            $attr['name'] = $name;
        else
            $attr = $name;

        if ($attr['multiple']){
            $attr['name'] .= '[]';
            $attr['multiple'] = 'multiple';
        }

        foreach ($array as $k => $v)
            if (is_array($v)){
                $attr['#text'] .= piles::fill('optgroup', array('label' => $k));

                foreach ($v as $a => $b){                    $array = array('value' => $a, '#text' => ($b !== '' ? $b : $a));

                    if (in_array($a, $selected))
                        $array['selected'] = 'selected';

                    $attr['#text'] .= piles::fill('option', $array);
                }
            } else {                $array = array('value' => $k, '#text' => ($v !== '' ? $v : $k));

                if (in_array($k, $selected))
                    $array['selected'] = 'selected';

                $attr['#text'] .= piles::fill('option', $array);
            }

        return piles::fill('select', $attr);
    }

////////////////////////////////////////////////////////////////////////////////

    static function js($text){
        $attr = array('type' => 'text/javascript', '#text' => '');

        if (strtolower(substr($text, -3)) != '.js' or strpos($text, "\n"))
            $attr['#text'] = $text;
        else
            $attr['src'] = $text;

        return piles::fill('script', $attr);
    }

////////////////////////////////////////////////////////////////////////////////

    static function css($text, $media = ''){
        $attr = array('type' => 'text/css');

        if ($media)
            $attr['media'] = $media;

        if (strtolower(substr($text, -4)) != '.css' or strpos($text, "\n")){
            $attr['#text'] = $text;
            return tags::fill('style', $attr);
        }

        $attr['href'] = $text;
        $attr['rel'] = 'stylesheet';

        return piles::fill('link', $attr);
    }

////////////////////////////////////////////////////////////////////////////////

    static function highlight($search, $output, $case = false, $class = 'highlight'){
        $pattern = array('/', '('.join('|', (array)$search).')', '/u', (!$case ? 'i' : ''));
        $replace = '<'.base64_encode('span class="'.$class.'"').'>\\1<'.base64_encode('/span').'>';

        $output = preg_replace('/<([^>]*)>/es', "'<'.base64_encode('\\1').'>'", $output);
        $output = preg_replace(join('', $pattern), $replace, $output);
        $output = preg_replace('/<([^>]*)>/es', "'<'.base64_decode('\\1').'>'", $output);
        $output = str_replace('\"', '"', $output);

        return $output;
    }

////////////////////////////////////////////////////////////////////////////////

    static function quotes($message, $class = 'quotes'){
        $result = array();

        foreach (explode("\r\n", $message) as $line){
            $line = trim($line);
            $left = substr($line, 0, strpos($line, ' '));

            if (!$level = substr_count($left, '>'))
                $level = substr_count($left, '&gt;');

            $result[] = ($level ? '<span class="'.$class.'_'.$level.'">'.$line.'</span>' : $line);
        }

        return join("\r\n", $result);
    }

////////////////////////////////////////////////////////////////////////////////
}