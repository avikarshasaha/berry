<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_Union extends SQL_Etc {    protected $table = '<union>';

////////////////////////////////////////////////////////////////////////////////

    function __construct($array){        foreach ($array as $class){            if (!$class->select)
                $class->select[] = '*';

            $this->union[] = $class->build('select');
            $this->placeholders = array_merge($this->placeholders, $class->placeholders);
        }
    }

////////////////////////////////////////////////////////////////////////////////

    function order_by(){
        foreach (func_get_args() as $arg)
            $this->order_by[] = ($arg[0] == '-' ? substr($arg, 1).' desc' : $arg);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function limit($limit){
        $this->limit = (is_numeric($limit) ? $limit : self::$sql->escape($limit));
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function offset($offset){
        $this->offset = (is_numeric($offset) ? $offset : self::$sql->escape($offset));
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function page($page){
        $this->offset($page * $this->limit - $this->limit);
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch(){        $key = self::hash('fetch');

        if (array_key_exists($key, self::$cache))
            return self::$cache[$key];

        $args = $this->placeholders;
        array_unshift($args, self::build('select'));
        return self::$cache[$key] = call_user_func_array(array(self::$sql, 'select'), $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_cell(){
        if (is_array($array = self::fetch_col()))
            return reset($array);

        return $array;
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_col(){
        $array = self::fetch();
        self::_fetch_col($array);

        return ($array ? $array : array());
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _fetch_col(&$v){
        if (!is_array($cell = reset($v)))
            $v = $cell;
        else
            array_walk($v, array('self', '_fetch_col'));
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_row(){
        if ($array = reset(self::fetch()))
            return $array;

        return array();
    }

////////////////////////////////////////////////////////////////////////////////
}