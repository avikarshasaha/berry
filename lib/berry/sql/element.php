<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_element implements ArrayAccess, Countable, Iterator {    protected $data = array();

////////////////////////////////////////////////////////////////////////////////

    function __construct($data){        if (is_array($data))            $this->data = arr::assoc($data);    }
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

    function offsetExists($offset){        return isset($this->data[$offset]);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetUnset($offset){        unset($this->data[$offset]);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetGet($offset){        if ($this->data[$offset] instanceof ArrayObject)
            return (array)$this->data[$offset];
        elseif (!is_array($this->data[$offset]))
            return $this->data[$offset];
        $class = clone $this;
        $class->data = &$this->data[$offset];

        return $class;
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetSet($offset, $value){        if ($this->data[$offset] instanceof ArrayObject)
            return $this->data[$offset] = $value;        elseif ($offset === null)
            return $this->data[] = $value;

        $this->data[$offset] = $value;
    }

////////////////////////////////////////////////////////////////////////////////

    function count(){        return b::len($this->data);    }

////////////////////////////////////////////////////////////////////////////////

    function rewind(){        reset($this->data);
    }

////////////////////////////////////////////////////////////////////////////////

    function key(){
        return key($this->data);
    }

////////////////////////////////////////////////////////////////////////////////

    function current(){
        return $this[self::key()];
    }

////////////////////////////////////////////////////////////////////////////////

    function next(){
        next($this->data);
    }

////////////////////////////////////////////////////////////////////////////////

    function valid(){
        return (current($this->data) !== false);
    }

////////////////////////////////////////////////////////////////////////////////

}