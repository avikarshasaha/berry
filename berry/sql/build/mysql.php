<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_Build_MySQL extends SQL_Build_Base {

////////////////////////////////////////////////////////////////////////////////

    function _select(){
        $keywords = b::config('sql.select');
        $query = '';

        foreach ($keywords as $k => $v){
            $key = strtr($k, array(' ' => '_'));
            $value = $this->o->$key;

            if (!$value)
                continue;

            $query .= ' '.$k.' ';

            if ($k == 'where')
                $query .= '('.join(') and (', $this->o->where).')';
            elseif ($k != 'join')
                $query .= (is_array($value) ? join(', ', $value) : $value);
        }

        if (!$this->o->union)
            return $this->rebuild($query, $keywords);

        $union = '('.join(') union (', $this->o->union).')';
        $query  = ' order by '.str_replace($this->o->alias.'.', '', join(', ', $this->o->order_by));
        $query .= ' limit '.$this->o->limit;
        $query .= ' offset '.$this->o->offset;

        return $union.$this->rebuild($query, $keywords);
    }

////////////////////////////////////////////////////////////////////////////////

    function _insert(){
        $keywords = b::config('sql.insert');
        $query  = 'insert into '.$this->o->table;
        $query .= ' (?f) values (?a) ';
        $query .= ($this->o->into ? str_repeat(', (?a) ', b::len($this->o->values) - 1) : '');

        return $this->rebuild(substr($query, 0, -1), $keywords);
    }

////////////////////////////////////////////////////////////////////////////////

    function _update(){
        $keywords = b::config('sql.update');
        $query  = 'update '.$this->o->table;
        $query .= ' as '.$this->o->alias;
        $query .= ' set ?a';
        $query .= ' where ('.join(') and (', $this->o->where).')';
        $query .= ' order by '.join(', ', $this->o->order_by);
        $query .= ' limit '.$this->o->limit;

        return $this->rebuild($query, $keywords);
    }

////////////////////////////////////////////////////////////////////////////////

    function _delete(){
        if (!$this->o->where and !$this->o->limit)
            return 'truncate table '.$this->o->table;

        $class = clone $this;
        $class->o->alias = inflector::tableize($this->o->alias);

        $keywords = b::config('sql.delete');
        $query  = 'delete from '.$this->o->table;
        $query .= ' where ('.join(') and (', $this->o->where).')';
        $query .= ' order by '.join(', ', $this->o->order_by);
        $query .= ' limit '.$this->o->limit;

        return $class->rebuild($query, $keywords);
    }

////////////////////////////////////////////////////////////////////////////////

    function _create(){
        $query  = 'create table '.$this->o->table;
        $query .= ' (';
        $query .= $this->o->primary_key.' int not null auto_increment,';
        $query .= 'primary key ('.$this->o->primary_key.')';
        $query .= ') default charset=utf8 collate=utf8_unicode_ci';

        return $query;
    }

////////////////////////////////////////////////////////////////////////////////

    function _alter(){
        $array1 = $this->o->values;
        $array2 = $this->o->schema();

        $query = array();
        $array = arr::assoc(array_diff_assoc(arr::flat($array1), arr::flat($array2)));
        $keys  = array('p' => 'primary', 'u' => 'unique');

        foreach (array_keys($array) as $k){
            $name = $array1[$k]['name'];

            if (array_key_exists($name, $array2)){
                $before = $array2[$name];
                $after = array_merge($before, $array1[$k]);
                $k = $name;
            } elseif (array_key_exists($k, $array2)){
                $before = $array2[$k];
                $after = array_merge($before, $array1[$k]);
            } else {
                $before = array();
                $after = $array1[$k];
            }

            $name = '`'.($name ? $name : $k).'`';
            $k = '`'.$k.'`';

            if ($before == $after)
                continue;

            if ($before['default'] === $after['default'])
                unset($after['default']);

            if (
                isset($after['default']) and
                !is_numeric($after['default']) and
                strtolower($after['default']) != 'current_timestamp'
            ){
                $this->o->placeholders[] = $after['default'];
                $after['default'] = '?';
            }
            
            $after['type'] .= ($after['length'] ? '('.$after['length'].')' : '');
            $after['type'] .= ' '.$after['attr'];

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
            return 'alter table '.$this->o->table.' '.join(', ', $query);
    }

////////////////////////////////////////////////////////////////////////////////

    function _join($relation){
        $join = array();

        if ($relation['foreign']['table1'])
            $table = &$relation['foreign']['table1'];
        else
            $table = &$relation['foreign']['table'];

        if ($pos = strpos($table, '.'))
            $table = '`'.substr($table, 0, $pos).'`.`'.substr($table, ($pos + 1)).'`';
        else
            $table = '`'.$table.'`';

        if (in_array($relation['type'], array('has_one', 'belongs_to', 'has_many')))
            $join[] = str::format('
                %foreign.table as %foreign.alias on (
                    %foreign.alias.%foreign.field = %local.alias.%local.field
                )
            ', $relation);

        if ($relation['type'] == 'has_and_belongs_to_many'){
            $join[] = str::format('
                %foreign.table1 as %foreign.alias1 on (
                    %foreign.alias1.%foreign.field1 = %local.alias.%local.field
                )
            ', $relation);
            $join[] = str::format('
                %foreign.table2 as %foreign.alias2 on (
                    %foreign.alias2.%foreign.field2 = %foreign.alias1.%foreign.field3
                )
            ', $relation);
        }

        return $join;
    }

////////////////////////////////////////////////////////////////////////////////

    function _schema($table){
        $result = array();
        $query = new SQL_Query('desc ?t', array($table));
        $keys = array('p' => 'p', 'u' => 'u', 'k' => 'k', 'm' => 'k');

        foreach ($query->fetch() as $info){
            preg_match('/(\w+)(\((\d+)\))?( (.*))?/i', $info['type'], $m);
            
            $result[$info['field']] = array(
                'name' => $info['field'],
                'type' => $m[1],
                'length' => (int)$m[3],
                'attr' => ($info['extra'] == 'auto_increment' ? $m[5] : $m[5].$info['extra']),
                'null' => ($info['null'] == 'yes'),
                'key'  => (string)$keys[strtolower($info['key'][0])],
                'auto' => ($info['extra'] == 'auto_increment'),
                'default' => $info['default']
            );
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function _last_id($table){
	    $query = new SQL_Query('show table status like "?t"', $table);
	    $query = $query->fetch_row();

	    return max(0, ($query['Auto_increment'] - 1));
    }

////////////////////////////////////////////////////////////////////////////////

    function _subquery($where, $parent){
        return call_user_func(array($this, '_subquery_'.$where), $parent);
    }

////////////////////////////////////////////////////////////////////////////////

    function _subquery_select($parent){
        if (!$this->o->relations[$table = $parent->alias])
            $table = inflector::tableize($table);

        $this->o->placeholders = array_merge($parent->placeholders, $this->o->placeholders);
        $query = trim($parent->build('select'));

        if ($query[0] != '(')
            $query = '('.$query.') as _'.$table;
        elseif (!stripos($query, ' as '))
            $query .= ' as _'.$table;

        return $query;
    }

////////////////////////////////////////////////////////////////////////////////

    function _subquery_join($parent){
        if (
            (!$relation = $this->o->relations[$table = $parent->alias]) and
            (!$relation = $this->o->relations[$table = inflector::tableize($table)])
        )
            return;

        $this->o->placeholders = array_merge($parent->placeholders, $this->o->placeholders);

        $alias = 'subquery_'.$parent->alias;
        $parent->select[] = $relation['foreign']['field'];
        $parent->from[0] = $parent->table.' as '.$alias;
        $parent->alias = $alias;

        $query[] = '(';
        $query[] = $parent->build('select');
        $query[] = ') as _'.$table.' on (';
        $query[] = str::format('_%foreign.alias.%foreign.field = %local.alias.%local.field', $relation);
        $query[] = ')';

        return join("\r\n", $query);
    }

////////////////////////////////////////////////////////////////////////////////

}