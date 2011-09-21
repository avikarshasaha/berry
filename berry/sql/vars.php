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
        if (
            $this[$offset] instanceof SQl or
            $this[$offset] instanceof SQl_Element
        )
            return (count($this[$offset]) > 0);

        return ($this[$offset] !== null);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetUnset($offset){
        $this[$offset] = null;
        unset($this->values[$offset]);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetGet($offset){
        return (method_exists($this, ($func = '_get_'.$offset)) ? $this->$func() : self::_get($offset));
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetSet($offset, $value){
        method_exists($this, ($func = '_set_'.$offset)) ? $this->$func($value) : self::_set($offset, $value);
    }

////////////////////////////////////////////////////////////////////////////////

    function rewind(){
        if ($count = $this->count())
            $this->iterator = range(0, ($count - 1));
        else
            $this->iterator = array();

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

    protected function _get($name){
        $key = self::_hash();

        if (is_int($name) and $name < 0)
            $name = ($this->count() + $name);

        if (($this->with or $this->raw_query) and !isset(self::$cache[$key])){
            $this->with($this->primary_key);
            self::$cache[$key] = new SQL_Element($this);
        }

        if (
            isset(self::$cache[$key][$name]) or
            (is_array(self::$cache[$key]) and array_key_exists($name, self::$cache[$key]))
        )
            return self::$cache[$key][$name];

        if ($name === null){
            $key = ($this->children ? (max(array_keys($this->children)) + 1) : 0);

            if ($this->part('where') and $key < ($count = count($this)))
                $key = $count;

            $class = clone $this;
            $class->children = array();

            $class->reset('where');

            return $this->children[$key] = $class;
        }

        if (isset($this->values[$name]))
            return $this->values[$name];

        if ($this->relations[$name]){
            if (!isset($this->children[$name]))
                $this->children[$name] = self::_object($name);

            return $this->children[$name];
        }

        if (is_int($name)){
            if (!isset($this->children[$name])){
                if ($this->parent and !$this->part('where'))
                    return;

                $class = clone $this;
                $class->children = array();

                $class->reset('group');
                $class->with($class->primary_key)->group();

                $array = $class->fetch_column();

                if (!$class->id = $array[$name])
                    return;

                $class->with('*')->where($class->id);
                $class->reset('group');

                if ($this->part('group'))
                    $class->group(join(', ', $this->part('group')));

                $this->children[$name] = $class;
            }

            return $this->children[$name];
        }

        if ($_name = self::_is_HABTM($name)){
            $relation = $this->relations[$_name];

            if ($id = $this[$relation['local']['field']]){
                $foreign = $relation['foreign'];
                $this->values[$name] = new ArrayObject(
                    self::table($foreign['table1'])->with($foreign['field3'])->
                    where(array($foreign['field1'] => $id))->fetch_column()
                );
            } else {
                $this->values[$name] = new ArrayObject;
            }

            $tmp = (array)$this->values[$name];
            sort($tmp);
            self::$cache[$this->table][$name] = $tmp;

            return $this->values[$name];
        }

        if (!isset($this->children[$this->alias]))
            $this->children[$this->alias] = new SQL_Element($this->part('where') ? $this->fetch_array() : null);

        return $this->children[$this->alias][$name];
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _set($name, $value){
        $key = self::_hash();

        if (is_array($value) or $value instanceof SQL){
            if ($class = self::_get($name)){
                $values = (is_array($value) ? $value : $value->values);

                foreach ($values as $k => $v)
                    $class[$k] = $v;

                if (!$this->id or !is_int($name))
                    return;

                $value = $class;
            }
        } elseif (is_int($value) or is_float($value)){
            self::$cache[$key.'[+-]'][$name][] = ($value - $this[$name]);
            self::$cache[$key][$name] = $value;

            if ($sum = array_sum(self::$cache[$key.'[+-]'][$name]))
                $value = self::raw(sprintf('(%s + %s)', $name, $sum));
            else
                return;
        } elseif ($value === null){
            if ($value === $this[$name])
                return;

            $value = self::raw('null');
            self::$cache[$key][$name] = null;
        }

        if ($name === null){
            $this->values[] = self::$cache[$key][] = $value;
        } elseif (self::_is_HABTM($name)){
            $this->values[$name] = self::$cache[$key][$name] = new ArrayObject($value);
        } else {
            if ($value === $this[$name])
                return;

            $this->values[$name] = self::$cache[$key][$name] = $value;
        }

        if ($relation = $this->relations['#']){
            $local = $relation['local'];
            $foreign = $relation['foreign'];

            if ($relation['type'] == 'has_one' and !$this->parent[$local['field']]){
                $class = clone $this->parent;
                $class->children = array();
                $class->values = array('#'.$local['field'] => $foreign['alias']);
                $this->parent->children[] = $class;
            }

            if ($relation['type'] == 'belongs_to' or $relation['type'] == 'has_many'){
                if ($this->parent->id)
                    $this->values[$foreign['field']] = $this->parent->id;
                else
                    $this->values['#'.$foreign['field']] = $local['alias'];
            }

            if ($relation['type'] == 'has_and_belongs_to_many' and !$this->id){
                $class = self::table($foreign['alias1']);
                $class->children = array();
                $class->values = array('#'.$foreign['field3'] => inflector::singular($foreign['alias2']));

                if ($this->parent->id)
                    $class->values[$foreign['field1']] = $this->parent->id;
                else
                    $class->values['#'.$foreign['field1']] = $local['alias'];

                $this->parent->children[] = $class;
            }
        }
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _is_HABTM($name){
        if (
            substr($name, -4) == '_ids' and
            ($_name = substr($name, 0, -4)) and
            ($relation = $this->relations[$_name]) and
            $relation['type'] == 'has_and_belongs_to_many'
        )
            return $_name;
    }

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

        if ($relation['type'] == 'has_and_belongs_to_many'){
            $ids = self::table($foreign['table1'], array($foreign['field1'] => $id));
            $ids->with($foreign['field3']);

            return $class->_where_between($name.'.'.$foreign['field2'], $ids->fetch_column());
        }

        if ($relation['type'] == 'has_one'){
            $class->id = $id;
        } elseif ($relation['type'] == 'belongs_to'){
            $class->id = -1;
            $class->limit(1);
        }

        return $class->where(array($foreign['field'] => $id));
    }

////////////////////////////////////////////////////////////////////////////////

}