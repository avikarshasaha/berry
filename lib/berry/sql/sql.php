<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL extends SQL_Control {////////////////////////////////////////////////////////////////////////////////

    function __construct($id = 0, $class = ''){        $class = strtolower($class ? $class : get_class($this));
        $this->alias = (substr($class, -4) == '_sql' ? substr($class, 0, -4) : $class);

        if (!$this->table){
            $this->table  = self::$connection['database'];
            $this->table .= '.'.self::$connection['prefix'];
            $this->table .= inflector::tableize($this->alias);
        } elseif (!strpos($this->table, '.')){
            $this->table = self::$connection['database'].'.'.$this->table;        }
        $this->table = trim($this->table, '.');
        $this->from[] = $this->table.' as '.$this->alias;
        $this->relations = self::deep_throat($this->alias);

        if ($id){            if (is_array($id))
                list($this->primary_key, $id) = array(key($id), reset($id));

            self::where($this->primary_key.' = ?', ($this->id = $id));
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
            if ($arg instanceof SQL_Raw){
                $this->select[] = '?';
                $this->placeholders[] = $arg;
            } elseif ($arg instanceof SQL or $arg instanceof SQL_Query){
                $this->select[] = $this->build('subquery', 'select', $arg);
            } elseif ($arg == '*'){                $this->select = array_merge($this->select, array_keys(self::schema($this->alias)));
            } elseif (substr($arg, -2) == '.*'){                $arg = substr($arg, 0, -2);

                foreach (array_keys(self::schema($arg)) as $field)
                    $this->select[] = $arg.'.'.$field.' as '.$arg.'.'.$field;
            } else {
                $this->select[] = $arg;
            }

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function from($table){        if (!$this instanceof self and $table)
            return self::table($table);
        foreach (func_get_args() as $arg)            if ($pos = stripos($arg, ' as ')){
                $this->from[] = self::table(trim(substr($arg, 0, $pos)))->table.substr($arg, $pos);
            } else {
                $this->from[] = self::table($arg)->table;            }

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function join(){        foreach (func_get_args() as $arg){            if ($arg instanceof SQL){                $this->join[] = $this->build('subquery', 'join', $arg);                continue;
            } elseif ($arg instanceof SQL_Query){                $this->join[] = $arg->build('subquery', 'join', $this);
                continue;            } elseif (is_array($arg)){                list($arg, $vars) = array($this->alias, $arg);                $this->relations = array_merge($this->relations, self::deep_throat(array($arg => $vars)));            }

            if ($pos = stripos($arg, ' as ')){
                $alias = trim(substr($arg, ($pos + 4)));
                $arg = trim(substr($arg, 0, $pos));

                $relation = $this->relations[strtolower($arg)];

                if ($relation['foreign']['alias2'])
                    $relation['foreign']['alias2'] = $alias;
                else
                    $relation['foreign']['alias'] = $alias;
            } else {                $relation = $this->relations[strtolower($arg)];
            }

            $this->join = array_merge($this->join, $this->build('join', $relation));

            if (substr($relation['type'], -4) == 'many')
                $this->multiple[] = ($relation['foreign']['alias2'] ? $relation['foreign']['alias2'] : $relation['foreign']['alias']);
        }

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function where(){
        $args = func_get_args();
        $this->where[] = join(' or ', (array)array_shift($args));

        foreach ($args as $arg){
            if ($arg instanceof SQL or $arg instanceof SQL_Query){
                $arg->alias = 'subquery_'.$arg->alias;
                $arg->from[0] = $arg->table.' as '.$arg->alias;

                $query = $arg->query($arg->build('select'))->build('select');
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
        foreach (func_get_args() as $arg)
            $this->having[] = (is_array($arg) ? join(' or ', $arg) : $arg);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function order_by(){
        foreach (func_get_args() as $arg)
            $this->order_by[] = ($arg[0] == '-' ? substr($arg, 1).' desc' : $arg);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function limit($limit){        $this->limit = (is_numeric($limit) ? $limit : self::$connection->quote($limit));
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function offset($offset){        if ($offset > 0)
            $this->offset = (is_numeric($offset) ? $offset : self::$connection->quote($offset));

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function page($page){        if ($page > 0)
            $this->offset($page * $this->limit - $this->limit);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    static function union(){        $class = new self;
        $class->table = $class->alias = '';

        foreach (func_get_args() as $arg)
            if ($arg instanceof SQL or $arg instanceof SQL_Query){
                if (!$arg->select)
                    $arg->select[] = '*';

                $class->union[] = $arg->build('select');
                $class->placeholders = array_merge($class->placeholders, $arg->placeholders);
            }

        return $class;
    }

////////////////////////////////////////////////////////////////////////////////

    function __call($method, $params){
        $method = strtolower($method);

        if (!$scope = $this->scope[$method])
            trigger_error(sprintf('Call to undefined method %s::%s()', get_class($this), $method), E_USER_ERROR);

        foreach ($scope as $k => $v)
            if (call_user_func_array(array($this, $k), array_merge((array)$v)))
                $this->placeholders = array_merge($this->placeholders, $params);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////
}