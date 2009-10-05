<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Tags_Except extends Except {

////////////////////////////////////////////////////////////////////////////////

    function __construct($parser, $output){        $this->line = array();
        $this->code = xml_get_error_code($parser);
        $this->string = substr(tags::_unsux($output), 7, -8);
        $this->message = xml_error_string($this->code);

        libxml_use_internal_errors(true);
        simplexml_load_string($output);

        foreach (libxml_get_errors() as $error)
            if ($error->code == $this->code){
                if (preg_match('/line (\d+)/', $error->message, $match))
                    $this->line[] = $match[1];
                else
                    $this->line[] = $error->line;
            }

        sort($this->line);
    }

////////////////////////////////////////////////////////////////////////////////

    function __toString(){        foreach ($this->line as $line)
            $href[] = '<a href="#'.$line.'">'.$line.'</a>';

        $result = '<h2>'.$this->message.'</h2>';

        if ($this->line)
            $result .= '<h3>Line'.(b::len($this->line) > 1 ? 's' : '').': '.join(', ', $href).'.</h3>';

        $result .= '<table>';

        foreach (explode("\n", $this->string) as $k => $v){
            $bg = (in_array(++$k, $this->line)  ? array('ffcccc', 'ffe6e6') : array('ccc', 'f3f3f3'));

            $result .= '<tr>';
            $result .= '<td style="background: #'.$bg[0].'; padding: 5px; text-align: center;">';
            $result .= '<a name="'.$k.'"></a>'.$k;
            $result .= '<td style="background: #'.$bg[1].'; padding: 5px;">';
            $result .= '<pre>'.tags::unhtml($v).'</pre>';
        }

        return $result.'</table>';    }

////////////////////////////////////////////////////////////////////////////////

}