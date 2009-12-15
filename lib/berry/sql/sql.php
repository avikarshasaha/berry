<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL extends SQL_Control {
////////////////////////////////////////////////////////////////////////////////

    function __construct($id = 0, $class = ''){        $class = strtolower($class ? $class : get_class($this));
        $this->_table = ($this->table ? $this->table : inflector::tableize($class));
        $from = (($this->table and $this->table != $class) ? $this->table.' as ' : '');
        self::from($from.$class)->table = $class;
        $this->relations = self::deep_throat($class);

        if ($id){            if (is_array($id))
                list($this->primary_key, $id) = array(key($id), reset($id));

            self::where($class.'.'.$this->primary_key.' = ?', ($this->id = $id));
        }
    }

////////////////////////////////////////////////////////////////////////////////

    function into(){
        if (!$this->into)
            $this->into = func_get_args();

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function values(){
        $args = func_get_args();

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

    function select(){        foreach (func_get_args() as $arg)
            if (is_object($arg))
                $this->select[] = $arg->build('select_in_select', $this);
            else
                $this->select[] = $arg;

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function from($table){        if (!$this)
            return self::table($table);

        foreach (func_get_args() as $arg)            if (!$this->table){                $table = inflector::tableize($arg);                $this->from[] = $table.($arg == $table ? '' : ' as '.$arg);
            } else {                $this->from[] = $arg;            }

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function join(){        foreach (func_get_args() as $arg){            if (is_object($arg)){                $this->join[] = $arg->build('select_in_join', $this);                continue;            }
            $relation = $this->relations[strtolower($arg)];
            $this->join = array_merge($this->join, self::build('join', $relation));

            if (substr($relation['type'], -4) == 'many')
                $this->multiple[] = ($relation['foreign']['alias2'] ? $relation['foreign']['alias2'] : $relation['foreign']['alias']);
        }

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function where(){
        $args = func_get_args();

        $this->where[] = array_shift($args);
        $this->placeholders = array_merge($this->placeholders, $args);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function group_by(){
        $args = func_get_args();
        $this->group_by = array_merge($this->group_by, $args);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function having(){
        $args = func_get_args();
        $this->having = array_merge($this->having, $args);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function order_by(){
        foreach (func_get_args() as $arg)
            $this->order_by[] = ($arg[0] == '-' ? substr($arg, 1).' desc' : $arg);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function limit($limit){
        $this->limit = max(0, $limit);
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function offset($offset){
        $this->offset = (int)$offset;
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function page($page){
        $this->offset(max(0, $page * $this->limit - $this->limit));
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function __call($method, $params){
        $method = strtolower($method);

        if (substr($method, 0, 3) == 'by_' and $params){
            $method = substr($method, 3);

            if (!strpos($method, '_')){
                $select[] = $method;
            } else {                $pos = strrpos($method, '_');
                $select[] = str_replace('_', '.', substr($method, 0, $pos));
                $select[] = substr($method, ($pos + 1));
            }

            foreach ($params as $param)
                self::where(join('.', $select).' = ?'.(is_array($param) ? 'a' : ''), $param);

            return $this;
        }

        return parent::__call($method, $params);
    }

////////////////////////////////////////////////////////////////////////////////
}