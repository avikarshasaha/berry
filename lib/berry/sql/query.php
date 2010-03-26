<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_Query extends SQL_Etc {

////////////////////////////////////////////////////////////////////////////////

    function __construct(){
        $args = (is_array($args[0]) ? $args[0] : $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch(){

        if (array_key_exists($key, self::$cache))
            return self::$cache[$key];

        $args = $this->placeholders;
        array_unshift($args, $this->query);
        return self::$cache[$key] = call_user_method_array('select', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_cell(){
        array_unshift($args, $this->query);
        return call_user_method_array('selectCell', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_col(){
        array_unshift($args, $this->query);
        return call_user_method_array('selectCol', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_row(){
        array_unshift($args, $this->query);
        return call_user_method_array('selectRow', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_select(){
        return $this->query;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_subquery_select($parent){
        $parent->placeholders = array_merge($this->placeholders, $parent->placeholders);
        return $this->query;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_subquery_join($parent){
        return self::_build_subquery_select($parent);
    }

////////////////////////////////////////////////////////////////////////////////
