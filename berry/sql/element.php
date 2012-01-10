<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_Element extends SQL_Vars implements Countable {
    protected $o;
    protected $scope = array();
    protected $_scope = array();
    protected $no_magick_pls = false;

////////////////////////////////////////////////////////////////////////////////

    function __construct($object = null){
        if (is_array($object)){
            $this->scope = $this->_scope = $object;
        } elseif ($this->o = $object){
            $this->scope = $this->_scope = $object->fetch_array();
            $this->no_magick_pls = true;
        }
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
        if ($this->no_magick_pls)
            return self::_get($offset);

        return (method_exists($this->o, ($func = '_get_'.$offset)) ? $this->o->$func() : self::_get($offset));
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetSet($offset, $value){
        if ($this->no_magick_pls)
            return self::_set($offset, $value);

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
        if (is_int($name))
            $this->o->values = $this->_scope[$name];

        if (is_array($this->scope[$name])){
            $this->scope[$name] = new self($this->scope[$name]);
            $this->scope[$name]->o = $this->o;

            if ($this->o->relations[$name])
                $this->scope[$name]->o = self::table($name, $this->o[$this->o->primary_key]);
        }

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

    function fetch_row(){
        if ($array = reset($this->scope))
            return $array;

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_pair($number1 = 1, $number2 = null){
        if ($number2 === null)
            list($number1, $number2) = array(0, $number1);

        $array1 = $array2 = self::fetch();
        self::_fetch_column($array2, 0, $number1);
        $result = array();

        foreach ($array2 as $k => $v)
            if ($number2){
                for ($i = 0; $i < $number2; $i++)
                    $result[$v] = next($array1[$k]);
            } else {
                $result[$v] = reset($array1[$k]);
            }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_column($number = null){
        $array = $this->scope;
        self::_fetch_column($array, 0, $number);

        return ($array ? $array : array());
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _fetch_column(&$v, $k = 0, $number = null){
        if ($number){
            for ($i = 0; $i < $number; $i++)
                $cell = next($v);
        } else {
            $cell = reset($v);
        }

        if (!is_array($cell))
            $v = $cell;
        else
            array_walk($v, array('self', '_fetch_column'), $number);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_cell($number = 0){
        if (is_array($array = self::fetch_column($number)))
            return reset($array);

        return $array;
    }

////////////////////////////////////////////////////////////////////////////////

    function count(){
        return count($this->scope);
    }

////////////////////////////////////////////////////////////////////////////////

    function exists(){
        return (bool)$this->scope;
    }

////////////////////////////////////////////////////////////////////////////////

    function sort($order_by = null){
        if (!$order_by)
            $order_by = $this->o->primary_key;
        elseif (!is_array($order_by))
            $order_by = func_get_args();

        foreach ($order_by as $v){
            $field = reset(explode(' ', $v));
            $order = 'ASC';
            $array = array();

            if (strpos($v, 'desc')){
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

        for ($i = 0, $c = count($args); $i < $c; $i++)
            $result[] = '$args['.$i.']';

        $result = 'array_multisort('.join(', ', $result).', $args['.$c.'])';
        $func = create_function('$args', 'return '.$result.';');
        $args[] = &$this->scope;

        $func($args);
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function group($group_by = null){
        if (!$group_by)
            $group_by = $this->o->primary_key;
        elseif (!is_array($group_by))
            $group_by = func_get_args();

        foreach ((array)$group_by as $v){
            $used = array();

            foreach ($this->scope as $k2 => $v2){
                if (in_array($v2[$v], $used))
                    unset($this->scope[$k2]);

                $used[] = $v2[$v];
            }
        }

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function limit($limit = 0, $offset = 0){
        if ($limit > 0 and is_numeric($offset))
            $this->scope = $this->_scope = array_slice($this->scope, $offset, $limit);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function page($limit = 0, $page = 0){
        return self::limit($limit, ($page * $limit - $limit));
    }

////////////////////////////////////////////////////////////////////////////////

}
