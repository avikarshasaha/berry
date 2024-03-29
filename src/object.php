<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru/>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Object implements ArrayAccess, Countable, Iterator {
    protected $scope = array();

////////////////////////////////////////////////////////////////////////////////

    function __construct($scope = null){
        if ($scope)
            $this->scope = $scope;
    }

////////////////////////////////////////////////////////////////////////////////

    function __isset($name){
        return isset($this[$name]);
    }

////////////////////////////////////////////////////////////////////////////////

    function __unset($name){
        unset($this[$name]);
    }

////////////////////////////////////////////////////////////////////////////////

    function __get($name){
        return $this[$name];
    }

////////////////////////////////////////////////////////////////////////////////

    function __set($name, $value){
        $this[$name] = $value;
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
        if (!isset($this->scope[$offset]))
            $this->scope[$offset] = array();

        if (is_array($this->scope[$offset]))
            $this->scope[$offset] = new self($this->scope[$offset]);

        return $this->scope[$offset];
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetSet($offset, $value){
        if ($offset === null)
            return $this->scope[] = $value;

        $this->scope[$offset] = $value;
    }

////////////////////////////////////////////////////////////////////////////////

    function count(){
        return count($this->scope);
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

}
