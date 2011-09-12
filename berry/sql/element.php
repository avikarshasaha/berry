<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_Element extends Object {
    protected $relations = array();

////////////////////////////////////////////////////////////////////////////////

    function __construct($scope = null, $relations = array()){
        if (is_array($scope)){
            $this->scope = arr::assoc($scope);
            $this->relations = $relations;
        }
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetGet($offset){
        if (!isset($this->scope[$offset]) and $this->relations[$offset])
            $this->scope[$offset] = array();

        if (is_array($this->scope[$offset]))
            $this->scope[$offset] = new $this($this->scope[$offset], $this->relations);

        return $this->scope[$offset];
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch(){
        return $this->scope;
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_array(){
        return self::fetch();
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_cell(){
        if (is_array($array = self::fetch_col()))
            return reset($array);

        return $array;
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_col(){
        $array = $this->scope;
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
        if ($array = reset($this->scope))
            return $array;

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    function exists(){
        return (bool)$this->scope;
    }

////////////////////////////////////////////////////////////////////////////////

    function group_by(){
        foreach (func_get_args() as $arg){
            $used = array();

            foreach ($this->scope as $k => $v){
                if (in_array($v[$arg], $used))
                    unset($this->scope[$k]);

                $used[] = $v[$arg];
            }
        }

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function group($group_by = null){
        if (!$group_by)
            return $this;
        elseif (!is_array($group_by))
            $group_by = func_get_args();

        return call_user_func_array(array($this, 'group_by'), (array)$group_by);
    }

////////////////////////////////////////////////////////////////////////////////

    function order_by(){
        $args = array();

        foreach (func_get_args() as $arg){
            $field = reset(explode(' ', $arg));
            $order = 'ASC';
            $array = array();

            if (strpos($arg, 'desc')){
                $order = 'DESC';
            } elseif ($field[0] == '-'){
                $field = substr($field, 1);
                $order = 'DESC';
            }

            foreach ($this->scope as $k => $v)
                $array[$k] = (isset($v[$field]) ? $v[$field] : '');

            $args[] = $array;
            $args[] = constant('SORT_'.$order);
        }

        for ($i = 0, $c = b::len($args); $i < $c; $i++)
            $result[] = '$args['.$i.']';

        $result = 'array_multisort('.join(', ', $result).', $args['.$c.'])';
        $func = create_function('$args', 'return '.$result.';');
        $args[] = &$this->scope;

        $func($args);
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function sort($order_by = null){
        if (!$order_by)
            return $this;
        elseif (!is_array($order_by))
            $order_by = func_get_args();

        return call_user_func_array(array($this, 'order_by'), (array)$order_by);
    }

////////////////////////////////////////////////////////////////////////////////

}