<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Piles_Except extends Except {

////////////////////////////////////////////////////////////////////////////////

    function __construct($message, $string){
        $message = explode(':', trim(strip_tags($message)));

        $this->message = $message[0];
        $this->string = $string;
        $this->code = preg_replace('/(.*?)(T\_([A-Z_]+))(.*?)/e', "constant('\\2')", $message[0]);
        $this->file = 'eval()';
        $this->line = strrchr(end($message), ' ');    }

////////////////////////////////////////////////////////////////////////////////

    function __toString(){
        $result  = '<h2>'.$this->message.'</h2>';
        $result .= '<h3>Line: <a href="#'.$this->line.'">'.$this->line.'</a>.</h3>';
        $result .= '<table>';

        foreach (explode("\n", $this->string) as $k => $v){
            $bg = $this->colors[++$k == $this->line];

            $result .= '<tr>';
            $result .= '<td style="background: #'.$bg[0].'; padding: 5px; text-align: center;">';
            $result .= '<a name="'.$k.'"></a>'.$k;
            $result .= '</td>';
            $result .= '<td style="background: #'.$bg[1].'; padding: 5px;">';
            $result .= '<pre>'.str::plain($v).'</pre>';
            $result .= '</td>';
            $result .= '</tr>';
        }

        return $result.'</table>';
    }

////////////////////////////////////////////////////////////////////////////////

}