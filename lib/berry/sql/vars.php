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
            unset($this->joinvalues[$name], self::$cache['HABTM_'.$offset]);
        else
            unset($this->values[$offset]);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetGet($offset){        if ($class = self::_multisave($offset))
            return $class;

        if (!$this->where)
            return;

        $key = self::hash('offsetGet');

        if ($this->relations[$offset]){
            if (!isset(self::$cache[$key.$offset]))
                return self::$cache[$key.$offset] = self::_object($offset);

            return self::$cache[$key.$offset];
        }

        if (!array_key_exists($key, self::$cache)){
            $class = clone $this;

            foreach ($class->select as $k => $v){
                if (preg_match('/(^|\.)('.$this->table.'\.'.$offset.')(\.|$)/', $v, $match))
                    $class->select[$k] = $match[2];
                else
                    unset($class->select[$k]);
            }

            $class->select = ($class->select ? $class->select : array($class->table.'.*'));
            self::$cache[$key] = $class->as_array();
        }

        if (
            ($name = self::_is_HABTM($offset)) and
            ($relation = $this->relations[$name]) and
            !isset(self::$cache[$key][$offset])
        )
            if ($id = self::$cache[$key][$relation['local']['field']]){
                $foreign = $relation['foreign'];
                self::$cache[$key][$offset] = self::$sql->selectCol(
                    self::build('HABTM_IDs'),
                    $foreign['field3'], $foreign['table1'], $foreign['field1'], $id
                );
            } else {
                self::$cache[$key][$offset] = array();
            }

        return self::$cache[$key][$offset];
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetSet($offset, $value){        if (is_array($value)){            if ($class = self::_multisave($offset)){
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

        if ($this->where and ($value == $this[$offset] or !$this[$this->primary_key]))
            return $value;

        if ($name = self::_is_HABTM($offset)){            foreach ($value as $v)
                $this->joinvalues[$name][] = (is_object($v) ? ($v->id ? $v->id : $v->save()) : $v);
        } else {
            if (is_int($value)){
                $value -= $this[$offset];
                $value = $this->raw('`'.$offset.'`'.($value >= 0 ? ' + ' : ' ').$value);
            } elseif (is_object($value)){
                $value = ($value->id ? $value->id : $value->save());
            }

            $this->values[$offset] = $value;
        }
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

    protected function _is_HABTM($offset){        if (
            substr($offset, -4) == '_ids' and
            ($name = substr($offset, 0, -4)) and
            ($relation = $this->relations[$name]) and
            $relation['type'] == 'has_and_belongs_to_many'
        )
            return $name;    }

////////////////////////////////////////////////////////////////////////////////

    protected function _multisave($offset){
        if (is_null($offset))            return $this->multisave[] = $this->table($this->table);

        if (is_int($offset)){
            if (!isset($this->multisave[$offset]))
                $this->multisave[$offset] = $this->table($this->table, $offset);

            return $this->multisave[$offset];
        }
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _object($name){
        $relation = $this->relations[$name];
        $local = $relation['local'];
        $foreign = $relation['foreign'];
        $name = inflector::singular($name);

        if ($relation['type'] == 'has_one')
            return $this->table($name, $this[$relation['local']['field']]);

        if ($relation['type'] == 'belongs_to')
            return $this->table($name)->limit(1)->where(
                $foreign['alias'].'.'.$foreign['field'].' = ?',
                $this[$local['field']]
            );

        if ($relation['type'] == 'has_many')
            return $this->table($name)->where(
                inflector::singular($foreign['alias']).'.'.$foreign['field'].' = ?',
                $this[$local['field']]
            );

        return $this->table($name)->where(
            $foreign['alias2'].'.'.$foreign['field2'].' in (?a)',
            self::$sql->selectCol(
                self::build('HABTM_IDs'),
                $foreign['field3'], $foreign['table1'], $foreign['field1'], $this[$local['field']]
            )
        );
    }

////////////////////////////////////////////////////////////////////////////////

}