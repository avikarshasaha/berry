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

    function save(){
        $result = array();
        self::_save($result);
        return (b::len($result) <= 1 ? reset($result) : $result);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _save(&$result){
        static $cache = array();

        if ($this->into){
            $into = $this->into;
            $values = $this->values;
            $belongs = array();

            foreach ($values as $k => $v){
                if (!check::is_valid($this->check, array_combine($into, $v)))
                    throw new Check_Except($this->check, $this->table);

                foreach ($v as $k2 => $v2)
                    if ($name = self::_is_HABTM($into[$k2])){
                        $belongs[$name][] = $v2;
                        unset($this->into[$k2], $values[$k][$k2]);
                    }
            }

            $args = array_merge(array(self::build('insert')), $values);
            $query = call_user_func_array(array(self::$sql, 'query'), $args);
            $this->into = $this->values = array();

            if (is_int($query)){
                if (($key = array_search($this->primary_key, $into)) !== false){
                    for ($i = 0, $c = b::len($values); $i < $c; $i++)
                        $result[] = $ids[] = $values[$i][$key];
                } else {
                    for ($i = $query, $c = (b::len($values) + $query); $i < $c; $i++)
                        $result[] = $ids[] = $i;
                }

                foreach ($belongs as $table => $values){
                    $foreign = $this->relations[$table]['foreign'];
                    $class[$table] = self::table($foreign['alias1']);

                    foreach ($values as $k => $v)
                        foreach ($v as $i)
                            $class[$table]->values(array(
                                $foreign['field1'] => $ids[$k],
                                $foreign['field3'] => $i
                            ));

                    $class[$table]->save();
                }
            } else {
                $result[] = $query;
            }
        } elseif ($this->values){
            if (!check::is_valid($this->check, $this->values))
                throw new Check_Except($this->check, $this->table);

            $values = $this->values;
            $current = $values[$this->primary_key];
            $belongs = array();

            foreach ($values as $k => $v)
                if ($name = self::_is_HABTM($k)){
                    $belongs[$name] = $v;
                    unset($values[$k]);
                } elseif ($v instanceof SQL){
                    $v->_save($result);

                    if (is_int($id = end($result)))
                        $values[$k] = (string)$id;
                    else
                        unset($values[$k]);
                }

            if (b::len($values) > 1 or !$current){
                $args = array_merge(array($values), (!$this->where ? array($values) : $this->placeholders));
                array_unshift($args, self::build('save'));
                $result[] = $id = call_user_func_array(array(self::$sql, 'query'), $args);
                $this->values = array($this->primary_key => (string)($current ? $current : $id));
            } else {
                $result[] = 0;
            }

            if ($belongs){
                $id = ($this->id ? $this->id : self::last_id());

                foreach ($belongs as $k => $v){
                    $foreign = $this->relations[$k]['foreign'];
                    $class = self::table($foreign['alias1']);

                    $class->where($foreign['field1'].' = ?', $id)->delete();
                    $class->where = array();

                    foreach ($v as $i)
                        $class->values(array(
                            $foreign['field1'] => $id,
                            $foreign['field3'] => $i
                        ));

                    $class->save();
                }
            }
        }

        foreach ($this->parallel as $class){
            $key = spl_object_hash($class);

            if (isset($cache[$key]))
                continue;

            $class->_save($result);
            $cache[$key] = true;
        }
    }

////////////////////////////////////////////////////////////////////////////////

    function delete(){
        $args = $this->placeholders;
        array_unshift($args, self::build('delete'));
        return call_user_func_array(array(self::$sql, 'query'), $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function create(){
        if (self::$sql->query(self::build('create')) === null)
            return 0;

        $result = self::alter();
        return ($result === 0 ? 1 : $result);
    }

////////////////////////////////////////////////////////////////////////////////

    function alter(){
        if (!$query = self::build('alter'))
            return 0;

        $args = $this->placeholders;
        array_unshift($args, $query);
        return call_user_func_array(array(self::$sql, 'query'), $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch(){
        if (!$this->select)
            $this->select[] = '*';

        $query = self::build('select');
        $self = clone $this;

        if ($this->multiple and !$this->group_by and ($this->limit or $this->where)){
            $class = clone $this;
            $class->select = $class->group_by = array($this->primary_key);
            $class->join = array();

            $args = $this->placeholders;
            array_unshift($args, $class->build('select'));

            if (!$ids = call_user_func_array(array(self::$sql, 'selectCol'), $args))
                return array();

            $self->where = array($this->primary_key.' in (?a)');
            $self->having = array();
            $self->limit = $this->offset = 0;
            $self->placeholders = array($ids);
            $query = $self->build('select');
        }

        $args = $self->placeholders;
        array_unshift($args, $query);
        return call_user_func_array(array(self::$sql, 'select'), $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_array(){
        $class = clone $this;

        if (!$this->select)
            $class->select[] = '*';

        $class->select[] = $this->table.'.'.$this->primary_key.' as array_key_1';

        if ($this->parent_key){
            $class->select[] = $this->table.'.'.$this->parent_key.' as parent_key';

            foreach (arr::tree(self::fetch()) as $k => $v){
                unset($array[$k]);
                $array[$k][] = $v;
            }
        } else {
            self::build('select');

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
        return (($result and $this->id) ? $result[$this->id] : array_values($result));
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
        return self::$sql->transaction();
    }

////////////////////////////////////////////////////////////////////////////////

    // ???
    function commit(){
        $result = self::save();

        if (!self::$sql->commit())
            throw new SQL_Except;

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function rollback(){
        return self::$sql->rollback();
    }

////////////////////////////////////////////////////////////////////////////////

}