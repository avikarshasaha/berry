<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Compare extends Diff {
////////////////////////////////////////////////////////////////////////////////

    function __construct($array1, $array2, $format = 'text'){
        if (is_file($array1))
            $array1 = file($array1);
        elseif (!is_array($array1))
            $array1 = explode("\r\n", $array1);

        if (is_file($array2))
            $array2 = file($array2);
        elseif (!is_array($array2))
            $array2 = explode("\r\n", $array2);

        $diff = new parent($array1, $array2);

        if ($format == 'text')
            $formatter = new UnifiedDiffFormatter;
        elseif ($format == 'html')
            $formatter = new TableDiffFormatter;

        $this->output = $formatter->format($diff);
    }

////////////////////////////////////////////////////////////////////////////////

    function __toString(){
        return $this->output;
    }

////////////////////////////////////////////////////////////////////////////////

}