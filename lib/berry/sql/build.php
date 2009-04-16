<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_build extends SQL_etc {
////////////////////////////////////////////////////////////////////////////////

    function build($type){        return $this->_prepareBuild()->{'_build'.$type}();    }

////////////////////////////////////////////////////////////////////////////////

    private function _appendJoin($v){        if ($pos = strrpos($v, '.')){
            $table = substr($v, 0, $pos);

            if ($table != $this->table and strpos($table, '`') === false)
                $this->join($table);
        }
    }

////////////////////////////////////////////////////////////////////////////////

    private function _prepareFields($v){        $this->_appendJoin($v);

        if (strpos($v, '`') === false)
            $v = preg_replace('/([\w\.]+)\.(\w+)/i', '`\\1`.\\2', $v);

        return $v;
    }

////////////////////////////////////////////////////////////////////////////////

    private function _prepareGetAll($v){
        static $cache = array();

        if (strtolower($v) == strtolower($this->table))
            return array('`'.$v.'`.*');

        $vars = get_class_vars(($pos = strrpos($v, '.')) ? substr($v, ($pos + 1)) : $v);
        $key = ($vars['table'] ? $vars['table'] : $v);

        if (!$cache[$key]){            $this->_appendJoin($v);
            $cache[$key] = array_keys($this->schema($key));
        }

        if (!$cache['#'][$v])
            foreach ($cache[$key] as $k)
                $cache['#'][$v][] = '`'.$v.'`.'.$k.' as `'.$v.'.'.$k.'`';

        return $cache['#'][$v];
    }

////////////////////////////////////////////////////////////////////////////////

    private function _prepareBuild(){        foreach ($this->select as $v){
            $this->_appendJoin($v);

            $if = (
                !stripos($v, " as ") and is_bool(strpos($v, "`")) and
                strtolower(substr($v, 0, (b::len($this->table) + 1))) != strtolower($this->table.".")
            );

            if ($v == '*'){                $v = $this->table.'.*';
            } elseif (strpos($v, '*')){                $v = join(', ', $this->_prepareGetAll(preg_replace('/([\w\.]+)\.\*/', '\\1', $v)));
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
                    $from[] = '['.(trim(substr($v, 0, $pos))).']'.substr($v, $pos);
                else
                    $from[] = '['.$v.']';
            }

        $this->select = $select;
        $this->from = $from;
        $this->join = array_unique($this->join);
        $this->where = array_map(array($this, '_prepareFields'), $this->where);
        $this->order_by = array_map(array($this, '_prepareFields'), $this->order_by);
        $this->group_by = array_map(array($this, '_prepareFields'), $this->group_by);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    private function _buildGet(){
        $query[] = 'select '.join(', ', $this->select);
        $query[] = 'from '.join(', ', $this->from);
        $query[] = ($this->join ? 'left join '.join("\r\n".'left join ', $this->join) : '');
        $query[] = ($this->where ? 'where ('.join(') and (', $this->where).')' : '');
        $query[] = ($this->group_by ? 'group by '.join(', ', $this->group_by): '');
        $query[] = ($this->order_by ? 'order by '.join(', ', $this->order_by) : '');

        if (!$this->multiple and $this->limit){
            $query[] = 'limit '.$this->limit;
            $query[] = ($this->offset ? 'offset '.$this->offset : '');
        }

        return join("\r\n", $query);
    }

////////////////////////////////////////////////////////////////////////////////

    private function _buildGetSub(){ // :(
        if (!($this->multiple and ($this->limit or $this->where)))
            return;

        $query[] = 'select '.$this->table.'.'.$this->primary_key;
        $query[] = 'from '.join(', ', $this->from);

        $query[] = ($this->join ? 'left join '.join("\r\n".'left join ', $this->join) : '');
        $query[] = ($this->where ? 'where ('.join(') and (', $this->where).')' : '');
        $query[] = 'group by '.$this->table.'.'.$this->primary_key;
        $query[] = ($this->order_by ? 'order by '.join(', ', $this->order_by) : '');

        if ($this->limit){
            $query[] = 'limit '.$this->limit;
            $query[] = ($this->offset ? 'offset '.$this->offset : '');
        }

        return join("\r\n", $query);
    }

////////////////////////////////////////////////////////////////////////////////

    private function _buildSave(){        if (!$this->where)
            return 'insert into ['.$this->_table.'] set ?a on duplicate key update ?a';

        $query[] = 'update ['.$this->_table.'] as '.$this->table.' set ?a';
        $query[] = 'where ('.join(') and (', $this->where).')';
        $query[] = ($this->order_by ? 'order by '.join(', ', $this->order_by) : '');

        if ($this->limit){
            $query[] = 'limit '.$this->limit;
            $query[] = ($this->offset ? 'offset '.$this->offset : '');
        }

        return join("\r\n", $query);
    }

////////////////////////////////////////////////////////////////////////////////

    private function _buildInsert(){        foreach ($this->values as $value){
            $values[] = '(?a)';
            $this->placeholders[] = $value;
        }

        $query[] = 'insert into ['.$this->_table.']';
        $query[] = '(`'.join('`, `', $this->into).'`)';
        $query[] = 'values '.join(', ', $values);

        return join("\r\n", $query);
    }

////////////////////////////////////////////////////////////////////////////////

    private function _buildDelete(){        if ($pos = stripos(($from = $this->from[0]), ' as '))
            $table = trim(substr($this->from[0], 0, $pos));

        $query[] = 'delete from ['.$this->_table.']';
        $query[] = ($this->where ? 'where ('.join(') and (', $this->where).')' : '');
        $query[] = ($this->order_by ? 'order by '.join(', ', $this->order_by) : '');

        if ($this->limit){
            $query[] = 'limit '.$this->limit;
            $query[] = ($this->offset ? 'offset '.$this->offset : '');
        }

        return join("\r\n", $query);
    }

////////////////////////////////////////////////////////////////////////////////

    private function _buildHABTM(){        $query = array();
        if (!$this->joinvalues)
            return $query;
        $id = ($this->id ? $this->id : ($this->last_id() + 1));
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

    private function _buildCreate(){
        $query[] = 'create table ['.$this->_table.'] (';
        $query[] = 'id int not null auto_increment,';
        $query[] = 'primary key (id)';
        $query[] = ') default charset=utf8 collate=utf8_unicode_ci';

        return join("\r\n", $query);
    }

////////////////////////////////////////////////////////////////////////////////

    private function _buildAlter(){        $array1 = $this->values;
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
                $after['default'] = '"'.mysql_real_escape_string($after['default']).'"';

            $query[] = 'alter table ['.$this->_table.'] '.($before ? 'change '.$k : 'add').' '.
                       $name.' '.$after['type'].' '.(!$after['null'] ? 'not' : '').' null '.
                       ($after['auto'] ? 'auto_increment' : '').' '.
                       ($after['default'] ? 'default '.$after['default'] : '');

            $key = array (
                'add'  => 'add '.$keys[$after['key']].' key ('.$name.')',
                'drop' => 'drop key '.($before['key'] != 'p' ? $name : '')
            );

            if (array_key_exists('key', $after)){
                if (!$after['key'] and $before['key'])
                    $query[] = 'alter table ['.$this->_table.'] '.$key['drop'];
                elseif ($after['key'] != $before['key'])
                    $query[] = 'alter table ['.$this->_table.'] '.($before['key'] ? $key['drop'].', ' : '').$key['add'];
            }
        }

        return $query;
    }

////////////////////////////////////////////////////////////////////////////////
}