<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_build {
////////////////////////////////////////////////////////////////////////////////

    protected function _append_join($v){        if ($pos = strrpos($v, '.')){
            $table = substr($v, 0, $pos);

            if ($table != $this->table and strpos($table, '`') === false)
                $this->join($table);
        }
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _prepare_fields($v){        self::_append_join($v);

        if (strpos($v, '`') === false){            if (!strpos($v, '.') and strpos($v, '(') === false and !stripos($v, ' as '))
                $v = $this->table.'.'.$v;

            $v = preg_replace('/([\w\.]+)\.(\w+)/i', '`\\1`.\\2', $v);
        }

        return $v;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _prepare_select_all($v){
        static $cache = array();

        $vars = get_class_vars(($pos = strrpos($v, '.')) ? substr($v, ($pos + 1)) : $v);
        $key = ($vars['table'] ? $vars['table'] : inflector::tableize($v));

        if (!$cache[$key]){            self::_append_join($v);
            $cache[$key] = array_keys($this->schema($key));
        }

        if (!$cache['#'][$v])
            foreach ($cache[$key] as $k)
                $cache['#'][$v][] = '`'.$v.'`.'.$k.' as `'.$v.'.'.$k.'`';

        return $cache['#'][$v];
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _prepare_bulid(){
        foreach ($this->select as $v){
            self::_append_join($v);

            $if = (
                !stripos($v, ' as ') and strpos($v, '`') === false and
                strtolower(substr($v, 0, (b::len($this->table) + 1))) != strtolower($this->table.'.')
            );

            $v = self::_prepare_fields($v);

            if ($v == '*'){
                $v = '`'.$this->table.'`.*';
            } elseif (strpos($v, '*') and strpos($v, '`') === false){
                $tmp = preg_replace('/([\w\.]+)\.\*/', '\\1', $v);

                if (strtolower($tmp) == strtolower($this->table))
                    $v = '`'.$tmp.'`.*';
                elseif ($this->relations[$tmp])
                    $v = join(', ', self::_prepare_select_all($tmp));
            } elseif ($if){
                $v = preg_replace('/([\w\.]+)\.(\w+)/', '`\\1`.\\2 as `\\1.\\2`', $v);
            } else {
                $v = preg_replace('/(?!`)([\w\.]+)\.(\w+)(?!`)/i', '`\\1`.\\2', $v);
                $v = preg_replace('/``([\w\.]+)`\./', '`\\1.', $v);
            }

            $select[] = $v;
        }

        foreach ($this->from as $v)
            if (strpos($v, '[') !== false){
                $from[] = $v;
            } else {
                if ($pos = stripos($v, ' as '))
                    $from[] = '['.(trim(substr($v, 0, $pos))).']'.substr($v, $pos, 4).'`'.substr($v, ($pos + 4)).'`';
                else
                    $from[] = '['.$v.']';
            }

        foreach (array('where', 'group_by', 'having', 'order_by') as $v)
            $this->$v = array_map(array('self', '_prepare_fields'), $this->$v);

        $this->select = $select;
        $this->from = $from;
        $this->join = array_unique($this->join);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_get(){        self::_prepare_bulid();

        $query[] = 'select '.join(', ', $this->select);
        $query[] = 'from '.join(', ', $this->from);
        $query[] = ($this->join ? 'left join '.join("\r\n".'left join ', $this->join) : '');
        $query[] = ($this->where ? 'where ('.join(') and (', $this->where).')' : '');

        if (!$subquery = self::_build_subquery()){
            $query[] = ($this->group_by ? 'group by '.join(', ', $this->group_by): '');
            $query[] = ($this->having ? 'having '.join(', ', $this->having): '');
        }

        $query[] = ($this->order_by ? 'order by '.join(', ', $this->order_by) : '');

        if (!$subquery and !$this->multiple and $this->limit){
            $query[] = 'limit '.$this->limit;
            $query[] = ($this->offset ? 'offset '.$this->offset : '');
        }

        return join("\r\n", $query);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_subquery(){
        if (!($this->multiple and ($this->limit or $this->where)))
            return;

        self::_prepare_bulid();

        $query[] = 'select '.$this->table.'.'.$this->primary_key;
        $query[] = 'from '.join(', ', $this->from);

        $query[] = ($this->join ? 'left join '.join("\r\n".'left join ', $this->join) : '');
        $query[] = ($this->where ? 'where ('.join(') and (', $this->where).')' : '');
        $query[] = 'group by '.$this->table.'.'.$this->primary_key;
        $query[] = ($this->having ? 'having '.join(', ', $this->having): '');
        $query[] = ($this->order_by ? 'order by '.join(', ', $this->order_by) : '');

        if ($this->limit){
            $query[] = 'limit '.$this->limit;
            $query[] = ($this->offset ? 'offset '.$this->offset : '');
        }

        return join("\r\n", $query);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_save(){        if (!$this->where)
            return 'insert into ['.$this->_table.'] set ?a on duplicate key update ?a';

        $query[] = 'update ['.$this->_table.'] as '.$this->table.' set ?a';
        $query[] = 'where ('.join(') and (', $this->where).')';
        $query[] = ($this->order_by ? 'order by '.join(', ', $this->order_by) : '');

        if ($this->limit)
            $query[] = 'limit '.$this->limit;

        return join("\r\n", $query);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_insert(){        foreach ($this->values as $value){
            $values[] = '(?a)';
            $this->placeholders[] = $value;
        }

        $query[] = 'insert into ['.$this->_table.']';
        $query[] = '(`'.join('`, `', $this->into).'`)';
        $query[] = 'values '.join(', ', $values);

        return join("\r\n", $query);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_delete(){        if ($pos = stripos(($from = $this->from[0]), ' as '))
            $table = trim(substr($this->from[0], 0, $pos));

        $query[] = 'delete from ['.$this->_table.']';
        $query[] = ($this->where ? 'where ('.join(') and (', $this->where).')' : '');
        $query[] = ($this->order_by ? 'order by '.join(', ', $this->order_by) : '');

        if ($this->limit)
            $query[] = 'limit '.$this->limit;

        return join("\r\n", $query);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_HABTM(){        $query = array();
        if (!$this->joinvalues)
            return $query;
        $id = ($this->id ? $this->id : $this->last_id());
        foreach ($this->joinvalues as $k => $v){
            $relation = $this->relations[$k];
            $foreign = $relation['foreign'];

            if ($this->id)
                $query[] = 'delete from ['.$foreign['table1'].'] where `'.$foreign['field1'].'` = '.$id;

            $query[] = 'insert into ['.$foreign['table1'].'] '.
                       '(`'.join('`, `', array($foreign['field1'], $foreign['field3'])).'`) '.
                       'values ('.$id.', '.join('), ('.$id.', ', $v).')';
        }

        return $query;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_create(){
        $query[] = 'create table ['.$this->_table.'] (';
        $query[] = 'id int not null auto_increment,';
        $query[] = 'primary key (id)';
        $query[] = ') default charset=utf8 collate=utf8_unicode_ci';

        return join("\r\n", $query);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_alter(){        $array1 = $this->values;
        $array2 = $this->schema($this->_table);

        $query = array();
        $array = arr::assoc(array_diff_assoc(arr::flat($array1), arr::flat($array2)));
        $keys  = array('p' => 'primary', 'u' => 'unique');

        foreach (array_keys($array) as $k){            $name = $array1[$k]['name'];

            if (array_key_exists($name, $array2)){                $before = $array2[$name];
                $after = array_merge($before, $array1[$k]);
                $k = $name;
            } elseif (array_key_exists($k, $array2)){                $before = $array2[$k];
                $after = array_merge($before, $array1[$k]);            } else {                $before = array();
                $after = $array1[$k];            }

            $name = '`'.($name ? $name : $k).'`';
            $k = '`'.$k.'`';

            if ($before == $after)
                continue;

            if (
                isset($after['default']) and
                !is_numeric($after['default']) and
                strtolower($after['default']) != 'current_timestamp'
            )
                $after['default'] = self::$sql->escape($after['default']);

            $query[] = ($before ? 'change '.$k : 'add').' '.
                       $name.' '.$after['type'].' '.(!$after['null'] ? 'not' : '').' null '.
                       ($after['auto'] ? 'auto_increment' : '').' '.
                       (isset($after['default']) ? 'default '.$after['default'] : '');

            $key = array (
                'add'  => 'add '.$keys[$after['key']].' key ('.$name.')',
                'drop' => 'drop key '.($before['key'] != 'p' ? $name : '')
            );

            if (array_key_exists('key', $after)){
                if (!$after['key'] and $before['key'])
                    $query[] = $key['drop'];
                elseif ($after['key'] != $before['key'])
                    $query[] = ($before['key'] ? $key['drop'].', ' : '').$key['add'];
            }
        }

        if ($query)
            return 'alter table ['.$this->_table.'] '.join(', ', $query);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_count(){        self::_prepare_bulid();

        $query[] = 'select count(*)';
        $query[] = 'from '.join(', ', $this->from);

        $query[] = ($this->join ? 'left join '.join("\r\n".'left join ', $this->join) : '');
        $query[] = ($this->where ? 'where ('.join(') and (', $this->where).')' : '');
        $query[] = ($this->group_by ? 'group by '.join(', ', $this->group_by): '');
        $query[] = ($this->having ? 'having '.join(', ', $this->having): '');

        return join("\r\n", $query);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_join($relation){        if (in_array($relation['type'], array('has_one', 'belongs_to', 'has_many')))
            $join[] = str::format('
                [%foreign.table] as `%foreign.alias` on (
                    `%foreign.alias`.%foreign.field = `%local.alias`.%local.field
                )
            ', $relation);

        if ($relation['type'] == 'has_and_belongs_to_many'){
            $join[] = str::format('
                [%foreign.table1] as `%foreign.alias1` on (
                    `%foreign.alias1`.%foreign.field1 = `%local.alias`.%local.field
                )
            ', $relation);
            $join[] = str::format('
                [%foreign.table2] as `%foreign.alias2` on (
                    `%foreign.alias2`.%foreign.field2 = `%foreign.alias1`.%foreign.field3
                )
            ', $relation);
        }

        return $join;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_schema(){        return 'desc ?_';    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_childrens(){
        return 'select ?# as array_key, ?# as parent_key from ?_';
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_HABTM_IDs(){
        return 'select ?# from ?_ where ?# = ?d';
    }

////////////////////////////////////////////////////////////////////////////////
}