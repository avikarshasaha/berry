<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL extends SQL_Vars implements Countable {

////////////////////////////////////////////////////////////////////////////////

    function __construct($id = 0, $class = ''){
        $class = strtolower($class ? $class : get_class($this));
        $this->alias = (substr($class, -4) == '_sql' ? substr($class, 0, -4) : $class);

        if (!$this->table){
            $this->table = self::$connection['prefix'].inflector::tableize($this->alias);
        } else {
            $tmp = self::$connection;
            unset($tmp['link']);
            $this->table = str::format($this->table, $tmp);
        }

        $this->table = trim($this->table, '.');
        $this->query = self::$connection['link']->select()->from(array($this->alias => $this->table));
        $this->relations = self::_deep_throat($this->alias);

        if ($id)
            $this->_find('where', $id);
    }

////////////////////////////////////////////////////////////////////////////////

    function query(){
        return $this->query->query();
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch(){
        return self::query()->fetchAll();
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_array(){
        $query = clone $this->query;

        if ($multiple = array_unique($this->multiple)){
            $query->columns(array('__' => self::_name($this->primary_key)));

            foreach ($multiple as $k => $v){
                $vars = self::_vars(inflector::singular(end(explode('.', $v))));
                $field = $v.'.'.($vars['primary_key'] ? $vars['primary_key'] : 'id');
                $query->columns(array('__'.$v => self::_name($field)));
            }
        }

        $tmp = $this->query;
        $this->query = $query;
        $array = self::fetch();
        $this->query = $tmp;

        if (!$array)
            return array();

        if (!$multiple){
            $result = arr::assoc($array);
            return ($this->id ? $result[0] : $result);
        }

        $result = $repl = $values = array();
        $func = create_function('$a, $b', 'return (substr_count($a, ".") < substr_count($b, "."));');

        foreach ($array as $row){
            $i = $row['__'];
            unset($row['__']);

            if (!isset($result[$i]))
                $result[$i] = array();

            foreach ($row as $k => $v)
                if (substr($k, 0, 2) == '__'){
                    unset($row[$k]);

                    if ($v !== null){
                        $key = substr($k, 2);
                        $repl[$key] = $key.'.'.$v;
                    }
                } elseif ($v === null){
                    unset($row[$k]);
                }

            uasort($repl, $func);

            foreach ($row as $k => $v){
                $key = str_replace(array_keys($repl), array_values($repl), $k);
                $row[$key] = $v;

                if (
                    ($pos = strrpos($k, '.')) and
                    isset($repl[$key = substr($k, 0, $pos)])
                )
                    unset($row[$k]);
            }

            $result[$i] += $row;
        }

        $result = self::_fetch_array(arr::assoc($result));
        $result = array_values($result);

        return ($this->id ? $result[0] : $result);
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _fetch_array($array){
        $result = array();

        foreach ($array as $k => $v){
            if (is_array($v))
                $v = self::_fetch_array(is_int(key($v)) ? array_values($v) : $v);

            $result[$k] = $v;
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_row(){
        $query = clone $this->query;

        return $query->limit(1)->query()->fetch();
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_pair($number1 = 1, $number2 = null){
        if ($number2 === null)
            list($number1, $number2) = array(0, $number1);

        $array1 = $array2 = self::fetch();
        self::_fetch_column($array2, 0, $number1);
        $result = array();

        foreach ($array2 as $k => $v)
            if ($number2){
                for ($i = 0; $i < $number2; $i++)
                    $result[$v] = next($array1[$k]);
            } else {
                $result[$v] = reset($array1[$k]);
            }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_column($number = null){
        $array = self::fetch();
        self::_fetch_column($array, 0, $number);

        return ($array ? $array : array());
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _fetch_column(&$v, $k = 0, $number = null){
        if ($number){
            for ($i = 0; $i < $number; $i++)
                $cell = next($v);
        } else {
            $cell = reset($v);
        }

        if (!is_array($cell))
            $v = $cell;
        else
            array_walk($v, array('self', '_fetch_column'), $number);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_cell($number = 0){
        return self::query()->fetchColumn($number);
    }

////////////////////////////////////////////////////////////////////////////////

    function count(){
        $query = self::table($this->table)->with('count(*)');
        $query->_find('where', $this->query->getPart('where'));
        $query->_find('having', $this->query->getPart('having'));
        $query->group($this->query->getPart('group'));

        return array_sum($query->fetch_column());
    }

////////////////////////////////////////////////////////////////////////////////

    function exists(){
        $query = clone $this->query;

        return (bool)$query->reset('columns')->columns(self::raw('1'))->query()->fetchColumn();
    }

////////////////////////////////////////////////////////////////////////////////

    function sort($order_by = null){
        if (!$order_by)
            $order_by = $this->primary_key;
        elseif (!is_array($order_by))
            $order_by = func_get_args();

        foreach ((array)$order_by as $v){
            $v = ($v[0] == '-' ? substr($v, 1).' desc' : $v);
            $this->query->order(self::_name($v));
        }

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function group($group_by = null){
        if (!$group_by)
            $group_by = $this->primary_key;
        elseif (!is_array($group_by))
            $group_by = func_get_args();

        foreach ((array)$group_by as $v)
            $this->query->group(self::_name($v));

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function limit($limit = 0, $offset = 0){
        $this->query->limit($limit, $offset);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function page($limit = 0, $page = 0){
        $this->query->limitPage($page, $limit);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function begin(){
        self::$connection['link']->beginTransaction();

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function commit(){
        try {
            self::$connection['link']->commit();

            return $this;
        } catch (Exception $e){
            throw new SQL_Except;
        }
    }

////////////////////////////////////////////////////////////////////////////////

    function rollback(){
        self::$connection['link']->rollback();

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    static function find($where = null, $placeholders = array()){
        return self::table(get_called_class())->_find('where', $where, $placeholders);
    }

////////////////////////////////////////////////////////////////////////////////

    function where($where = null, $placeholders = array()){
        if (is_numeric($where))
            $where = array($this->primary_key => $where);

        return $this->_find('where', $where, $placeholders);
    }

////////////////////////////////////////////////////////////////////////////////

    function having($where = null, $placeholders = array()){
        return $this->_find('having', $where, $placeholders);
    }

////////////////////////////////////////////////////////////////////////////////

    function with($select){
        if (!is_array($select))
            $select = func_get_args();

        if (!$this->with)
            $this->query->reset('columns');

        $this->with = true;
        $join = array();

        foreach ((array)$select as $v)
            if ($pos = strpos($v, '.')){
                $k = substr($v, 0, $pos);
                $v = substr($v, ($pos + 1));

                if ($relation = $this->relations[$k]){
                    $local = $relation['local'];
                    $foreign = $relation['foreign'];
                    $table = ($foreign['table2'] ? $foreign['table2'] : $foreign['table']);
                    $alias = ($foreign['alias2'] ? $foreign['alias2'] : $foreign['alias']);

                    $join[$k][0] = array($alias => $table);
                    $join[$k][1] = str::format('%foreign.alias.%foreign.field = %local.alias.%local.field', $relation);

                    if (in_array($relation['type'], array('has_one', 'belongs_to', 'has_many')))
                        $join[$k][1] = str::format('%foreign.alias.%foreign.field = %local.alias.%local.field', $relation);

                    if ($relation['type'] == 'has_and_belongs_to_many'){
                        $join[$k][1] = str::format('%foreign.alias2.%foreign.field2 = %foreign.alias1.%foreign.field3', $relation);

                        $this->query->joinLeft(
                            array($foreign['alias1'] => $foreign['table1']),
                            str::format('%foreign.alias1.%foreign.field1 = %local.alias.%local.field', $relation),
                            array()
                        );
                    }

                    if (substr($relation['type'], -4) == 'many')
                        $this->multiple[] = $alias;

                    if ($v == '*'){
                        $schema = self::_schema($table);

                        foreach ($schema['cols'] as $col)
                            $join[$k][2][$alias.'.'.$col] = $col;
                    } else {
                        $join[$k][2][$alias.'.'.$v] = $v;
                    }
                }
            } else {
                $this->query->columns($v, $this->alias);
            }

        foreach ($join as $v)
            $this->query->joinLeft($v[0], $v[1], $v[2]);

        foreach ($this->query->getPart('columns') as $v)
            if ($found = ($v[0] == $this->alias and is_string($v[1])))
                break;

        if (!$found)
            self::with('*');

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function union($table){
        if (!is_array($table))
            $table = func_get_args();

        foreach ($table as $k => $v)
            $table[$k] = '('.$v.')';

        $class = self::table('sql');
        $class->query->reset('columns')->reset('from');
        $class->query->union($table);

        return $class;
    }

////////////////////////////////////////////////////////////////////////////////

    function save($full = false){
        $result = array();

        self::_check();
        self::_save($result);

        if (!array_key_exists($this->alias, $result))
            $result = array($this->alias => array(false)) + $result;

        if ($full)
            return $result;

        $result = $result[$this->alias];
        self::_fetch_column($result);

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _check(){
        if (!check::is_valid($this->check, $this->values))
            throw new Check_Except($this->check, $this->alias);

        foreach ($this->children as $class)
            if ($class instanceof SQL)
                $class->_check();
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _save(&$result, $is_children = false){
        $values = $this->values;
        $belongs = array();

        foreach ($values as $k => $v)
            if (self::_is_HABTM($k)){
                $v = array_unique((array)$v);
                sort($v);
                unset($values[$k]);

                if (self::$cache[$this->alias][$k] != $v)
                    $belongs[substr($k, 0, -4)] = $v;
            } elseif ($k[0] == '#'){
                $first = ($result[$v] ? array_shift($result[$v]) : 0);
                $values[substr($k, 1)] = $first;
                $result[$v][] = $first;
                unset($values[$k]);

                if (
                    !$this->where and !$this->relations['#'] and
                    $result[$this->alias] and $result[$this->alias][0]
                )
                    $this->where($result[$this->alias][0]);
            }

        if ($values){
            $this->values = array();

            if ($where = $this->query->getPart('where')){
                $where = join(' ', $where);
                $where = str_replace($this->alias.'.', '', $where);
                $result[$this->alias][] = (bool)self::$connection['link']->update($this->table, $values, $where);
            } else {
                if ($id = (bool)self::$connection['link']->insert($this->table, $values)){
                    $id = (int)self::$connection['link']->lastInsertId();

                    $key = self::_hash();
                    $this->id = self::$cache[$key][$this->primary_key] = $id;

                    $this->where($id);
                }

                $result[$this->alias][] = $id;
            }
        }

        foreach ($belongs as $k => $v){
            $local = $this->relations[$k]['local'];
            $foreign = $this->relations[$k]['foreign'];

            $class = self::table($foreign['alias1']);
            $id = ($this->id ? $this->id : $result[$local['alias']][0]);

            $class->where(array($foreign['field1'] => $id))->delete();
            $class->query->reset('where');

            foreach ($v as $i){
                $class->values[$foreign['field1']] = $id;
                $class->values[$foreign['field3']] = $i;
            }

            $this->children[] = $class;
        }

        foreach ($this->children as $class)
            if ($class instanceof self)
                $class->_save($result, true);
    }

////////////////////////////////////////////////////////////////////////////////

    function delete(){
        $where = join(' ', $this->query->getPart('where'));
        $where = str_replace($this->alias.'.', '', $where);

        return self::$connection['link']->delete($this->table, $where);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _find($func, $where = null, $placeholders = array()){
        if (is_numeric($where)){
            $this->query->{$func}($this->_name($this->primary_key).' = ?', $where);

            if ($func == 'where')
                $this->id = $where;
        }

        if (is_string($where))
            $this->query->{$func}($this->_name($where), $placeholders);

        if (is_array($where) and is_numeric($where[0]))
            $where = array($this->primary_key => $where);

        if (is_array($where))
            foreach ($where as $k => $v){
                if (is_int($k) and is_array($v)){
                    if (isset($v[0])){
                        $result = array();

                        foreach ($v as $k2 => $v2)
                            foreach ($v2 as $k3 => $v3){
                                $tmp = $this->_name($k3).(strpos($v3, '?') ? $v3 : ' = ?');
                                $result[] = self::_quote($tmp, $v3);
                            }

                        $this->query->{$func}(join(' or ', $result));
                    } else {
                        foreach ($v as $k2 => $v2)
                            $this->query->{$func}($this->_name($k2).(strpos($v2, '?') ? $v2 : ' = ?'), $v);
                    }
                } elseif (is_int($k)){
                    $this->query->{$func}($v);
                } else {
                    if (!strpos($k, '?'))
                        $k .= (is_array($v) ? ' in (?)' : ' = ?');

                    $this->query->{$func}($this->_name($k), $v);
                }
            }

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function __toString(){
        return (string)$this->query;
    }

////////////////////////////////////////////////////////////////////////////////

}