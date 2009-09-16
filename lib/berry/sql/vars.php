<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
abstract class SQL_vars extends SQL_build implements Countable, Iterator, ArrayAccess {    const SKIP = 7.2e83;

    protected $id;
    protected $table;
    protected $_table;

    protected $primary_key = 'id';
    protected $parent_key;

    protected $has_one = array();
    protected $belongs_to = array();
    protected $has_many = array();
    protected $has_and_belongs_to_many = array();

    protected $select = array('*');
    protected $from = array();
    protected $join = array();

    protected $into = array();
    protected $values = array();
    protected $joinvalues = array();

    protected $where = array();
    protected $group_by = array();
    protected $having = array();
    protected $order_by = array();
    protected $limit = 0;
    protected $offset = 0;

    protected $multiple = array();
    protected $relations = array();
    protected $placeholders = array();
    protected $multisave = array();
    protected $iterator = 0;

    protected static $sql;
    protected static $cache = array();

////////////////////////////////////////////////////////////////////////////////

    public function count(){        static $cache = array();

        $id = spl_object_hash($this);

        if (!isset($cache[$id])){            $args = $this->placeholders;
            array_unshift($args, self::build('count'));
            $cache[$id] = call_user_method_array('selectCell', self::$sql, $args);
        }

        return $cache[$id];
    }

////////////////////////////////////////////////////////////////////////////////

    function __isset($name){        return ($this->__get($name) !== null);    }

////////////////////////////////////////////////////////////////////////////////

    function __unset($name){        if (substr($name, -4) == '_ids' and ($rname = substr($name, 0, -4)))
            unset($this->joinvalues[$rname]);
        else
            unset($this->values[$name]);
    }

////////////////////////////////////////////////////////////////////////////////

    function __get($name){
        if (!$this->where)
            return;

        $id = '__get'.spl_object_hash($this);

        if ($this->relations[$name]){
            if (!array_key_exists($id.$name, self::$cache))
                return self::$cache[$id.$name] = $this->_object($name);

            return self::$cache[$id.$name];
        }

        if (!array_key_exists($id, self::$cache)){            $class = clone $this;

            /*foreach ($this->schema() as $field => $schema)
                if (!in_array(substr($schema['type'], -4), array('text', 'blob')))
                    $class->select($class->table.'.'.$field);*/

            self::$cache[$id] = $class->select($class->table.'.*')->as_array();
        }

        if (
            substr($name, -4) == '_ids' and
            ($rname = substr($name, 0, -4)) and
            ($relation = $this->relations[$rname]) and
            $relation['type'] == 'has_and_belongs_to_many' and
            array_key_exists($id, self::$cache) and
            !array_key_exists($name, self::$cache[$id])
        )
            if ($fid = self::$cache[$id][$relation['local']['field']]){
                $foreign = $relation['foreign'];
                self::$cache[$id][$name] = self::$sql->selectCol(
                    self::build('HABTM_IDs'),
                    $foreign['field3'], $foreign['table1'], $foreign['field1'], $fid
                );
            } else {
                self::$cache[$id][$name] = array();
            }

        return self::$cache[$id][$name];
    }

////////////////////////////////////////////////////////////////////////////////

    function __set($name, $value){        if (is_array($value)){            $func = create_function('$a', 'return !is_int($a);');            $array = array_map($func, array_keys($value));

            if (in_array(true, $array))                asort($value);
            else
                sort($value);
        }

        if ($this->where and ($value == $this->__get($name) or !$this->__get($this->primary_key)))
            return $value;

        if (
            substr($name, -4) == '_ids' and
            ($rname = substr($name, 0, -4)) and
            ($relation = $this->relations[$rname]) and
            $relation['type'] == 'has_and_belongs_to_many'
        ){
            $this->joinvalues[$rname] = $value;
        } else {
            if (is_int($value)){                $value -= $this->__get($name);
                $value = $this->raw('`'.$name.'`'.($value >= 0 ? ' + ' : ' ').$value);
            }

            $this->values[$name] = $value;
        }

        return $this->$name = $value;
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetExists($offset){
        return $this->__isset($offset);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetUnset($offset){
        $this->__unset($offset);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetGet($offset){
        if ($class = $this->_multisave($offset))
            return $class;

        return $this->__get($offset);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetSet($offset, $value){
        if (!is_array($value) or (!$class = $this->_multisave($offset)))
            return $this->__set($offset, $value);

        foreach ($value as $k => $v)
            $class->$k = $v;

        return $value;
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
        return ($this->iterator < $this->count());
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _multisave($offset){
        if (is_null($offset))
            return $this->multisave[] = $this->table($this->table);

        if (is_int($offset)){
            if (!isset($this->multisave[$offset]))
                $this->multisave[$offset] = $this->table($this->table, $offset);

            return $this->multisave[$offset];
        }
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _object($name){        $relation = $this->relations[$name];        $local = $relation['local'];
        $foreign = $relation['foreign'];

        if ($relation['type'] == 'has_one')
            return $this->table($name, $this->__get($relation['local']['field']));

        if ($relation['type'] == 'belongs_to')
            return $this->table($name)->limit(1)->where(
                $foreign['alias'].'.'.$foreign['field'].' = ?',
                $this->__get($local['field'])
            );

        if ($relation['type'] == 'has_many')
            return $this->table($name)->where(
                $foreign['alias'].'.'.$foreign['field'].' = ?',
                $this->__get($local['field'])
            );

        return $this->table($name)->where(
            $foreign['alias2'].'.'.$foreign['field2'].' in (?a)',
            self::$sql->selectCol(
                self::build('HABTM_IDs'),
                $foreign['field3'], $foreign['table1'], $foreign['field1'], $this->__get($local['field'])
            )
        );
    }

////////////////////////////////////////////////////////////////////////////////
}