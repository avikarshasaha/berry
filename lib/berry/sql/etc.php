<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
abstract class SQL_etc extends SQL_build {    const SKIP = 7.2e83;

    protected $id;
    protected $table;
    protected $_table;

    protected $primary_key = 'id';
    protected $parent_key;

    protected $has_one = array();
    protected $belongs_to = array();
    protected $has_many = array();
    protected $has_and_belongs_to_many = array();

    protected $schema = array();
    protected $trigger = array();

    protected $into = array();
    protected $values = array();
    protected $joinvalues = array();

    protected $select = array('*');
    protected $from = array();
    protected $join = array();

    protected $where = array();
    protected $group_by = array();
    protected $having = array();
    protected $order_by = array();
    protected $limit = 0;
    protected $offset = 0;
    protected $union = array();

    protected $multiple = array();
    protected $relations = array();
    protected $placeholders = array();
    protected $multisave = array();
    protected $iterator = array();

    protected static $sql;
    protected static $cache = array();
////////////////////////////////////////////////////////////////////////////////

    static function connect($dsn){        define('DBSIMPLE_SKIP', self::SKIP);
        define('DBSIMPLE_ARRAY_KEY', 'array_key');
        define('DBSIMPLE_PARENT_KEY', 'parent_key');

        $dsn = (array)$dsn;

        $class = new SQL_connect(reset($dsn));
        $class->using = array(key($dsn) => $class->link);
        $class->dsn = $dsn;

        return self::$sql = $class;
    }

////////////////////////////////////////////////////////////////////////////////

    static function using($dsn = ''){
        static $last;

        if (!$dsn)
            return key(self::$sql->using);

        if (!self::$sql->dsn[$dsn])
            return;

        if (!$current = $last)
            $current = key(self::$sql->using);

        if (!self::$sql->using[$dsn]){
            $class = new SQL_connect(self::$sql->dsn[$dsn]);
            self::$sql->using[$dsn] = $class->link;
            self::$sql->_statistics['time'] += $class->_statistics['time'];
        }

        $last = $dsn;
        self::$sql->link = self::$sql->using[$dsn];

        return $current;
    }

////////////////////////////////////////////////////////////////////////////////

    static function table($table, $id = 0){        return (class_exists($table, true) ? new $table($id) : new SQL($id, $table));
    }

////////////////////////////////////////////////////////////////////////////////

    static function query(){
        $args = func_get_args();
        return call_user_method_array('query', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    static function is_valid(){
        return (bool)self::$sql->link;
    }

////////////////////////////////////////////////////////////////////////////////

    static function link(){
        return self::$sql->link;
    }

////////////////////////////////////////////////////////////////////////////////

    function last_id($table = ''){
        if (!$table = ((!$table and $this) ? $this->_table : $table))
            return (int)self::$sql->selectCell('select last_insert_id()');

	    $query = self::$sql->selectRow('show table status like "?_"', $table);

	    return max(0, ($query['Auto_increment'] - 1));
    }

////////////////////////////////////////////////////////////////////////////////

    static function last_error($what = ''){        if (!$error = self::$sql->error)
            return array();

        unset($error['context']);
        return ($what ? $error[$what] : $stat);
    }

////////////////////////////////////////////////////////////////////////////////

    static function last_query($what = ''){
        if (!$query = self::$sql->_lastQuery)
            return array();

        $query = array('query' => $query[0], 'placeholders' => array_slice($query, 1));
        return ($what ? $query[$what] : $query);
    }

////////////////////////////////////////////////////////////////////////////////

    static function statistics($what = ''){        if (!$stat = self::$sql->getStatistics())
            return array();

	    return ($what ? $stat[$what] : $stat);
    }

////////////////////////////////////////////////////////////////////////////////

    static function logger($func){
	    return self::$sql->setLogger($func);
    }

////////////////////////////////////////////////////////////////////////////////

    function schema($table = ''){        if (!$table and $this){
            if ($this->schema)
                return $this->schema;

            $table = $this->_table;
        };

        if (!$schema = cache::get('schema/'.$table.'.php', array('db' => $table))){            $schema = array();
            $keys = array('p' => 'p', 'u' => 'u', 'm' => 'i');

            foreach (self::$sql->query(self::build('schema'), $table) as $info)
                $schema[$info['Field']] = array(
                    'name' => $info['Field'],
                    'type' => $info['Type'],
                    'null' => ($info['Null'] == 'YES'),
                    'key'  => (string)$keys[strtolower($info['Key'][0])],
                    'auto' => ($info['Extra'] == 'auto_increment'),
                    'default' => (string)$info['Default']
                );

            cache::set($schema);        }

        return $schema;
    }

////////////////////////////////////////////////////////////////////////////////

    static function raw($raw){        return new SQL_raw($raw);    }

////////////////////////////////////////////////////////////////////////////////

    function childrens($table = 0, $id = 0){        if (!$table and $this){
            list($table, $id) = array($this->_table, ($table ? $table : $this->id));
            $primary_key = $this->primary_key;
            $parent_key = $this->parent_key;
        } else {
            $vars = get_class_vars($table);
            $primary_key = ($vars['primary_key'] ? $vars['primary_key'] : 'id');
            $parent_key = $vars['parent_key'];
        }

        if (!$parent_key)
            return array();
        $array = self::$sql->query(
            self::build('childrens'),
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

        return call_user_func_array(array(($this ? $this : 'self'), '_build_'.$type), $args);
    }

////////////////////////////////////////////////////////////////////////////////

    static function union(){
        $union = func_get_args();
        return new SQL_union($union);
    }

////////////////////////////////////////////////////////////////////////////////

    function __call($method, $params){        if (!$trigger = $this->trigger[$method])
            trigger_error(sprintf('Call to undefined method %s::%s()', get_class($this), $method), E_USER_ERROR);

        foreach ($trigger as $k => $v)
            if (call_user_method_array($k, $this, array_merge((array)$v)))
                $this->placeholders = array_merge($this->placeholders, $params);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function hash($prefix = ''){        // Заебался. То тут не то, то там не так.        return $this->table.'['.$prefix.']'.spl_object_hash($this);
        ob_start();
            var_dump($this);
        return $prefix.'_'.md5(ob_get_clean());
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function relations($table1, $table2, $type){
        if (is_array($table2)){
            $key = key($table2);

            if (!is_numeric($key))
                list($table2, $keys) = array($key, reset($table2));
            else
                $table2 = reset($table2);
        }

        $vars1 = get_class_vars(inflector::singular($table1));
        $vars2 = get_class_vars(inflector::singular($table2));

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
            $foreign['table'] = ($vars2['table'] ? $vars2['table'] : inflector::plural($foreign['table']));
        }

        if ($type == 'belongs_to'){
            $foreign['field'] = $local['alias'].'_'.$foreign['field'];
            $local['table'] = ($vars1['table'] ? $vars1['table'] : inflector::plural($local['table']));
            $foreign['table'] = ($vars2['table'] ? $vars2['table'] : inflector::plural($foreign['table']));
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
}