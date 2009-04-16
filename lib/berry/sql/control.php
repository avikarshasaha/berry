<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_control extends SQL_build {

////////////////////////////////////////////////////////////////////////////////

    function save(){        if ($this->multisave){            foreach ($this->multisave as $class){                $result[] = $class->save();            }

            return $result;
        }
        if (!$this->values){            foreach ($this->build('HABTM') as $query)
                self::$sql->query($query);

            return 0;
        }
        if ($this->into){            $args = $this->values;
            array_unshift($args, $this->build('insert'));
            return call_user_method_array('query', self::$sql, $args);
        }
        if (!$this->where)
            $args = array_merge(array($this->values), array($this->values));
        else
            $args = array_merge(array($this->values), $this->placeholders);

        array_unshift($args, $this->build('save'));
        $result = call_user_method_array('query', self::$sql, $args);
        foreach ($this->build('HABTM') as $query)
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

        if (func_num_args() > 1 or !is_array($args[0])){
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
        array_unshift($args, $this->build('delete'));
        return call_user_method_array('query', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function create(){
        if (self::$sql->query($this->build('create')) !== null)
            return $this->alter();
    }

////////////////////////////////////////////////////////////////////////////////

    function alter(){        foreach ($this->build('alter') as $query)
            $result[] = self::$sql->query($query);

        return $result;
    }
////////////////////////////////////////////////////////////////////////////////

    function get($method = ''){        if (!$this){            $args = func_get_args();
            return call_user_method_array('query', self::$sql, $args);
        }

        $method = strtolower($method);
        $method = 'select'.(in_array($method, array('cell', 'col', 'row')) ? $method : '');

        if ($query = $this->build('getsub')){
            $args = $this->placeholders;
            array_unshift($args, $query);

            $this->where = array($this->table.'.'.$this->primary_key.' in (?a)');
            $this->placeholders = array(call_user_method_array('selectCol', self::$sql, $args));
        }

        $args = $this->placeholders;
        array_unshift($args, $this->build('get'));
        return call_user_method_array($method, self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function getArray(){        $this->select[] = $this->table.'.'.$this->primary_key.' as array_key_1';

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

    function getObject(){        $result = array();
        $array = ($this->id ? array($this->id => $this->getArray()) : $this->getArray());

        foreach ($array as $id => $row)
            foreach ($row as $k => $v)
                if (is_array($v[0])){                    foreach ($v as $i => $value)
                        $result[$id]->{$k}[$i] = (object)$value;
                } else {                    $result[$id]->$k = (is_array($v) ? (object)$v : $v);                }

        return ($this->id ? $result[$this->id] : $result);
    }

////////////////////////////////////////////////////////////////////////////////

    function getCell(){        if ($this)
            return $this->get('cell');

        $args = func_get_args();
        return call_user_method_array('selectCell', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function getCol(){
        if ($this)
            return $this->get('col');

        $args = func_get_args();
        return call_user_method_array('selectCol', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function getRow(){
        if ($this)
            return $this->get('row');

        $args = func_get_args();
        return call_user_method_array('selectRow', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////
}