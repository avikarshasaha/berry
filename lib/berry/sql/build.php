<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
abstract class SQL_Build {
////////////////////////////////////////////////////////////////////////////////

    protected function tokenize($query, $keywords){
        $token = token_get_all('<?php '.$query);
        $result = $array = array();

        array_walk($keywords, create_function('&$v', 'if ($v === null) $v = "";'));

        for ($i = 1, $c = b::len($token); $i < $c; $i++){
            if (
                (
                    $token[$i + 1][0] == T_WHITESPACE and
                    isset($keywords[$key = strtolower($token[$i][1].' '.$token[$i + 2][1])]) and
                    $token[$i + 3][0] == T_WHITESPACE and $i += 2
                ) or
                (isset($keywords[$key = strtolower($token[$i][1])]) and $token[$i + 1][0] == T_WHITESPACE)
            ){
                if (!isset($result[$key])){
                    $result[$key] = array();
                    $array = &$result[$key];
                } else {                    $tmp = '';
                    $count = 0;

                    for ($j = $i; $j < $c; $j++){
                        if ($token[$j] == '(')
                            $count++;

                        $tmp .= (is_array($token[$j]) ? $token[$j][1] : $token[$j]);

                        if ($token[$j] == ')' and !$count--)
                            break;
                    }

                    $array[] = $tmp;
                    $i = $j;
                }

                continue;
            }

            if (is_array($token[$i])){
                end($array);
                $key = key($array);
                $key = ($key === null ? 0 : $key);

                for ($j = $i; $j < $c; $j++)
                    if ($token[$j - 1] == '['){
                        array_pop($array);
                        end($array);
                        $key = key($array);

                        for ($j2 = $j; $j2 < $c; $j2++){
                            $tmp = (is_array($token[$j2]) ? $token[$j2][1] : $token[$j2]);
                            $array[$key] .= $tmp;

                            if ($tmp == ']')
                                break;
                        }

                        $j = $j2;
                    } elseif ($token[$j][0] == T_WHITESPACE){
                        break;
                    } elseif ($token[$j + 1] == '('){
                        $array[] = $token[$j][1].'(';
                        $j += 1;
                    } elseif ($token[$j + 1] == '.'){
                        $array[$key] .= $token[$j][1].'.';
                        $j += 1;
                    } else {
                        if (substr($array[$key], -1) == '.'){
                            $array[$key] = array(substr($array[$key], 0, -1), $token[$j][1]);
                        } else {
                            $array[] = (is_array($token[$j]) ? $token[$j][1] : $token[$j]);
                            $array[] = '';
                        }

                        break;
                    }

                $i = $j;
            } else {
                $array[] = $token[$i];
                $array[] = '';
            }
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function rebuild($tokens, $keywords){        $tokens = (!is_array($tokens) ? $this->tokenize($tokens, $keywords) : $tokens);

        foreach ($tokens as $key => $value)
            foreach ($value as $k => $v)
                if (is_array($v)){                    $tmp = explode('.', $v[0]);
                    $name = '';

                    for ($i = 0, $c = b::len($tmp); $i < $c; $i++){
                        $name .= strtolower($tmp[$i]);

                        if (
                            (!$relation = $this->relations[$name]) and
                            (!$relation = $this->relations[$name = inflector::singular($name)])
                        )
                            break;

                        $name .= '.';
                        $local = $relation['local'];
                        $foreign = $relation['foreign'];
                        $table = ($foreign['table1'] ? $foreign['table1'] : $foreign['table']);

                        if ($pos = strpos($table, '.'))
                            $table = array(substr($table, 0, $pos), substr($table, ($pos + 1)));
                        else                            $table = '`'.$table.'`';

                        if (isset($tokens['select'])){                            if ($foreign['alias1']){
                                $foreign['alias'] = $foreign['alias1'];
                                $foreign['field'] = $foreign['field1'];
                            }
                            if ($foreign['alias'] and in_array($foreign['alias'], $tokens['from']))
                                continue;

                            $this->join(substr($name, 0, -1));

                            $tokens['from'] = array_merge($tokens['from'], array(
                                'left', 'join', $table, 'as', $foreign['alias'],
                                'on', '(',
                                    array($foreign['alias'], $foreign['field']),
                                    '=',
                                    array($local['alias'], $local['field']),
                                ')'
                            ));
                            if ($relation['type'] == 'has_and_belongs_to_many'){                                $table2 = $foreign['table2'];
                                if ($pos = strpos($table2, '.'))
                                    $table2 = array(substr($table2, 0, $pos), substr($table2, ($pos + 1)));
                                else
                                    $table2 = '`'.$table2.'`';

                                $tokens['from'] = array_merge($tokens['from'], array(
                                    'left', 'join', $table2, 'as', $foreign['alias2'],
                                    'on', '(',
                                        array($foreign['alias2'], $foreign['field2']),
                                        '=',
                                        array($foreign['alias1'], $foreign['field3']),
                                    ')'
                                ));                            }
                        } elseif (isset($tokens['using'])){                            if (!in_array($table, $tokens['using'])){
                                $tokens['using'][] = ',';
                                $tokens['using'][] = $table;
                            }
                        } else {                            if (!in_array($table, $tokens['from'])){
                                $tokens['from'][] = ',';
                                $tokens['from'][] = $table;
                            }
                        }                    }
                }

        foreach ($tokens as $key => $value){            $array = array();
            $quote = false;

            foreach ($value as $k => $v){
                if (trim($v) === '')
                    continue;

                if (is_array($v)){
                    if (strtolower(end($array)) == 'as'){
                        $array[] = '`'.$v[0].'.'.$v[1].'`';
                    } else {                        if ($key != 'select' or strtolower($value[$k + 1]) == 'as')
                            $array[] = '`'.$v[0].'`.'.$v[1];
                        else
                            $array[] = '`'.$v[0].'`.'.$v[1].' as `'.$v[0].'.'.$v[1].'`';
                    }
                } elseif ($v == '`'){
                    if (!$quote)
                        $array[] = $v.$value[$k + 2].$v;
                    else
                        array_pop($array);

                    $quote = !$quote;
                } elseif (strtolower(end($array)) == 'as'){
                    $array[] = '`'.$v.'`';
                } elseif (
                    $key == 'from' or strtolower($v) == 'as' or
                    !preg_match('/^\w+$/i', $v) or is_numeric($v) or
                    in_array(strtolower($v), (array)$keywords[$key])
                ){
                    $array[] = $v;
                } elseif (end($array) == '?'){
                    array_pop($array);
                    $array[] = '?'.$v;
                } else {
                    $array[] = ($this->alias ? '`'.$this->alias.'`.' : '').'`'.$v.'`';
                }
            }

            $result[$key] = join(' ', $array).$result[$key];
        }

        $key = key($keywords);

        foreach ($tokens as $k => $v)
            if ($key == $k or (($tmp = trim($result[$k]) and $tmp != '( )')))
                $query .= "\r\n$k ".$result[$k];

        return $query;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_select(){        $keywords = b::config('lib.sql.select');
        $query = '';

        foreach ($keywords as $k => $v){
            $key = strtr($k, array(' ' => '_'));
            $value = $this->$key;

            if (!$value)
                continue;

            $query .= ' '.$k.' ';

            if ($k == 'where')
                $query .= '('.join(') and (', $this->where).')';
            elseif ($k != 'join')
                $query .= (is_array($value) ? join(', ', $value) : $value);
        }

        if (!$this->union)
            return $this->rebuild($query, $keywords);

        $union = '('.join(') union (', $this->union).')';
        $query  = ' order by '.str_replace($this->alias.'.', '', join(', ', $this->order_by));
        $query .= ' limit '.$this->limit;
        $query .= ' offset '.$this->offset;

        return $union.$this->rebuild($query, $keywords);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_insert(){        $keywords = b::config('lib.sql.insert');
        $query  = 'insert into '.$this->table;
        $query .= ' (?f) values (?a) ';
        $query .= ($this->into ? str_repeat(', (?a) ', b::len($this->values) - 1) : '');

        return $this->rebuild(substr($query, 0, -1), $keywords);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_update(){
        $keywords = b::config('lib.sql.update');
        $query  = 'update '.$this->table;
        $query .= ' as '.$this->alias;
        $query .= ' set ?a';
        $query .= ' where ('.join(') and (', $this->where).')';
        $query .= ' order by '.join(', ', $this->order_by);
        $query .= ' limit '.$this->limit;

        return $this->rebuild($query, $keywords);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_delete(){        if (!$this->where and !$this->limit)            return 'truncate table '.$this->table;
        $class = clone $this;
        $class->alias = inflector::tableize($this->alias);

        $keywords = b::config('lib.sql.delete');
        $query  = 'delete from '.$this->table;
        $query .= ' where ('.join(') and (', $this->where).')';
        $query .= ' order by '.join(', ', $this->order_by);
        $query .= ' limit '.$this->limit;

        return $class->rebuild($query, $keywords);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_create(){
        $query  = 'create table '.$this->table;
        $query .= ' (';
        $query .= $this->primary_key.' int not null auto_increment,';
        $query .= 'primary key ('.$this->primary_key.')';
        $query .= ') default charset=utf8 collate=utf8_unicode_ci';

        return $query;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_alter(){        $array1 = $this->values;
        $array2 = $this->schema();

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

            if ($before['default'] === $after['default'])
                unset($after['default']);

            if (
                isset($after['default']) and
                !is_numeric($after['default']) and
                strtolower($after['default']) != 'current_timestamp'
            ){                $this->placeholders[] = $after['default'];
                $after['default'] = '?';
            }

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
            return 'alter table '.$this->table.' '.join(', ', $query);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_join($relation){        $join = array();

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

    protected function _build_schema($table){        $result = array();
        $query = new SQL_Query('desc ?t', array($table));

        foreach ($query->fetch() as $info)
            $result[$info['field']] = array(
                'name' => $info['field'],
                'type' => $info['type'],
                'null' => ($info['null'] == 'yes'),
                'key'  => (string)$keys[strtolower($info['key'][0])],
                'auto' => ($info['extra'] == 'auto_increment'),
                'default' => $info['default']
            );

        return $result;    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_last_id($table){
	    $query = new SQL_Query('show table status like "?t"', $table);
	    $query = $query->fetch_row();

	    return max(0, ($query['Auto_increment'] - 1));
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_subquery($where, $parent){        return call_user_func(array($this, '_build_subquery_'.$where), $parent);    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_subquery_select($parent){        if (!$this->relations[$table = $parent->alias])
            $table = inflector::tableize($table);

        $this->placeholders = array_merge($parent->placeholders, $this->placeholders);
        $query = trim($parent->build('select'));

        if ($query[0] != '(')
            $query = '('.$query.') as _'.$table;
        elseif (!stripos($query, ' as '))
            $query .= ' as _'.$table;

        return $query;    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_subquery_join($parent){        if (
            (!$relation = $this->relations[$table = $parent->alias]) and
            (!$relation = $this->relations[$table = inflector::tableize($table)])
        )
            return;

        $this->placeholders = array_merge($parent->placeholders, $this->placeholders);

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