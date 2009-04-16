<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL extends SQL_control {
////////////////////////////////////////////////////////////////////////////////

    function __construct($id = 0, $class = ''){
        $this->_table = ($this->table ? $this->table : $class);
        $from = (($this->table and $this->table != $class) ? $this->table.' as ' : '');

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

    function select(){
            $this->select = array();

        $args = func_get_args();
        $this->select = array_merge($this->select, $args);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function from($table){
            return new $table;

        $args = func_get_args();
        $this->from = array_merge($this->from, $args);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function join(){
        foreach (func_get_args() as $arg){

            if (strpos($arg, '.')){
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
                $this->relations[$relation['foreign']['alias']] = $relation;


            if (in_array($relation['type'], array('has_one', 'belongs_to', 'has_many')))
                $this->join[] = str::format('
                    [%foreign.table] as `%foreign.alias` on (
                        `%foreign.alias`.%foreign.field = `%local.alias`.%local.field
                    )
                ', $relation);

            if ($relation['type'] == 'has_and_belongs_to_many'){
                    [%foreign.table1] as `%foreign.alias1` on (
                        `%foreign.alias1`.%foreign.field1 = `%local.alias`.%local.field
                    )
                ', $relation);
                $this->join[] = str::format('
                    [%foreign.table2] as `%foreign.alias2` on (
                        `%foreign.alias2`.%foreign.field2 = `%foreign.alias1`.%foreign.field3
                    )
                ', $relation);

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
        $this->limit = $limit;
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function page($page){
        $this->offset($page * $this->limit - $this->limit);
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function offset($offset){
        $this->offset = $offset;
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function groupBy(){
        $args = func_get_args();
        $this->group_by = array_merge($this->group_by, $args);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////
