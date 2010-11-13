<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
abstract class SQL_Etc extends SQL_Build {    protected $id;
    protected $table;
    protected $alias;

    protected $primary_key = 'id';
    protected $parent_key;

    protected $has_one = array();
    protected $belongs_to = array();
    protected $has_many = array();
    protected $has_and_belongs_to_many = array();

    protected $schema = array();
    protected $scope = array();
    protected $check = array();

    protected $into = array();
    protected $values = array();
    protected $select = array();
    protected $from = array();
    protected $join = array();
    protected $where = array();
    protected $group_by = array();
    protected $having = array();
    protected $order_by = array();
    protected $limit = 0;
    protected $offset = 0;
    protected $union = array();

    protected $parent;
    protected $iterator;
    protected $parallel = array();
    protected $relations = array();
    protected $multiple = array();
    protected $placeholders = array();

    protected static $connection;
    protected static $connections = array();
    protected static $cache = array();

////////////////////////////////////////////////////////////////////////////////

    static function init($dsn){        if (isset($dsn['database'])){            self::$connection = self::connect($dsn);
            return;
        }
        self::$connections += $dsn;
        self::$connection = self::connect(current($dsn));
        self::$connections[key($dsn)] = self::$connection;

        return key($dsn);
    }

////////////////////////////////////////////////////////////////////////////////

    static function using($key = ''){        static $last;

        if (!$last)
            $last = key(self::$connections);
        if (!$key or !isset(self::$connections[$key]))
            return $last;

        $dsn = self::$connections[$key];
        if (is_object($dsn['link'])){            self::$connection = $dsn;
        } elseif (is_array($dsn)){            self::$connection = self::connect($dsn);            self::$connections[$key] = self::$connection;
        }

        $current = $last;
        $last = $key;

        return $current;    }

////////////////////////////////////////////////////////////////////////////////

    static function table($table, $id = 0){        if (
            $class = $table and
            (class_exists($class, true) or class_exists($class = $class.'_sql', true)) and
            is_subclass_of($class, 'SQL')
        )
            return new $class($id);

        if (
            $class = inflector::singular($table) and
            (class_exists($class, true) or class_exists($class = $class.'_sql', true)) and
            is_subclass_of($class, 'SQL')
        )
            return new $class($id);

        return new SQL($id, $table);
    }

////////////////////////////////////////////////////////////////////////////////

    function query($query, $placeholders = null){        if ($placeholders === null and $this)
            $placeholders = $this->placeholders;
        elseif (!is_array($placeholders) or func_num_args() > 2)
            $placeholders = array_slice(func_get_args(), 1);
        return new SQL_Query($query, ($placeholders ? $placeholders : array()));
    }

////////////////////////////////////////////////////////////////////////////////

    static function raw($query){
        return new SQL_Raw($query);
    }

////////////////////////////////////////////////////////////////////////////////

    function last_id($table = ''){        return $this->build('last_id', ((!$table and $this) ? $this->table : $table));
    }

////////////////////////////////////////////////////////////////////////////////

    static function last_error($what = ''){        $error = self::$connection['link']->errorInfo();
        if (!isset($error[1]))
            return array();

        $error = array('code' => $error[1], 'string' => $error[2]);
        return ($what ? $error[$what] : $error);
    }

////////////////////////////////////////////////////////////////////////////////

    static function stat($what = ''){        if (!self::$connection)
            return array('time' => 0, 'count' => 0);
        $stat = self::$cache['stat'];
	    return ($what ? $stat[$what] : $stat);
    }

////////////////////////////////////////////////////////////////////////////////

    static function logger($func){        if (is_callable($func)){            self::$cache['logger'] = $func;
	        return true;
	    }
    }

////////////////////////////////////////////////////////////////////////////////

    function schema($table = ''){        if (!$table and $this){
            if ($this->schema)
                return $this->schema;

            $table = $this->table;
        } elseif ($table){
            if (strpos($table, '.'))
                $table = end(explode('.', $table));

            if ($vars = self::vars(inflector::singular($table)))
                $table = ($vars['table'] ? $vars['table'] : $table);
        } else {            return array();        }

        if (!$schema = cache::get('sql/schema/'.$table.'.php'))
            cache::set($schema = $this->build('schema', $table));

        return $schema;
    }

////////////////////////////////////////////////////////////////////////////////

    function build(){
        $args = func_get_args();
        $type = array_shift($args);

        return call_user_func_array(array($this, '_build_'.$type), $args);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function hash($prefix = ''){        return $this->alias.'::'.$prefix.'['.spl_object_hash($this).']';
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function connect($array){        $array = array_merge(array(
            'driver' => 'mysql'
        ), $array);

        if (!$array['dsn']){
            if ($pos = strrpos($array['database'], '/')){
                $array['host'] = substr($array['database'], 0, $pos);
                $array['database'] = substr($array['database'], ($pos + 1));
            }

            if ($pos = strpos($array['host'], ':')){
                $array['port'] = substr($array['host'], ($pos + 1));
                $array['host'] = substr($array['host'], 0, $pos);            }

            $array['dsn']  = $array['driver'].':';
            $array['dsn'] .= 'dbname='.$array['database'];

            if (strpos($array['host'], '/') !== false){
                $array['dsn'] .= '; unix_socket='.$array['host'];
            } else {                $array['dsn'] .= '; host='.$array['host'];
                $array['dsn'] .= ($port ? '; port='.$array['port'] : '');
            }
        }

        $array['link'] = new PDO($array['dsn'], $array['username'], $array['password'], array(
            PDO::ATTR_CASE => PDO::CASE_LOWER,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8'
        ));

        return $array;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function vars($table){        static $cache = array();

        if (isset($cache[$table]))
            return $cache[$table];

        $class = (($this and $table == $this->alias) ? clone $this : self::table($table));
        $class->alias = $class->table;

        return $cache[$table] = get_object_vars($class);    }

////////////////////////////////////////////////////////////////////////////////

    protected function relations($table1, $type, $table2){
        if (is_array($table2)){
            $key = key($table2);

            if (!is_numeric($key))
                list($table2, $keys) = array($key, reset($table2));
            else
                $table2 = reset($table2);
        }

        $vars1 = self::vars(inflector::singular($table1));
        $vars2 = self::vars(inflector::singular($table2));

        $local = array(
            'table' => $table1,
            'alias' => $table1,
            'field' => ($vars1['primary_key'] ? $vars1['primary_key'] : 'id')
        );
        $foreign = array(
            'table' => $table2,
            'alias' => $table2,
            'field' => ($vars2['primary_key'] ? $vars2['primary_key'] : 'id')
        );

        if ($type == 'has_one'){
            $local['field'] = inflector::singular($foreign['table']).'_'.$local['field'];
            $local['table'] = ($vars1['table'] ? $vars1['table'] : inflector::plural($local['table']));
            $foreign['table'] = ($vars2['table'] ? $vars2['table'] : inflector::tableize($foreign['table']));
        }

        if ($type == 'belongs_to'){
            $foreign['field'] = $local['alias'].'_'.$foreign['field'];
            $local['table'] = ($vars1['table'] ? $vars1['table'] : inflector::plural($local['table']));
            $foreign['table'] = ($vars2['table'] ? $vars2['table'] : inflector::tableize($foreign['table']));
        }

        if ($type == 'has_many'){
            $foreign['field'] = $local['alias'].'_'.$foreign['field'];
            $local['table'] = ($vars1['table'] ? $vars1['table'] : inflector::plural($local['table']));
            $foreign['table'] = ($vars2['table'] ? $vars2['table'] : $foreign['table']);
        }

        if ($type == 'has_and_belongs_to_many'){
            $table1 = inflector::plural($table1);
            $local['table'] = ($vars1['table'] ? $vars1['table'] : $local['table']);

            $foreign['table1'] = ($table1 < $table2 ? $table1.'_'.$table2 : $table2.'_'.$table1);
            $foreign['alias1'] = $foreign['table1'];
            $foreign['field1'] = inflector::singular($local['alias']).'_'.$foreign['field'];

            $foreign['table2'] = ($vars2['table'] ? $vars2['table'] : $foreign['table']);
            $foreign['alias2'] = $foreign['table'];
            $foreign['field2'] = $foreign['field'];

            $foreign['field3'] = inflector::singular($foreign['alias2']).'_'.$foreign['field'];

            unset($foreign['table'], $foreign['alias'], $foreign['field']);
        } elseif ($keys){            if ($keys['local'] and $keys['foreign'])
                list($local['field'], $foreign['field']) = array($keys['local'], $keys['foreign']);
            else
                list($local['field'], $foreign['field']) = $keys;
        }

        return compact('local', 'type', 'foreign', 'table');
    }

////////////////////////////////////////////////////////////////////////////////

    protected function deep_throat($class, $parent = '', $main = '', $result = array()){        if (is_array($class))
            list($class, $vars) = array(key($class), reset($class));

        $current = inflector::singular(substr($parent, strrpos($parent, '.')));
        $parent .= ($parent ? '.' : '');
        $main = ($main ? $main : $class);
        $vars = ($vars ? $vars : self::vars($class));

        foreach (array('has_one', 'belongs_to', 'has_many', 'has_and_belongs_to_many') as $has)
            foreach ((array)$vars[$has] as $key => $table){
                if ($table == $main)
                    continue;

                $relation = self::relations($class, $has, array($key => $table));
                $local = &$relation['local'];
                $foreign = &$relation['foreign'];

                if ($foreign['alias1']){
                    $alias = $foreign['alias2'];
                    $foreign['alias1'] = $parent.$foreign['alias1'];
                    $foreign['alias2'] = $parent.$foreign['alias2'];
                } else {
                    $alias = $foreign['alias'];
                    $foreign['alias'] = $parent.$foreign['alias'];
                }

                $tmp = inflector::tableize($local['alias']);

                if ($parent and ($relation['type'] == 'has_many' or strpos($parent, $tmp.'.') !== false))
                    $local['alias'] = $tmp;

                $table = inflector::singular($alias);
                $alias = $parent.$alias;
                $result[$alias] = $relation;

                if ($class != $current)
                    $result = array_merge($result, self::deep_throat($table, $alias, $main, $result));
            }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function where_between($field, $ids){        $len = b::len($ids);

        if ($len == 1)
            return $this->where($field.' = ?d', $ids[0]);

        sort($ids);
        $seq = $ids;
        $query = $args = array();

        for ($i = 0; $i < $len; $i += 2)
            if (
                (($i + 1) != $len and ($seq[$i] + 1) != $seq[$i + 1]) or
                (($i + 1) == $len and ($seq[$i] - 1) != $seq[$i - 1])
            ){
                unset($seq[$i]);
                $i -= 1;
            }

        if (b::len($seq) > 2){
            $ids = array_diff($ids, $seq);
            $tmp = $seq;
            $seq = array(array());

            foreach ($tmp as $k => $v){
                end($seq);
                $key = key($seq);
                $seq[$key][] = $v;

                if (($v + 1) != $tmp[$k + 1])
                    array_push($seq, array());
            }

            foreach ($seq as $k => $v)
                if (!$v){
                    unset($seq[$k]);
                } elseif (b::len($v) <= 2){
                    unset($seq[$k]);
                    $ids = array_merge($ids, $v);
                } else {
                    $query[] = $field.' >= ?d and '.$field.' <= ?d';
                    $args[] = min($v);
                    $args[] = max($v);
                }
        }

        if ($ids){
            $query[] = $field.' in (?a)';
            $args[] = $ids;
        }

        array_unshift($args, '('.join(') or (', $query).')');
        return call_user_func_array(array($this, 'where'), $args);    }

////////////////////////////////////////////////////////////////////////////////
}