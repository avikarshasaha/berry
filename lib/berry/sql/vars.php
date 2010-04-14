<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
abstract class SQL_Vars extends SQL_Etc implements ArrayAccess, Iterator {
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

    function offsetExists($offset){        if (
            $this[$offset] instanceof SQl or
            $this[$offset] instanceof SQl_Element
        )
            return (b::len($this[$offset]) > 0);

        return ($this[$offset] !== null);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetUnset($offset){        $this[$offset] = null;
        unset($this->values[$offset]);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetGet($offset){        return (method_exists($this, ($func = '_get_'.$offset)) ? $this->$func() : self::_get($offset));
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetSet($offset, $value){
        method_exists($this, ($func = '_set_'.$offset)) ? $this->$func($value) : self::_set($offset, $value);
    }

////////////////////////////////////////////////////////////////////////////////

    function rewind(){        if (!isset($this->iterator)){            if ($len = b::len($this))
                $this->iterator = range(0, ($len - 1));
            else
                $this->iterator = array();
        }

        return reset($this->iterator);
    }

////////////////////////////////////////////////////////////////////////////////

    function key(){
        return key($this->iterator);
    }

////////////////////////////////////////////////////////////////////////////////

    function current(){
        return $this[current($this->iterator)];
    }

////////////////////////////////////////////////////////////////////////////////

    function next(){
        next($this->iterator);
    }

////////////////////////////////////////////////////////////////////////////////

    function valid(){
        return (current($this->iterator) !== false);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _get($name){        $key = self::hash('_get');

        if ($this->select and !isset(self::$cache[$key])){
            $class = clone $this;
            $class->select[] = $class->primary_key;

            self::$cache[$key] = new SQL_Element($class->fetch_array());
        }

        if (isset(self::$cache[$key][$name]))            return self::$cache[$key][$name];

        if (is_null($name)){
            $key = ($this->parallel ? (max(array_keys($this->parallel)) + 1) : 0);

            if ($this->where and $key < ($count = b::len($this)))
                $key = $count;

            $class = clone $this;
            $class->parallel = $class->where = array();

            return $this->parallel[$key] = $class;
        }

        if (isset($this->values[$name]))
            return $this->values[$name];

        if ($this->relations[$name]){
            if (!isset($this->parallel[$name]))
                $this->parallel[$name] = self::_object($name);

            return $this->parallel[$name];
        }

        if (is_int($name)){
            if (!isset($this->parallel[$name])){
                if ($this->parent and !$this->where)
                    return;

                $class = clone $this;
                $class->parallel = array();
                $class->select = $class->group_by = array($this->primary_key);
                $array = $class->fetch_col();

                if (!$class->id = $array[$name])
                    return;

                $class->select = $this->select;
                $class->group_by = $this->group_by;
                $class->where($this->primary_key.' = ?d', $class->id);

                $this->parallel[$name] = $class;
            }

            return $this->parallel[$name];
        }

        if ($_name = self::_is_HABTM($name)){
            $relation = $this->relations[$_name];

            if ($id = $this[$relation['local']['field']]){
                $foreign = $relation['foreign'];
                $this->values[$name] = new ArrayObject(
                    self::table($foreign['table1'])->
                    select($foreign['field3'])->
                    where($foreign['field1'].' = ?', $id)->
                    fetch_col()
                );
            } else {
                $this->values[$name] = new ArrayObject;
            }

            $tmp = (array)$this->values[$name];
            sort($tmp);
            self::$cache[$this->table][$name] = $tmp;

            return $this->values[$name];
        }

        if (!isset($this->parallel[$this->table])){            $array = ($this->where ? $this->fetch_array() : array());
            $this->parallel[$this->table] = new SQL_Element($array);
        }

        return $this->parallel[$this->table][$name];
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _set($name, $value){
        if ($value == $this[$name])
            return;

        if ($value instanceof SQL){
            return;

            if (
                (!$relation = $this->relations['#']) and
                (!$relation = $this->relations[$value->table]) and
                (!$relation = $this->relations[inflector::tableize($value->table)])
            )
                return;

            //if ($name === null)

            if ($id = $this[$relation['local']['field']])
                $value->where($value->primary_key.' = ?d', $id);

            $value->parent = $this;
            $value->relations['#'] = $relation;
            $this->parallel[$value->table] = $value;
        } elseif ($name === null){            $this->values[] = $value;
        } elseif (self::_is_HABTM($name)){            $this->values[$name] = new ArrayObject($value);        } else {
            $this->values[$name] = $value;
        }

        if ($relation = $this->relations['#']){
            $local = $relation['local'];
            $foreign = $relation['foreign'];

            if ($relation['type'] == 'has_one' and !$this->parent[$local['field']]){
                $class = clone $this->parent;
                $class->parallel = array();
                $class->values = array('#'.$local['field'] => $foreign['alias']);
                $this->parent->parallel[] = $class;
            }

            if ($relation['type'] == 'belongs_to' or $relation['type'] == 'has_many'){
                if ($this->parent->id)
                    $this->values[$foreign['field']] = $this->parent->id;
                else
                    $this->values['#'.$foreign['field']] = $local['alias'];
            }

            if ($relation['type'] == 'has_and_belongs_to_many' and !$this->id){
                $class = self::table($foreign['alias1']);
                $class->parallel = array();
                $class->values = array('#'.$foreign['field3'] => inflector::singular($foreign['alias2']));

                if ($this->parent->id)
                    $class->values[$foreign['field1']] = $this->parent->id;
                else
                    $class->values['#'.$foreign['field1']] = $local['alias'];

                $this->parent->parallel[] = $class;
            }
        }
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _is_HABTM($name){        if (
            substr($name, -4) == '_ids' and
            ($_name = substr($name, 0, -4)) and
            ($relation = $this->relations[$_name]) and
            $relation['type'] == 'has_and_belongs_to_many'
        )
            return $_name;    }

////////////////////////////////////////////////////////////////////////////////

    protected function _object($name){
        $relation = $this->relations[$name];
        $local = $relation['local'];
        $foreign = $relation['foreign'];
        $name = inflector::singular($name);

        $class = self::table($name);
        $class->parent = $this;
        $class->relations['#'] = $relation;

        if (!$id = $this[$local['field']])
            return $class;

        if ($relation['type'] == 'has_and_belongs_to_many')
            return $class->where(
                $name.'.'.$foreign['field2'].' in (?a)',
                self::table($foreign['table1'])->
                select($foreign['field3'])->
                where($foreign['field1'].' = ?d', $id)->
                fetch_col()
            );

        $class->where($foreign['field'].' = ?d', $id);

        if ($relation['type'] == 'has_one'){
            $class->id = $id;
        } elseif ($relation['type'] == 'belongs_to'){
            $class->id = -1;
            $class->limit = 1;
        }

        return $class;
    }

////////////////////////////////////////////////////////////////////////////////

}