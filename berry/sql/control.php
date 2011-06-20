<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
abstract class SQL_Control extends SQL_Vars implements Countable {

////////////////////////////////////////////////////////////////////////////////

    function create(){
        $query = self::query($this->build('create'))->exec();

        if ($query === null)
            return 0;

        $result = self::alter();
        return ($result === 0 ? 1 : $result);
    }

////////////////////////////////////////////////////////////////////////////////

    function alter(){
        if (!$query = $this->build('alter'))
            return 0;

        cache::remove('sql/schema/'.$this->table.'.php');
        return self::query($query)->exec();
    }

////////////////////////////////////////////////////////////////////////////////

    function save($full = false){
        $result = array();

        self::_check();
        self::_save($result);

        if (!array_key_exists($this->alias, $result))
            $result = array($this->alias => array(0)) + $result;

        if ($full)
            return $result;

        self::_fetch_col($result = $result[$this->alias]);
        return reset($result);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _check(){
        if ($this->into){
            foreach ($this->values as $k => $v)
                if (!check::is_valid($this->check, array_combine($this->into, $v)))
                    throw new Check_Except($this->check, $this->alias);
        } elseif (!check::is_valid($this->check, $this->values)){
            throw new Check_Except($this->check, $this->alias);
        }

        foreach ($this->parallel as $class)
            if ($class instanceof SQL)
                $class->_check();
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _save(&$result, $is_parallel = false){
        $values = $this->values;
        $belongs = array();

        foreach ($values as $k => $v)
            if (self::_is_HABTM($k)){
                $v = array_unique((array)$v);
                sort($v);
                unset($values[$k]);

                if (self::$cache[$this->alias][$k] != $v)
                    $belongs[substr($k, 0, -4)] = $v;
            } elseif ($k[0] == '#' and $result[$v]){
                $first = array_shift($result[$v]);
                $values[substr($k, 1)] = $first;
                $result[$v][] = $first;
                unset($values[$k]);

                if (
                    !$this->where and !$this->relations['#'] and
                    $result[$this->alias] and $result[$this->alias][0]
                )
                    $this->where($this->primary_key.' = ?d', $result[$this->alias][0]);
            }

        if ($values){
            if (!$this->where){
                if (!$into = $this->into){
                    $into = array_keys($values);
                    $values = array(array_values($values));
                }

                $args = array_merge(array($into), $values, $this->placeholders);
                $count = self::query($this->build('insert'), $args)->exec();

                if ($count){
                    $id = self::$connection['link']->lastInsertId();
                    $result[$this->alias][] = $id;

                    if ($count > 1)
                        foreach (range(($id + 1), ($id + $count - 1)) as $k => $v)
                            $result[$this->alias][] = (string)$v;

                    $key = self::hash('_get');
                    $this->id = self::$cache[$key][$this->primary_key] = $id;
                    $this->where($this->primary_key.' = ?d', $id);
                } else {
                    $result[$this->alias][] = $count;
                }
            } else {
                $args = array_merge(array($values), $this->placeholders);
                $result[$this->alias][] = self::query($this->build('update'), $args)->exec();
            }

            $this->into = $this->values = array();

            if (end($result[$this->alias]) === null)
                return;
        }

        foreach ($belongs as $k => $v){
            $local = $this->relations[$k]['local'];
            $foreign = $this->relations[$k]['foreign'];

            $class = self::table($foreign['alias1']);
            $id = ($this->id ? $this->id : $result[$local['alias']][0]);

            $class->where($foreign['field1'].' = ?', $id)->delete();
            $class->where = array();

            foreach ($v as $i)
                $class->values(array(
                    $foreign['field1'] => $id,
                    $foreign['field3'] => $i
                ));

            $this->parallel[] = $class;
        }

        foreach ($this->parallel as $class)
            if ($class instanceof SQL)
                $class->_save($result, true);
    }

////////////////////////////////////////////////////////////////////////////////

    function delete(){
        $from = $this->from;

        foreach ($this->from as $k => $v)
            if ($pos = stripos($v, ' as '))
                $from[$k] = trim(substr($v, 0, $pos));

        return self::query($this->build('delete'))->exec();

        $args = $this->placeholders;
        array_unshift($args, $this->build('delete'), $from, ($this->limit ? self::SKIP : $from));
        return call_user_func_array(array(self::$connection, 'query'), $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch(){
        $self = clone $this;

        if (!$self->select)
            $self->select('*');

        $query = $self->build('select');

        if ($self->multiple and !$self->group_by and ($self->limit or $self->where)){
            $class = clone $self;
            $class->select = $class->group_by = array($self->primary_key);
            $class->join = array();

            if (!$ids = $class->query($class->build('select'))->fetch_col())
                return array();

            $self->where = $self->placeholders = array();
            $self->having = array();
            $self->limit = $self->offset = 0;

            $self->where_between($self->primary_key, $ids);
            $query = $self->build('select');
        }

        return $self->query($query)->fetch();
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_array(){
        $class = clone $this;

        if (!$class->select)
            $class->select('*');

        $class->build('select');

        if ($multiple = array_unique($class->multiple)){
            $class->select[] = $class->primary_key.' as __';

            foreach ($multiple as $k => $v){
                $vars = self::vars(inflector::singular(end(explode('.', $v))));
                $field = $v.'.'.($vars['primary_key'] ? $vars['primary_key'] : 'id');
                $class->select[] = $field.' as __'.$v;
            }
        }

        if (!$array = $class->fetch())
            return array();

        if (!$multiple){
            $result = arr::assoc($array);
            return ($class->id ? $result[0] : $result);
        }

        $result = $repl = $values = array();
        $func = create_function('$a, $b', 'return (substr_count($a, ".") < substr_count($b, "."));');

        foreach ($array as $row){
            $i = $row['__'];
            unset($row['__']);

            if (!isset($result[$i]))
                $result[$i] = array();

            foreach ($row as $k => $v)
                if (substr($k, 0, 2) == '__'){
                    unset($row[$k]);

                    if ($v !== null){
                        $key = substr($k, 2);
                        $repl[$key] = $key.'.'.$v;
                    }
                } elseif ($v === null){
                    unset($row[$k]);
                }

            uasort($repl, $func);

            foreach ($row as $k => $v){
                $key = str_replace(array_keys($repl), array_values($repl), $k);
                $row[$key] = $v;

                if (
                    ($pos = strrpos($k, '.')) and
                    isset($repl[$key = substr($k, 0, $pos)])
                )
                    unset($row[$k]);
            }

            $result[$i] += $row;
        }

        $result = self::_fetch_array(arr::assoc($result));
        $result = array_values($result);
        return ($class->id ? $result[0] : $result);
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _fetch_array($array){
        $result = array();

        foreach ($array as $k => $v){
            if (is_array($v))
                $v = self::_fetch_array(is_int(key($v)) ? array_values($v) : $v);

            $result[$k] = $v;
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_cell(){
        if (is_array($array = self::fetch_col()))
            return reset($array);

        return $array;
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_col(){
        $array = self::fetch();
        self::_fetch_col($array);

        return ($array ? $array : array());
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _fetch_col(&$v){
        if (!is_array($cell = reset($v)))
            $v = $cell;
        else
            array_walk($v, array('self', '_fetch_col'));
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_row(){
        if ($array = reset(self::fetch()))
            return $array;

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    function count(){
        $class = clone $this;

        $class->select = array('count(*)');
        $class->join = $class->order_by = array();
        $class->group_by = ($class->group_by ? $class->group_by : array($this->primary_key));
        $class->limit = $class->offset = 0;

        return array_sum($class->fetch_col());
    }

////////////////////////////////////////////////////////////////////////////////

    function exists(){
        if (!$this->where)
            return;

        $class = self::table($this->alias);
        $class->select[] = 1;
        $class->where = $this->where;
        $class->having = $this->having;
        $class->limit = 1;
        $class->placeholders = $this->placeholders;

        return (bool)$class->fetch_cell();
    }

////////////////////////////////////////////////////////////////////////////////

    function begin(){
        return self::$connection['link']->beginTransaction();
    }

////////////////////////////////////////////////////////////////////////////////

    function commit(){
        if (!self::$connection['link']->commit())
            throw new SQL_Except;
    }

////////////////////////////////////////////////////////////////////////////////

    function rollback(){
        return self::$connection['link']->rollback();
    }

////////////////////////////////////////////////////////////////////////////////

    function find($where = null, $placeholders = array()){
        if (!$this or !$this instanceof SQL){
            $class = get_called_class();
            $class = new $class;
            return $class->find($where, $placeholders);
        }

        if (is_numeric($where))
            $this->where($this->primary_key.' = ?', $where);

        if (is_string($where)){
            array_unshift($placeholders, $where);
            call_user_func_array(array($this, 'where'), $placeholders);
        }

        if (is_array($where))
            foreach ($where as $k => $v){
                if (is_int($k) and is_array($v)){
                    $keys = array_keys($v);
                    $args = array_values($v);

                    foreach ($keys as $i => $key)
                        if (!strpos($key, '?'))
                            $keys[$i] .= ' = ?';

                    array_unshift($args, $keys);
                    call_user_func_array(array($this, 'where'), $args);
                } elseif (is_int($k)){
                    $this->where($v);
                } else {
                    if (!strpos($k, '?'))
                        $k .= ' = ?';

                    $this->where($k, $v);
                }
            }

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function find_one($where = null, $placeholders = array()){
        if (!$this or !$this instanceof SQL){
            $class = get_called_class();
            $class = new $class;
        } else {
            $class = clone $this;
        }

        $result = $class->limit(1)->find_all($where, $placeholders);
        return ($result ? $result[0] : $result);
    }

////////////////////////////////////////////////////////////////////////////////

    function find_all($where = null, $placeholders = array()){
        if (!$this or !$this instanceof SQL){
            $class = get_called_class();
            $class = new $class;
        } else {
            $class = clone $this;
        }

        return $class->find($where, $placeholders)->fetch_array();
    }

////////////////////////////////////////////////////////////////////////////////

    function with($select){
        foreach ((array)$select as $v)
            $this->select($v);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function sort($order_by = null){
        if (!$order_by)
            $order_by = $this->primary_key;

        foreach((array)$order_by as $v)
            $this->order_by($v);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

}