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
        $query = self::$connection->query(
            $this->build('create'),
            inflector::tableize($this->alias),
            $this->primary_key, $this->primary_key
        );

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
        $args = array_merge(array($query, $this->table), $this->placeholders);
        return call_user_func_array(array(self::$connection, 'query'), $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function save(){
        $result = array();

        self::_check();
        self::_save($result);

        foreach ($result as $k => $v){
            $result = array_merge($result, $v);
            unset($result[$k]);
        }

        return (b::len($result) <= 1 ? reset($result) : $result);
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
                $v = array_unique($v);
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
            if ($this->into){
                array_unshift($values, $this->table, $this->into);
                $args = array_merge(array($this->build('insert')), $values);
            } else {
                $args = array($this->build('save'), $this->table);

                if (!$this->where){
                    $args[] = $values;
                    $args[] = $values;
                } else {
                    $args[] = $this->alias;
                    $args[] = $values;
                    $args = array_merge($args, $this->placeholders);
                }
            }

            $result[$this->alias][] = $last = call_user_func_array(array(self::$connection, 'query'), $args);
            $this->into = $this->values = array();

            if ($last === null)
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

        $args = $this->placeholders;
        array_unshift($args, $this->build('delete'), $from, $from);
        return call_user_func_array(array(self::$connection, 'query'), $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch(){
        if (!$this->select)
            $this->select[] = '*';

        $query = $this->build('select');
        $self = clone $this;

        if ($this->multiple and !$this->group_by and ($this->limit or $this->where)){
            $class = clone $this;
            $class->select = $class->group_by = array($this->primary_key);
            $class->join = array();

            $args = $this->placeholders;
            array_unshift($args, $class->build('select'));

            if (!$ids = call_user_func_array(array(self::$connection, 'selectCol'), $args))
                return array();

            sort($ids);
            $min = min($ids);
            $max = max($ids);
            $self->where = $self->placeholders = array();

            if (b::len($ids) == 1)                $self->where($this->primary_key.' = ?d', $ids);
            elseif ($ids == range($min, $max))
                $self->where($this->primary_key.' >= ?d', $min)->where($this->primary_key.' <= ?d', $max);            else
                $self->where($this->primary_key.' in (?a)', array($ids));

            $self->having = array();
            $self->limit = $self->offset = 0;
            $query = $self->build('select');
        }

        $args = array_merge($self->subquery_placeholders, $self->placeholders);
        array_unshift($args, $query);
        return call_user_func_array(array(self::$connection, 'select'), $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_array(){
        $class = clone $this;

        if (!$this->select)
            $class->select[] = '*';

        $class->select[] = $this->alias.'.'.$this->primary_key.' as array_key_1';

        if ($this->parent_key){
            $class->select[] = $this->alias.'.'.$this->parent_key.' as parent_key';

            foreach (arr::tree($class->fetch()) as $k => $v){
                unset($array[$k]);
                $array[$k][] = $v;
            }
        } else {
            $this->build('select');

            if ($multiple = array_unique($this->multiple)){
                foreach ($multiple as $k => $v){
                    $vars = self::vars(inflector::singular(end(explode('.', $v))));
                    $field = $v.'.'.($vars['primary_key'] ? $vars['primary_key'] : 'id');
                    $class->select[] = $field.' as array_key_'.($k + 2);
                }
            } else {
                $class->select[] = 'null as array_key_2';
            }

            $array = $class->fetch();
        }

        if (!$array)
            return array();

        $result = array();
        $multiple = ($multiple ? array_reverse($multiple) : array('Красивый-красивый мистер Биглз'));
        $len = b::len($multiple);

        foreach (arr::flat($array) as $k => $v){
            if ($v === null)
                continue;

            $k = str_replace('\.', '.', $k);
            $keys = explode('.', $k, ($len + 2));

            foreach ($multiple as $i => $w){
                $i = ($len - $i);
                $current = $keys[$len + 1];

                if (strpos($current, $w) === 0){
                    $w_len = b::len($w);
                    $keys[$len + 1] = substr($current, 0, $w_len).'.'.$keys[$i].substr($current, $w_len);
                }

                unset($keys[$i]);
            }

            $result[join('.', $keys)] = $v;
        }

        $result = self::_fetch_array(arr::assoc($result));
        $result = array_values($result);
        return ($this->id ? $result[0] : $result);
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

    function begin(){
        return self::$connection->transaction();
    }

////////////////////////////////////////////////////////////////////////////////

    // ???
    function commit(){
        $result = self::save();

        if (!self::$connection->commit())
            throw new SQL_Except;

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function rollback(){
        return self::$connection->rollback();
    }

////////////////////////////////////////////////////////////////////////////////

}