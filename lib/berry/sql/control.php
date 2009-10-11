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

        if ($this->values){            if (!$this->where)
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

            foreach ($v as $i)
                $table->values(array(
                    $foreign['field1'] => $id,
                    $foreign['field3'] => $i
                ));

            $table->delete();
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

    function get(){
        if (!$this){
            $args = func_get_args();
            return call_user_method_array('select', self::$sql, $args);
        }

        $key = self::hash('get');

        if (array_key_exists($key, self::$cache))
            return self::$cache[$key];

        if (!$this->select)
            $this->select[] = '*';

        $query = self::build('get');

        if ($this->multiple and ($this->limit or $this->where)){            $class = clone $this;            $class->select = array($this->primary_key);
            $class->group_by = array($this->primary_key);

            $args = $this->placeholders;
            array_unshift($args, $class->build('get'));

            if (!$ids = call_user_method_array('selectCol', self::$sql, $args))
                return array();

            $this->where = array($this->primary_key.' in (?a)');
            $this->having = array();
            $this->limit = $this->offset = 0;
            $this->placeholders = array($ids);
            $query = self::build('get');
        }

        $args = $this->placeholders;
        array_unshift($args, $query);
        return self::$cache[$key] = call_user_method_array('select', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function as_array(){        if (!$this->select)
            $this->select[] = '*';
        $result = array();        $this->select[] = $this->table.'.'.$this->primary_key.' as array_key_1';

        if ($this->parent_key){            $this->select[] = $this->table.'.'.$this->parent_key.' as parent_key';

            foreach (arr::tree(self::get()) as $k => $v){
                unset($array[$k]);
                $array[$k][] = $v;
            }
        } else {            $this->select[] = 'null as array_key_2';
            $array = self::get();        }

        foreach ($array as $id => $tmp)
            foreach ($tmp as $i => $row)
                foreach ($row as $k => $v){
                    if (strpos($k, '.'))
                        list($t, $f) = explode('.', $k);
                    else
                        unset($t, $f);

                    if ($t and in_array($t, $this->multiple)){
                        if ($v !== null)
                            $result[$id][$t][$i][$f] = $v;
                    } elseif ($v !== null){
                        $result[$id][$k] = $v;
                    }
                }

        $result = arr::assoc($result);
        return ($this->id ? $result[$this->id] : $result);
    }

////////////////////////////////////////////////////////////////////////////////

    function as_object(){        $result = array();
        $array = ($this->id ? array($this->id => self::as_array()) : self::as_array());

        foreach ($array as $id => $row)
            foreach ($row as $k => $v)
                if (is_array($v[0])){                    foreach ($v as $i => $value)
                        $result[$id]->{$k}[$i] = (object)$value;
                } else {                    $result[$id]->$k = (is_array($v) ? (object)$v : $v);                }

        return ($this->id ? $result[$this->id] : $result);
    }

////////////////////////////////////////////////////////////////////////////////

    function cell(){        if (!$this){            $args = func_get_args();
            return call_user_method_array('selectCell', self::$sql, $args);
        }

        if (is_array($array = self::col()))
            return reset($array);

        return $array;
    }

////////////////////////////////////////////////////////////////////////////////

    function col(){
        if (!$this){
            $args = func_get_args();
            return call_user_method_array('selectCol', self::$sql, $args);
        }

        $array = self::get();
        self::_col($array);

        return $array;
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _col(&$v){        if (!is_array($cell = reset($v)))
            $v = $cell;
        else
            array_walk($v, array('self', '_col'));    }

////////////////////////////////////////////////////////////////////////////////

    function row(){
        if (!$this){
            $args = func_get_args();
            return call_user_method_array('selectRow', self::$sql, $args);
        }

        return reset(self::get());
    }

////////////////////////////////////////////////////////////////////////////////

    function count(){        $class = clone $this;

        $class->select = array('count(*)');
        $class->order_by = array();

        return $class->cell();
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