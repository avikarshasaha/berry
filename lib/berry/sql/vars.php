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

    protected function _get($name){
        if ($class = self::_multisave($name)){            if (!$this->select)
                return $class;

            $array = $this->fetch_array();
            return new SQL_Element($array[$name]);
        }

        if (!$this->where)
            return;

        $key = self::hash('_get');

        if (!array_key_exists($key, self::$cache)){            $class = clone $this;

            if ($class->select){
                $class->select[] = $class->primary_key;

                if (substr($name, -3) == '_id' and ($relation = $this->relations[substr($name, 0, -3)]))
                    $class->select[] = $name;
            }

            self::$cache[$key] = new SQL_Element($class->fetch_array());
        }

        if ($this->relations[$name]){
            if (isset(self::$cache[$key][$name]))
                return self::$cache[$key][$name];
            elseif (!isset(self::$cache[$key.$name]))
                return self::$cache[$key.$name] = self::_object($name);

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

    protected function _set($name, $value){        if (is_array($value)){
            if ($class = self::_multisave($name)){
                foreach ($value as $k => $v)
                    $class[$k] = $v;
            }

            $func = create_function('$a', 'return !is_int($a);');
            $array = array_map($func, array_keys($value));
            $array = array_unique($array);

            if (in_array(true, $array))
                asort($value);
            else
                sort($value);
        }

        if (($tmp = self::_get($name)) instanceof ArrayObject)
            $tmp = (array)$tmp;

        if ($this->where and ($value == $tmp or !self::_get($this->primary_key)))
            return $value;

        if ($_name = self::_is_HABTM($name)){
            foreach ($value as $v)                if (is_object($v)){                    $vars = get_class_vars($v);
                    $key = ($vars['primary_key'] ? $vars['primary_key'] : 'id');
                    $this->joinvalues[$_name][] = ($v[$key] ? $v[$key] : $v->save());
                } else {
                    $this->joinvalues[$_name][] = $v;
                }
        } else {
            if (is_int($value)){                $tmp = $value;
                $tmp -= self::_get($name);
                $this->values[$name] = self::raw($this->table.'.'.$name.' '.($tmp >= 0 ? '+' : '').$tmp);
            } elseif (is_object($value)){                $vars = get_class_vars($value);
                $key = ($vars['primary_key'] ? $vars['primary_key'] : 'id');
                $this->values[$name] = ($value[$key] ? $value[$key] : $value->save());
            } else {                $this->values[$name] = $value;            }
        }

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

    protected function _multisave($name){
        if (is_null($name))            return $this->multisave[] = self::table($this->table);

        if (is_int($name)){
            if (!isset($this->multisave[$name]))
                $this->multisave[$name] = self::table($this->table, $name);

            return $this->multisave[$name];
        }
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _object($name){
        $relation = $this->relations[$name];
        $local = $relation['local'];
        $foreign = $relation['foreign'];
        $name = inflector::singular($name);

        if ($relation['type'] == 'has_one')
            return self::table($name, $this[$relation['local']['field']]);

        if ($relation['type'] == 'belongs_to')
            return self::table($name)->limit(1)->where(
                $name.'.'.$foreign['field'].' = ?',
                $this[$local['field']]
            );

        if ($relation['type'] == 'has_many')
            return self::table($name)->where(
                $name.'.'.$foreign['field'].' = ?',
                $this[$local['field']]
            );

        return self::table($name)->where(
            $name.'.'.$foreign['field2'].' in (?a)',
            self::table($foreign['table1'])->
                select($foreign['field3'])->
                where($foreign['field1'].' = ?', $this->id)->
                fetch_col()
        );
    }

////////////////////////////////////////////////////////////////////////////////

}