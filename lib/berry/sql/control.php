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

        foreach (self::_save() as $v){
            if (is_array($v))
                $result[] = $v[0];
            else
                $result[] = $v;
        }

        $result = array_reverse($result);
        return (b::len($result) == 1 ? reset($result) : $result);    }

////////////////////////////////////////////////////////////////////////////////

    protected function _save($result = array()){        foreach (self::$cache as $key => $value){            if (
                !$value instanceof SQL or
                (($key = '_save'.spl_object_hash($value)) and isset(self::$cache[$key]))
            )
                continue;

            self::$cache[$key] = true;
            $result = array_merge($result, (array)$value->_save($result));        }
        if ($this->multisave){            foreach ($this->multisave as $class)                $result[] = $class->_save();

            return $result;
        }
        if ($this->into){            $args = $this->values;
            array_unshift($args, self::build('insert'));
            $result[] = call_user_method_array('query', self::$sql, $args);

            return $result;
        }

        if ($this->values){
            if (!check::is_valid($this->checker, $this->values))
                throw new Check_Except($this->checker, $this->table);
            if (!$this->where)
                $args = array_merge(array($this->values), array($this->values));
            else
                $args = array_merge(array($this->values), $this->placeholders);

            array_unshift($args, self::build('save'));
            $result[] = call_user_method_array('query', self::$sql, $args);
        }
        $id = ($this->id ? $this->id : self::last_id());

        foreach ($this->joinvalues as $k => $v){
            $foreign = $this->relations[$k]['foreign'];

            $table = self::table($foreign['table1'])->where($foreign['field1'].' = ?', $id);
            $table->delete();

            $table = self::table($foreign['table1']);

            foreach ($v as $i)
                $table->values(array(
                    $foreign['field1'] => $id,
                    $foreign['field3'] => $i
                ));

            $table->save();
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function delete(){        $args = $this->placeholders;
        array_unshift($args, self::build('delete'));
        return call_user_method_array('query', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function create(){
        if (self::$sql->query(self::build('create')) !== null)
            return self::alter();
    }

////////////////////////////////////////////////////////////////////////////////

    function alter(){        if ($query = self::build('alter'))            return (self::$sql->query($query) !== null);
    }
////////////////////////////////////////////////////////////////////////////////

    function fetch(){
        $key = self::hash('fetch');

        if (array_key_exists($key, self::$cache))
            return self::$cache[$key];

        if (!$this->select)
            $this->select[] = '*';

        $query = self::build('select');

        if ($this->multiple and ($this->limit or $this->where)){            $class = clone $this;
            $class->select = $class->group_by = array($this->primary_key);
            $args = $this->placeholders;
            array_unshift($args, $class->build('select'));

            if (!$ids = call_user_method_array('selectCol', self::$sql, $args))
                return array();

            $this->where = array($this->primary_key.' in (?a)');
            $this->having = array();
            $this->limit = $this->offset = 0;
            $this->placeholders = array($ids);
            $query = self::build('select');
        }

        $args = $this->placeholders;
        array_unshift($args, $query);
        return self::$cache[$key] = call_user_method_array('select', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_array(){        if (!$this->select)            $this->select[] = '*';

        $this->select[] = $this->table.'.'.$this->primary_key.' as array_key_1';

        if ($this->parent_key){            $this->select[] = $this->table.'.'.$this->parent_key.' as parent_key';

            foreach (arr::tree(self::fetch()) as $k => $v){
                unset($array[$k]);
                $array[$k][] = $v;
            }
        } else {            self::build('select');

            if ($multiple = array_unique($this->multiple)){                foreach ($multiple as $k => $v){                    $vars = get_class_vars(inflector::singular(end(explode('.', $v))));
                    $field = $v.'.'.($vars['primary_key'] ? $vars['primary_key'] : 'id');
                    $this->select[] = $field.' as array_key_'.($k + 2);
                }
            } else {
                $this->select[] = 'null as array_key_2';
            }

            $array = self::fetch();        }

        if (!$array)
            return array();

        $result = array();
        $multiple = ($multiple ? array_reverse($multiple) : array('Красивый-красивый мистер Биглз'));
        $len = b::len($multiple);

        foreach (arr::flat($array) as $k => $v){            $k = str_replace('\.', '.', $k);
            $keys = explode('.', $k, ($len + 2));

            foreach ($multiple as $i => $w){                $i = ($len - $i);
                $current = $keys[$len + 1];
                if (strpos($current, $w) === 0){                    $w_len = b::len($w);                    $keys[$len + 1] = substr($current, 0, $w_len).'.'.$keys[$i].substr($current, $w_len);
                }

                unset($keys[$i]);
            }
            $result[join('.', $keys)] = $v;        }

        $result = self::_fetch_array(arr::assoc($result));
        return (($result and $this->id) ? $result[$this->id] : $result);
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _fetch_array($array){        $result = array();

        foreach ($array as $k => $v){            if (is_array($v)){                $v = self::_fetch_array(is_int(key($v)) ? array_values($v) : $v);            }
            $result[$k] = $v;        }

        return $result;    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_cell(){        if (is_array($array = self::fetch_col()))
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

    protected static function _fetch_col(&$v){        if (!is_array($cell = reset($v)))
            $v = $cell;
        else
            array_walk($v, array('self', '_fetch_col'));    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_row(){
        if ($array = reset(self::fetch()))
            return $array;

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    function count(){        $class = clone $this;

        $class->select = array('count(*)');
        $class->order_by = array();

        return $class->fetch_cell();
    }

////////////////////////////////////////////////////////////////////////////////

    function begin(){        return self::$sql->transaction();    }

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