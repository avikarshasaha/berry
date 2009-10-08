<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_item extends SQL_vars implements ArrayAccess, Countable, Iterator {    protected $parent;
    protected $array = array();

////////////////////////////////////////////////////////////////////////////////

    function __construct($parent, $array){        $this->parent = $parent;
        $this->array = arr::assoc($array);    }
////////////////////////////////////////////////////////////////////////////////

    function __isset($name){
        return isset($this[$name]);
    }

////////////////////////////////////////////////////////////////////////////////

    function __unset($name){        unset($this[$name]);
    }

////////////////////////////////////////////////////////////////////////////////

    function __get($name){
        return $this[$name];
    }

////////////////////////////////////////////////////////////////////////////////

    function __set($name, $value){        $this[$name] = $value;
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetExists($offset){        return array_key_exists($offset, $this->array);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetUnset($offset){        unset($this->array[$offset]);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetGet($offset){        if ($this->parent->_is_HABTM($offset))
            return $this->array[$offset];        elseif (!is_array($this->array[$offset]))
            return $this->array[$offset];
        $class = clone $this;
        $class->array = &$this->array[$offset];

        return $class;
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetSet($offset, $value){        if ($offset === null)
            return $this->array[] = $value;

        $this->array[$offset] = $value;
    }

////////////////////////////////////////////////////////////////////////////////

    function count(){        return b::len($this->array);    }

////////////////////////////////////////////////////////////////////////////////

    function rewind(){        reset($this->array);
    }

////////////////////////////////////////////////////////////////////////////////

    function current(){
        return $this[self::key()];
    }

////////////////////////////////////////////////////////////////////////////////

    function key(){
        return key($this->array);
    }

////////////////////////////////////////////////////////////////////////////////

    function next(){
        next($this->array);
    }

////////////////////////////////////////////////////////////////////////////////

    function valid(){
        return (current($this->array) !== false);
    }

////////////////////////////////////////////////////////////////////////////////

}