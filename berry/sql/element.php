<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_Element extends SQL_Vars implements Countable {
    protected $o;
    protected $_scope;

////////////////////////////////////////////////////////////////////////////////

    function __construct($object = null){
        if (is_array($object))
            $this->scope = $this->_scope = $object;
        elseif ($this->o = $object)
            $this->scope = $this->_scope = $o->fetch_array();
    }

////////////////////////////////////////////////////////////////////////////////

    function count(){
        return b::len($this->scope);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetExists($offset){
        return isset($this->scope[$offset]);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetUnset($offset){
        unset($this->scope[$offset]);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetGet($offset){
        return (method_exists($this->o, ($func = '_get_'.$offset)) ? $this->o->$func() : self::_get($offset));
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetSet($offset, $value){
        method_exists($this->o, ($func = '_set_'.$offset)) ? $this->o->$func($value) : self::_set($offset, $value);
    }

////////////////////////////////////////////////////////////////////////////////

    function rewind(){
        reset($this->scope);
    }

////////////////////////////////////////////////////////////////////////////////

    function key(){
        return key($this->scope);
    }

////////////////////////////////////////////////////////////////////////////////

    function current(){
        return $this[self::key()];
    }

////////////////////////////////////////////////////////////////////////////////

    function next(){
        next($this->scope);
    }

////////////////////////////////////////////////////////////////////////////////

    function valid(){
        return (current($this->scope) !== false);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _get($name){
        if (!isset($this->scope[$name]) and $this->o->relations[$name])
            $this->scope[$name] = array();

        if (is_array($this->scope[$name])){
            $this->scope[$name] = new $this($this->scope[$name]);
            $this->scope[$name]->o = $this->o;

            if ($this->o->relations[$name])
                $this->scope[$name]->o = SQL::table($name);
        }

        if (is_int($name))
            $this->o->values = $this->_scope[$name];

        return $this->scope[$name];
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _set($name, $value){
        if ($name === null)
            $this->scope[] = $value;
        else
            $this->scope[$name] = $value;
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

    function limit($limit){
        if ($this->limit = (is_numeric($limit) ? $limit : 0)){
            $this->iterator = ($this->iterator ? $this->iterator : $this->_scope);
            $this->scope = $this->_scope = array_slice($this->iterator, $this->offset, $this->limit);
        }

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function offset($offset){
        if ($this->offset = (is_numeric($offset) ? $offset : 0)){
            $this->iterator = ($this->iterator ? $this->iterator : $this->_scope);
            $this->scope = $this->_scope = array_slice($this->iterator, $this->offset, $this->limit);
        }

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function page($page){
        if ($page > 0)
            $this->offset($page * $this->limit - $this->limit);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

}