<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class HTML {
////////////////////////////////////////////////////////////////////////////////

    function block($key, $value = null, $sort = 50){
        static $block, $sorts;

        if ($value !== null and !in_array($value, (array)$block[$key])){
            $sorts[$key][] = $sort;
            return $block[$key][] = $value;
        } elseif ($value === null and $block[$key]){
            array_multisort($sorts[$key], SORT_ASC, $block[$key]);
            return $block[$key];
        }

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    function msg($key, $value = null, $sort = 50){
        static $sorts;

        if ($value !== null and !in_array($value, (array)$_SESSION['html']['msg'][$key])){
            $sorts[$key][] = $sort;

            return $_SESSION['html']['msg'][$key][] = $value;
        } elseif ($value === null and $_SESSION['html']['msg'][$key]){
            $msg[$key] = $_SESSION['html']['msg'][$key];

            unset($_SESSION['html']['msg'][$key]);
            array_multisort($sorts[$key], SORT_ASC, $msg[$key]);
            return $msg[$key];
        }

        return array();
    }////////////////////////////////////////////////////////////////////////////////

    function dropdown($name, $array, $selected = array()){
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
                $attr['#text'] .= '<optgroup label="'.$k.'" />';

                foreach ($v as $a => $b)
                    $attr['#text'] .= '<option value="'.$a.'"'.(in_array($a, $selected) ? ' selected="selected"' : '').'>'.($b !== '' ? $b : $a).'</option>';
            } else {
                $attr['#text'] .= '<option value="'.$k.'"'.(in_array($k, $selected) ? ' selected="selected"' : '').'>'.($v !== '' ? $v : $k).'</option>';
            }

        return tags::fill('select', $attr);
    }

////////////////////////////////////////////////////////////////////////////////

    function js($text){
        $attr = array('type' => 'text/javascript', '#text' => '');

        if (strtolower(substr($text, -3)) != '.js' or strpos($text, "\n"))
            $attr['#text'] = $text;
        else
            $attr['src'] = $text;

        return tags::fill('script', $attr);
    }

////////////////////////////////////////////////////////////////////////////////

    function css($text, $media = ''){
        $attr = array('type' => 'text/css');

        if ($media)
            $attr['media'] = $media;

        if (strtolower(substr($text, -4)) != '.css' or strpos($text, "\n")){
            $attr['#text'] = $text;
            return tags::fill('style', $attr);
        }

        $attr['href'] = $text;
        $attr['rel'] = 'stylesheet';

        return tags::fill('link', $attr);
    }

////////////////////////////////////////////////////////////////////////////////
}