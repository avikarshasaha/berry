<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Compare extends Diff {
////////////////////////////////////////////////////////////////////////////////

    function __construct($array1, $array2, $format = 'text'){
        if (!is_array($array1))
            $array1 = (is_file($array1) ? file($array1) : explode("\r\n", $array1));

        if (!is_array($array2))
            $array2 = (is_file($array2) ? file($array2) : explode("\r\n", $array2));

        if ($format == 'text')
            $this->formatter = new UnifiedDiffFormatter;
        elseif ($format == 'html')
            $this->formatter = new TableDiffFormatter;

        $this->compare = array($array1, $array2);
    }

////////////////////////////////////////////////////////////////////////////////

    function __toString(){
        return $this->formatter->format(new parent($this->compare[0], $this->compare[1]));
    }

////////////////////////////////////////////////////////////////////////////////

}