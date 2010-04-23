<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_Query extends SQL_Etc {    protected $query;

////////////////////////////////////////////////////////////////////////////////

    function __construct(){        $args = func_get_args();

        if (is_array($args[0]))
            $this->query = $args[0];
        else
            $this->query = $args;

        $array = $this->query;
        array_shift($array);
        $this->placeholders = $array;
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch(){        $key = self::hash('fetch');

        if (array_key_exists($key, self::$cache))
            return self::$cache[$key];

        return self::$cache[$key] = call_user_func_array(array(self::$connection, 'select'), $this->query);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_cell(){        return call_user_func_array(array(self::$connection, 'selectCell'), $this->query);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_col(){        return call_user_func_array(array(self::$connection, 'selectCol'), $this->query);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_row(){        return call_user_func_array(array(self::$connection, 'selectRow'), $this->query);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_select(){
        return $this->query[0];
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_subquery_select($parent){
        $parent->placeholders = array_merge($this->placeholders, $parent->placeholders);
        return $this->query[0];
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_subquery_join($parent){
        return self::_build_subquery_select($parent);
    }

////////////////////////////////////////////////////////////////////////////////
}