<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
abstract class SQL_vars extends SQL_etc implements ArrayAccess, Iterator {
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

    function offsetGet($offset){        return (method_exists($this, $func = '_get_'.$offset) ? $this->$func() : self::_get($offset));
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetSet($offset, $value){        if (method_exists($this, $func = '_set_'.$offset))
            return $this->$func($value);

        self::_set($offset, $value);
    }

////////////////////////////////////////////////////////////////////////////////

    function rewind(){
        $this->iterator = 0;
    }

////////////////////////////////////////////////////////////////////////////////

    function current(){
        return $this[$this->iterator + 1];
    }

////////////////////////////////////////////////////////////////////////////////

    function key(){
        return $this->iterator;
    }

////////////////////////////////////////////////////////////////////////////////

    function next(){
        ++$this->iterator;
    }

////////////////////////////////////////////////////////////////////////////////

    function valid(){
        return ($this->iterator < b::len($this));
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _get($name){
        if ($class = self::_multisave($name))
            return $class;

        if (!$this->where)
            return;

        $key = self::hash('_get');

        if ($this->relations[$name]){
            if (!isset(self::$cache[$key.$name]))
                return self::$cache[$key.$name] = self::_object($name);

            return self::$cache[$key.$name];
        }

        if (!array_key_exists($key, self::$cache)){            $class = clone $this;

            foreach ($class->select as $k => $v){
                if (preg_match('/(^|\.)('.$this->table.'\.'.$name.')(\.|$)/', $v, $match))
                    $class->select[$k] = $match[2];
                else
                    unset($class->select[$k]);
            }

            $class->select = ($class->select ? $class->select : array($class->table.'.*'));
            self::$cache[$key] = $class->as_array();
        }

        if (
            ($_name = self::_is_HABTM($name)) and
            ($relation = $this->relations[$_name]) and
            !isset(self::$cache[$key][$name])
        )
            if ($id = self::_get($relation['local']['field'])){                $foreign = $relation['foreign'];                self::$cache[$key][$name] = self::table($foreign['table1'])->
                    select($foreign['field3'])->
                    where($foreign['field1'].' = ?', $id)->
                    col();
            } else {
                self::$cache[$key][$name] = array();
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

        if ($this->where and ($value == self::_get($name) or !self::_get($this->primary_key)))
            return $value;

        if ($_name = self::_is_HABTM($name)){
            foreach ($value as $v)
                $this->joinvalues[$_name][] = (is_object($v) ? ($v->id ? $v->id : $v->save()) : $v);
        } else {
            if (is_int($value)){                $tmp = $value;
                $tmp -= self::_get($name);
                $this->values[$name] = self::raw($this->table.'.'.$name.' '.($tmp >= 0 ? '+' : '').$tmp);
            } elseif (is_object($value)){
                $this->values[$name] = ($value->id ? $value->id : $value->save());
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
                $foreign['alias'].'.'.$foreign['field'].' = ?',
                $this[$local['field']]
            );

        if ($relation['type'] == 'has_many')
            return self::table($name)->where(
                inflector::singular($foreign['alias']).'.'.$foreign['field'].' = ?',
                $this[$local['field']]
            );

        return self::table($name)->where(
            $foreign['alias2'].'.'.$foreign['field2'].' in (?a)',
            self::table($foreign['table1'])->
                select($foreign['field3'])->
                where($foreign['field1'].' = ?', $id)->
                col()
        );
    }

////////////////////////////////////////////////////////////////////////////////

}