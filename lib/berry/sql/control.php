<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_control extends SQL_etc {
////////////////////////////////////////////////////////////////////////////////

    function save(){
        $result = array();

        foreach ($this->_save() as $v){
            if (is_array($v))
                $result[] = $v[0];
            else
                $result[] = $v;
        }

        $result = array_reverse($result);
        return (b::len($result) == 1 ? reset($result) : $result);    }

////////////////////////////////////////////////////////////////////////////////

    protected function _save($result = array()){        foreach (self::$cache as $value){            if (
                !is_object($value) or
                (($key = '_save'.spl_object_hash($value)) and isset(self::$cache[$key]))
            )
                continue;

            self::$cache[$key] = true;
            $result = array_merge($result, (array)$value->_save($result));        }
        if ($this->multisave){            foreach ($this->multisave as $class)                $result[] = $class->_save();

            return $result;
        }
        if (!$this->values){            foreach (self::build('HABTM') as $query)
                self::$sql->query($query);

            return $result;
        }
        if ($this->into){            $args = $this->values;
            array_unshift($args, self::build('insert'));
            return call_user_method_array('query', self::$sql, $args);
        }
        if (!$this->where)
            $args = array_merge(array($this->values), array($this->values));
        else
            $args = array_merge(array($this->values), $this->placeholders);

        array_unshift($args, self::build('save'));
        $result[] = call_user_method_array('query', self::$sql, $args);
        foreach (self::build('HABTM') as $query)
            self::$sql->query($query);

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function into(){        if (!$this->into)
            $this->into = func_get_args();

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function values(){        $args = func_get_args();

        if (!is_array($args[0])){
            $this->values[] = $args;
            return $this;
        }
        if (!$this->into)
            $this->into = array_keys($args[0]);

        $this->values[] = array_values($args[0]);
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function delete(){        $args = $this->placeholders;
        array_unshift($args, self::build('delete'));
        return call_user_method_array('query', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function create(){
        if (self::$sql->query(self::build('create')) !== null)
            return $this->alter();
    }

////////////////////////////////////////////////////////////////////////////////

    function alter(){        if ($query = self::build('alter'))            return (self::$sql->query($query) !== null);
    }
////////////////////////////////////////////////////////////////////////////////

    function get($method = ''){        if (!$this){            $args = func_get_args();
            return call_user_method_array('query', self::$sql, $args);
        }

        $method = strtolower($method);

        if (in_array($method, array('array', 'object')))
            return $this->{'as_'.$method}();
        elseif ($method == 'count')
            return $this->count();

        $method = 'select'.(in_array($method, array('cell', 'col', 'row')) ? $method : '');

        if ($query = self::build('subquery')){
            $args = $this->placeholders;
            array_unshift($args, $query);

            if (!$ids = call_user_method_array('selectCol', self::$sql, $args))
                return array();

            $this->where = array($this->table.'.'.$this->primary_key.' in (?a)');
            $this->placeholders = array($ids);
        }

        $args = $this->placeholders;
        array_unshift($args, self::build('get'));
        return call_user_method_array($method, self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function as_array(){        $this->select[] = $this->table.'.'.$this->primary_key.' as array_key_1';

        if ($this->parent_key){            $this->select[] = $this->table.'.'.$this->parent_key.' as parent_key';

            foreach (arr::tree($this->get()) as $k => $v){
                unset($array[$k]);
                $array[$k][] = $v;
            }
        } else {            $this->select[] = 'null as array_key_2';
            $array = $this->get();        }

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
        $array = ($this->id ? array($this->id => $this->as_array()) : $this->as_array());

        foreach ($array as $id => $row)
            foreach ($row as $k => $v)
                if (is_array($v[0])){                    foreach ($v as $i => $value)
                        $result[$id]->{$k}[$i] = (object)$value;
                } else {                    $result[$id]->$k = (is_array($v) ? (object)$v : $v);                }

        return ($this->id ? $result[$this->id] : $result);
    }

////////////////////////////////////////////////////////////////////////////////

    function cell(){        if ($this)
            return $this->get('cell');

        $args = func_get_args();
        return call_user_method_array('selectCell', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function col(){
        if ($this)
            return $this->get('col');

        $args = func_get_args();
        return call_user_method_array('selectCol', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function row(){
        if ($this)
            return $this->get('row');

        $args = func_get_args();
        return call_user_method_array('selectRow', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function __call($method, $params){        if (!$trigger = $this->trigger[$method])
            trigger_error(sprintf('Call to undefined method %s::%s()', get_class($this), $method), E_USER_ERROR);

        foreach ($trigger as $k => $v)
            if (call_user_method_array($k, $this, array_merge((array)$v)))
                $this->placeholders = array_merge($this->placeholders, $params);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////
}