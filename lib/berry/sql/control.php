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

    function save(){

            return $result;
        }

                self::$sql->query($query);

            return 0;
        }

            array_unshift($args, $this->build('insert'));
            return call_user_method_array('query', self::$sql, $args);
        }

            $args = array_merge(array($this->values), array($this->values));
        else
            $args = array_merge(array($this->values), $this->placeholders);

        array_unshift($args, $this->build('save'));
        $result = call_user_method_array('query', self::$sql, $args);

            self::$sql->query($query);

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function into(){
            $this->into = func_get_args();

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function values(){

        if (func_num_args() > 1 or !is_array($args[0])){
            $this->values[] = $args;
            return $this;
        }

            $this->into = array_keys($args[0]);

        $this->values[] = array_values($args[0]);
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function delete(){
        array_unshift($args, $this->build('delete'));
        return call_user_method_array('query', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function create(){
        if (self::$sql->query($this->build('create')) !== null)
            return $this->alter();
    }

////////////////////////////////////////////////////////////////////////////////

    function alter(){
            $result[] = self::$sql->query($query);

        return $result;
    }
////////////////////////////////////////////////////////////////////////////////

    function get($method = ''){
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

    function getArray(){

        if ($this->parent_key){

            foreach (arr::tree($this->get()) as $k => $v){
                unset($array[$k]);
                $array[$k][] = $v;
            }
        } else {
            $array = $this->get();

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

    function getObject(){
        $array = ($this->id ? array($this->id => $this->getArray()) : $this->getArray());

        foreach ($array as $id => $row)
            foreach ($row as $k => $v)
                if (is_array($v[0])){
                        $result[$id]->{$k}[$i] = (object)$value;
                } else {

        return ($this->id ? $result[$this->id] : $result);
    }

////////////////////////////////////////////////////////////////////////////////

    function getCell(){
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
