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

            self::where($this->primary_key.' = ?d', ($this->id = $id));
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

        if ($this->where){            foreach ($args[0] as $k => $v)
                $this[$k] = $v;

            return $this;        }

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
            if ($arg instanceof SQL or $arg instanceof SQL_Query)
                $this->select[] = $arg->build('subquery_select', $this);
            else
                $this->select[] = $arg;

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function from($table){        foreach (func_get_args() as $arg)            if (!$this->table){                $table = inflector::tableize($arg);                $this->from[] = $table.($arg == $table ? '' : ' as '.$arg);
            } else {                $this->from[] = $arg;            }

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function join(){        foreach (func_get_args() as $arg){            if ($arg instanceof SQL or $arg instanceof SQL_Query){                $this->join[] = $arg->build('subquery_join', $this);                continue;            } elseif (is_array($arg)){                list($arg, $vars) = array($this->table, $arg);                $this->relations = array_merge($this->relations, self::deep_throat(array($arg => $vars)));            }
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
        $key = (b::len($this->where) - 1);

        foreach ($args as $arg){
            if ($arg instanceof SQL or $arg instanceof SQL_Query){
                self::$sql->_placeholderArgs = array_reverse($arg->placeholders);

                $class = clone $this;
                $query = self::$sql->_toBerry($arg->build('subquery_select', $class));
                $query = self::$sql->_expandPlaceholdersFlow($query);

                if ($arg instanceof SQL)
                    $query = substr($query, 0, strrpos($query, ')')).')';

                $this->placeholders[] = self::raw($query);
            } else {
                $this->placeholders[] = $arg;
            }
        }

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