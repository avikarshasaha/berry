<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_vars implements ArrayAccess {    const SKIP = 7.2e83;

    protected $id = array();
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

    protected static $sql;
    protected static $cache = array();

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

        if (!array_key_exists($id, self::$cache)){
            $class = $this->table($this->table);

            foreach ($this->schema() as $field => $schema)
                if (!in_array(substr($schema['type'], -4), array('text', 'blob')))
                    $class->select($this->table.'.'.$field);

            foreach (array_keys($this->where) as $k)
                $class->where($this->where[$k], $this->placeholders[$k]);

            self::$cache[$id] = $class->getRow();
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
                    'select ?# from ?_ where ?# = ?d',
                    $foreign['field3'], $foreign['table1'], $foreign['field1'], $fid
                );
            } else {
                self::$cache[$id][$name] = array();
            }

        return self::$cache[$id][$name];
    }

////////////////////////////////////////////////////////////////////////////////

    function __set($name, $value){        is_array($value) and sort($value);

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
            if (is_int($value) and ($value = ($value - $this->__get($name))))
                $value = $this->raw('`'.$name.'`'.($value >= 0 ? ' + ' : ' ').$value);

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
        if ($class = $this->_setMultiSave($offset))
            return $class;

        return $this->__get($offset);
    }

////////////////////////////////////////////////////////////////////////////////

    function offsetSet($offset, $value){
        if (!is_array($value) or (!$class = $this->_setMultiSave($offset)))
            return $this->__set($offset, $value);

        foreach ($value as $k => $v)
            $class->$k = $v;

        return $value;
    }

////////////////////////////////////////////////////////////////////////////////

    private function _setMultiSave($offset){
        if (is_null($offset))
            return $this->multisave[] = $this->table($this->table);

        if (is_int($offset)){
            if (!isset($this->multisave[$offset]))
                $this->multisave[$offset] = $this->table($this->table, $offset);

            return $this->multisave[$offset];
        }
    }

////////////////////////////////////////////////////////////////////////////////
}