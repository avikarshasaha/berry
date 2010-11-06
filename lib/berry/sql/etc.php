<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
defined('DBSIMPLE_SKIP') or define('DBSIMPLE_SKIP', log(0));
defined('DBSIMPLE_ARRAY_KEY') or define('DBSIMPLE_ARRAY_KEY', 'array_key');
defined('DBSIMPLE_PARENT_KEY') or define('DBSIMPLE_PARENT_KEY', 'parent_key');

abstract class SQL_Etc extends SQL_Build {    const SKIP = DBSIMPLE_SKIP;

    protected $id;
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
    protected $subquery_placeholders = array();

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
        if (is_object($dsn = self::$connections[$key])){            self::$connection = $dsn;
        } elseif (is_array($dsn)){            $logger = self::$connection->_logger;
            self::$connection = self::connect($dsn);
            self::$connection->_logger = $logger;            self::$connections[$key] = self::$connection;
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

    static function query(){        $args = func_get_args();        return new SQL_Query(is_array($args[0]) ? $args[0] : $args);
    }

////////////////////////////////////////////////////////////////////////////////

    static function raw($query){
        return new SQL_Raw($query);
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_valid(){
        return (bool)self::link();
    }

////////////////////////////////////////////////////////////////////////////////

    static function link(){
        return self::$connection->link;
    }

////////////////////////////////////////////////////////////////////////////////

    function last_id($table = ''){        return $this->build('last_id', ((!$table and $this) ? $this->table : $table));
    }

////////////////////////////////////////////////////////////////////////////////

    static function last_error($what = ''){        if (!$error = self::$connection->error)
            return array();

        unset($error['context']);
        return ($what ? $error[$what] : $stat);
    }

////////////////////////////////////////////////////////////////////////////////

    static function last_query($what = ''){
        if (!$query = self::$connection->_lastQuery)
            return array();

        $query = array('query' => $query[0], 'placeholders' => array_slice($query, 1));
        return ($what ? $query[$what] : $query);
    }

////////////////////////////////////////////////////////////////////////////////

    static function stat($what = ''){        if (!self::$connection)
            return array('time' => 0, 'count' => 0);
        $stat = self::$connection->getStatistics();
	    return ($what ? $stat[$what] : $stat);
    }

////////////////////////////////////////////////////////////////////////////////

    static function logger($func){
	    return self::$connection->setLogger($func);
    }

////////////////////////////////////////////////////////////////////////////////

    function schema($table = ''){        if (!$table and $this){
            if ($this->schema)
                return $this->schema;

            $table = $this->table;
        };

        if ($vars = self::vars(inflector::singular($table)))
            $table = ($vars['table'] ? $vars['table'] : $table);

        if (strpos($table, '.'))
            $table = end(explode('.', $table));

        if (!$schema = cache::get('sql/schema/'.$table.'.php'))
            cache::set($schema = $this->build('schema', $table));

        return $schema;
    }

////////////////////////////////////////////////////////////////////////////////

    function childrens($table = 0, $id = 0){        if (!$table and $this){
            list($table, $id) = array($this->table, ($table ? $table : $this->id));
            $primary_key = $this->primary_key;
            $parent_key = $this->parent_key;
        } else {
            $vars = self::vars($table);
            $primary_key = ($vars['primary_key'] ? $vars['primary_key'] : 'id');
            $parent_key = $vars['parent_key'];
        }

        if (!$parent_key)
            return array();
        $array = self::$connection->query(
            $this->build('childrens'),
            $primary_key, $parent_key, $table
        );
        $array = (self::_childrens($array, $id));
        $array[] = -1;

        return $array;
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _childrens($array, $id, $parent = false, $result = array()){
        foreach ($array as $k => $v){
            if ($k == $id or $parent){
                $result[] = $k;
                $result = self::_childrens($v['childNodes'], $id, true, $result);
            } else {
                $result = self::_childrens($v['childNodes'], $id, $parent, $result);
            }
        }

        return $result;
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

    protected static function connect($dsn){
        $pos = strrpos($dsn['database'], '/');
        $host = substr($dsn['database'], 0, $pos);
        $path = substr($dsn['database'], ($pos + 1));

        $dsn = array(
            'scheme' => ($dsn['type'] ? $dsn['type'] : 'mysql'),
            'host' => $host,
            'path' => $path,
            'user' => $dsn['username'],
            'pass' => $dsn['password'],
            'prefix' => $dsn['prefix']
        );

        $class = 'DbSimple_'.ucfirst($dsn['scheme']);
        $class = new $class($dsn);
        $class->setIdentPrefix($class->prefix = $dsn['prefix']);

        if ($class->error)
            $class->link = false;

        return $class;
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
}