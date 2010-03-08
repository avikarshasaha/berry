<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Check_Except extends Except {

////////////////////////////////////////////////////////////////////////////////

    function __construct($message, $string = ''){        $this->string = array();

        if (!$string){
            $_post = arr::flat($_POST);
            $_files = arr::flat($_FILES);
            $_get = arr::flat($_GET);
        }
        foreach ($message as $k => $v)
            if ($error = check::$errors[$k]){                if ($string)                    $this->string[$k] = $string;
                elseif (isset($_post[$k]))
                    $this->string[$k] = '_POST';
                elseif (isset($_files[preg_replace('/(\w+)\.(.*)/', '\\1.name.\\2', $k)]))
                    $this->string[$k] = '_FILES';
                elseif (isset($_get[$k]))
                    $this->string[$k] = '_GET';
                $v = (array)$v;
                if (is_array($message = str::json($v[1])))
                    $v[1] = $message[$error];

                $this->message[$k] = $v;
            }
    }

////////////////////////////////////////////////////////////////////////////////

    function __toString(){        $bg = $this->colors[1];

        foreach ($this->message as $k => $v){
            $result .= '<tr>';
            $result .= '<td style="background: #'.$bg[0].'; padding: 5px;">';
            $result .= '<b>'.piles::var2name($this->string[$k].'.'.$k).'</b>';
            $result .= '</td>';
            $result .= '<td style="background: #'.$bg[1].'; padding: 5px;">';
            $result .= ($v[1] ? $v[1] : 'must be '.$v[0]);
            $result .= '</td>';
            $result .= '</tr>';
        }

        return '<table>'.$result.'</table>';    }

////////////////////////////////////////////////////////////////////////////////

}