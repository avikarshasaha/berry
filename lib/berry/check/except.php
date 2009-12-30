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

    function __construct($message, $code = '_POST'){        $this->code = $code;
        foreach ($message as $k => $v)
            if ($error = check::$error[$k]){                $v = (array)$v;
                if (is_array($string = str::json($v[1])))
                    $v[1] = $string[$error];

                $this->message[$k] = $v;
            }
    }

////////////////////////////////////////////////////////////////////////////////

    function __toString(){
        foreach ($this->message as $k => $v){
            $result .= '<tr>';
            $result .= '<td style="background: #ffcccc; padding: 5px;">';
            $result .= '<b>'.tags::elmname_unparse($this->code.'.'.$k).'</b>';
            $result .= '</td>';
            $result .= '<td style="background: #ffe6e6; padding: 5px;">';
            $result .= ($v[1] ? $v[1] : 'must be '.$v[0]);
            $result .= '</td>';
            $result .= '</tr>';
        }

        return '<table>'.$result.'</table>';    }

////////////////////////////////////////////////////////////////////////////////

}