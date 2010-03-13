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

    function offsetExists($offset){        return ($this[$offset] !== null);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetUnset($offset){
        if ($name = self::_is_HABTM($offset))
            unset($this->joinvalues[$name]);
        else
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

    function rewind(){        if ($this->iterator)
            return reset($this->iterator);

        $class = clone $this;
        $class->select = array($class->primary_key);
        $this->iterator = $class->group_by($class->primary_key)->fetch_col();
    }

////////////////////////////////////////////////////////////////////////////////

    function key(){
        return key($this->iterator);
    }

////////////////////////////////////////////////////////////////////////////////

    function current(){
        return $this[(int)current($this->iterator)];
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

        if ($class = self::_multisave($name)){            if (!$this->select)
                return $class;

            if (!isset(self::$cache[$key.$name])){
                $array = $this->fetch_array();
                self::$cache[$key.$name] = new SQL_Element($array[$name]);
            }

            return self::$cache[$key.$name];
        }

        if (!$this->where){
            if ($relation = $this->relations[$name]){
                if (!isset(self::$cache[$key.$name])){                    $class = self::_object($name);

                    if ($relation['type'] == 'has_one')
                        $this[$relation['local']['field']] = $class;

                    if ($relation['type'] == 'belongs_to')
                        $class[$relation['foreign']['field']] = (string)(self::last_id() + 1);

                    if ($relation['type'] == 'has_many'){
                        $class[$relation['foreign']['field']] = (string)(self::last_id() + 1);
                        $this->multisave[] = $class;
                    }

                    if ($relation['type'] == 'has_and_belongs_to_many'){                    }

                    self::$cache[$key.$name] = $class;
                }

                return self::$cache[$key.$name];
            }

            return;
        }

        if (!isset(self::$cache[$key])){            $class = clone $this;

            if ($class->select){
                $class->select[] = $class->primary_key;

                if (substr($name, -3) == '_id' and ($relation = $this->relations[substr($name, 0, -3)]))
                    $class->select[] = $name;
            }

            self::$cache[$key] = new SQL_Element($class->fetch_array());
        }

        if ($relation = $this->relations[$name]){
            if (isset(self::$cache[$key][$name]))
                return self::$cache[$key][$name];
            elseif (!isset(self::$cache[$key.$name])){                $class = self::_object($name);
                if (!self::_get($class->primary_key))                    return self::$cache[$key.$name] = $class;

                if ($relation['type'] == 'has_one' and !$this[$relation['local']['field']])
                    return self::$cache[$key.$name] = $this[$relation['local']['field']] = $class;

                if ($relation['type'] == 'belongs_to' or $relation['type'] == 'has_many')
                    $class->values[$relation['foreign']['field']] = (string)$this->id;

                if ($relation['type'] == 'has_and_belongs_to_many'){                    /*$field = $relation['foreign']['alias2'].'_ids';                    $ids = $this[$field];
                    $ids[] = $this->id;
                    $ids['#'] = true;
                    $class->values[$field] = $ids;
                    $class->relations[$name] = $relation;*/
                }

                return self::$cache[$key.$name] = $this->multisave[] = $class;
            }

            return self::$cache[$key.$name];
        }

        if (
            ($_name = self::_is_HABTM($name)) and
            ($relation = $this->relations[$_name]) and
            !isset(self::$cache[$key][$name])
        )
            if ($id = self::_get($relation['local']['field'])){                $foreign = $relation['foreign'];                self::$cache[$key][$name] = new ArrayObject(self::table($foreign['table1'])->
                    select($foreign['field3'])->
                    where($foreign['field1'].' = ?', $id)->
                    fetch_col());
            } else {
                self::$cache[$key][$name] = new ArrayObject;
            }

        return self::$cache[$key][$name];
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _set($name, $value){        $func = create_function('$a', 'return !is_int($a);');
        if (is_array($value)){
            if ($class = self::_multisave($name)){
                foreach ($value as $k => $v)
                    $class[$k] = $v;
            }

            $array = array_map($func, array_keys($value));
            $value = array_unique($value);

            if (in_array(true, $array))
                asort($value);
            else
                sort($value);
        }

        if (($tmp = self::_get($name)) instanceof ArrayObject or is_array($tmp)){
            $tmp = (array)$tmp;
            sort($tmp);
        }

        if ($this->where and ($value == $tmp or !self::_get($this->primary_key)))
            return $value;

        if (is_int($value)){            $tmp = $value;
            $tmp -= self::_get($name);
            $this->values[$name] = self::raw($this->table.'.'.$name.' '.($tmp >= 0 ? '+' : '').$tmp);
        } else {            $this->values[$name] = $value;        }

        if (isset(self::$cache[$key = self::hash('_get')]))
            self::$cache[$key][$name] = $value;
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

    protected function _multisave($name, $values = array()){        if (is_null($name) or $values){            if ($this->multisave[0]){                $class = clone $this->multisave[0];            } else {                $class = clone $this;
                $class->where = $this->values = $class->multisave = array();
                $this->values = $values;
            }

            return $this->multisave[] = $class;
        }

        if (is_int($name)){
            if (!isset($this->multisave['#'.$name])){                if (!$this->where)
                    return;
                $class = clone $this;
                $class->select = $class->group_by = array($this->primary_key);
                $array = $class->fetch_col();

                if (!$class->id = $array[$name])
                    return;

                $class->select = $this->select;
                $class->group_by = $this->group_by;
                $class->where($this->primary_key.' = ?d', $class->id);

                $this->multisave['#'.$name] = $class;
            }

            return $this->multisave['#'.$name];
        }
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _object($name){
        $relation = $this->relations[$name];
        $local = $relation['local'];
        $foreign = $relation['foreign'];
        $name = inflector::singular($name);
        $class = self::table($name);

        if (!$id = $this[$local['field']])
            return $class;

        if ($relation['type'] == 'has_one'){            $class->id = $id;
            return $class->where($foreign['field'].' = ?d', $id);
        }

        if ($relation['type'] == 'belongs_to')
            return $class->limit(1)->where($foreign['field'].' = ?d', $id);

        if ($relation['type'] == 'has_many')
            return $class->where($foreign['field'].' = ?d', $id);

        return $class->where(
            $name.'.'.$foreign['field2'].' in (?a)',
            self::table($foreign['table1'])->
                select($foreign['field3'])->
                where($foreign['field1'].' = ?d', $id)->
                fetch_col()
        );
    }

////////////////////////////////////////////////////////////////////////////////

}