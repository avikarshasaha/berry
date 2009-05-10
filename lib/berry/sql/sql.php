<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL extends SQL_control {
////////////////////////////////////////////////////////////////////////////////

    function __construct($id = 0, $class = ''){        $class = strtolower($class ? $class : get_class($this));
        $this->_table = ($this->table ? $this->table : $class);
        $from = (($this->table and $this->table != $class) ? $this->table.' as ' : '');        $this->from($from.$class)->table = $class;

        foreach (array('has_one', 'belongs_to', 'has_many', 'has_and_belongs_to_many') as $has)
            foreach ($this->$has as $key => $table){
                $relation = self::relations($class, array($key => $table), $has);
                $alias = ($relation['foreign']['alias2'] ? $relation['foreign']['alias2'] : $relation['foreign']['alias']);
                $this->relations[$alias] = $relation;
            }

        if ($id)
            $this->where($class.'.'.$this->primary_key.' = ?', ($this->id = $id));
    }

////////////////////////////////////////////////////////////////////////////////

    function select(){        if ($this->select[0] == '*')
            $this->select = array();

        $args = func_get_args();
        $this->select = array_merge($this->select, $args);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function from($table){        if (!$this)
            return self::table($table);

        $args = func_get_args();
        $this->from = array_merge($this->from, $args);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function join(){        foreach (func_get_args() as $arg){            $arg = strtolower($arg);

            if (strpos($arg, '.')){                $args = explode('.', $arg);
                $table = array_pop($args);
                $class = array_pop($args);
                $vars = get_class_vars($class);

                if (!$vars['has_one'])
                    continue;

                if ($key = array_search($table, $vars['has_one']))
                    $table = $vars['has_one'][$key];
                else
                    $table = $vars['has_one'][$key = $table];

                $relation = self::relations($class, array($key => $table), 'has_one');
                $relation['foreign']['alias'] = $arg;
                $this->relations[$relation['foreign']['alias']] = $relation;            }
            $relation = $this->relations[$arg];
            $this->join = array_merge($this->join, $this->_buildJoin($relation));

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

    function orderBy(){
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

    function page($page){
        $this->offset(max(0, $page * $this->limit - $this->limit));
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function offset($offset){
        $this->offset = (int)$offset;
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function groupBy(){
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
}