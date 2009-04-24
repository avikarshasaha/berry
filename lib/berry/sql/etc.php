<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_etc extends SQL_vars {
////////////////////////////////////////////////////////////////////////////////

    function connect($dsn){        define('DBSIMPLE_SKIP', self::SKIP);
        define('DBSIMPLE_ARRAY_KEY', 'array_key');
        define('DBSIMPLE_PARENT_KEY', 'parent_key');

        $dsn = (array)$dsn;

        $class = new SQL_connect(reset($dsn));
        $class->using = array(key($dsn) => $class->link);
        $class->dsn = $dsn;

        return self::$sql = $class;
    }

////////////////////////////////////////////////////////////////////////////////

    function using($dsn){
        static $last;

        if (!self::$sql->dsn[$dsn])
            return $dsn;

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

    function table($table, $id = 0){        return (class_exists($table, true) ? new $table($id) : new SQL($id, $table));
    }

////////////////////////////////////////////////////////////////////////////////

    function query(){
        $args = func_get_args();
        return call_user_method_array('query', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function is_valid(){
        return (bool)self::$sql->link;
    }

////////////////////////////////////////////////////////////////////////////////

    function last_id($table = ''){
        if (!$table = ((!$table and $this) ? $this->_table : $table))
            return (int)self::$sql->selectCell('select last_insert_id()');

	    $query = self::$sql->selectRow('show table status like "?_"', $table);

	    return max(0, ($query['Auto_increment'] - 1));
    }

////////////////////////////////////////////////////////////////////////////////

    function last_error($what = ''){        if (!$error = self::$sql->error)
            return array();

        unset($error['context']);
        return ($what ? $error[$what] : $stat);
    }

////////////////////////////////////////////////////////////////////////////////

    function last_query($what = ''){
        if (!$query = self::$sql->_lastQuery)
            return array();

        $query = array('query' => $query[0], 'placeholders' => array_slice($query, 1));
        return ($what ? $query[$what] : $query);
    }

////////////////////////////////////////////////////////////////////////////////

    function statistics($what = ''){        if (!$stat = self::$sql->getStatistics())
            return array();

	    return ($what ? $stat[$what] : $stat);
    }

////////////////////////////////////////////////////////////////////////////////

    function logger($func){
	    return self::$sql->setLogger($func);
    }

////////////////////////////////////////////////////////////////////////////////

    function relations($table1, $table2, $type){        if (is_array($table2)){
            $key = key($table2);

            if (!is_numeric($key))
                list($table2, $keys) = array($key, reset($table2));
            else
                $table2 = reset($table2);
        }
        $vars1 = get_class_vars($table1);
        $vars2 = get_class_vars($table2);

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

        if ($type == 'has_one'){            $local['field'] = $foreign['table'].'_'.$local['field'];
            $local['table'] = ($vars1['table'] ? $vars1['table'] : $local['table']);
            $foreign['table'] = ($vars2['table'] ? $vars2['table'] : $foreign['table']);
        }

        if (in_array($type, array('belongs_to', 'has_many'))){            $foreign['field'] = $local['alias'].'_'.$foreign['field'];
            $local['table'] = ($vars1['table'] ? $vars1['table'] : $local['table']);
            $foreign['table'] = ($vars2['table'] ? $vars2['table'] : $foreign['table']);
        }

        if ($type == 'has_and_belongs_to_many'){
            $foreign['table1'] = ($table1 < $table2 ? $table1.'_'.$table2 : $table2.'_'.$table1);
            $foreign['alias1'] = $foreign['table1'];
            $foreign['field1'] = $local['table'].'_'.$foreign['field'];

            $foreign['table2'] = ($vars2['table'] ? $vars2['table'] : $foreign['table']);
            $foreign['alias2'] = $foreign['table'];
            $foreign['field2'] = $foreign['field'];

            $foreign['field3'] = $foreign['alias2'].'_'.$foreign['field'];

            unset($foreign['table'], $foreign['alias'], $foreign['field']);
        } elseif ($keys){
            list($local['field'], $foreign['field']) = $keys;
        }

        return compact('local', 'type', 'foreign', 'table');
    }

////////////////////////////////////////////////////////////////////////////////

    function schema($table = ''){        $table = ((!$table and $this) ? $this->_table : $table);        $schema = array();
        $keys = array('p' => 'p', 'u' => 'u', 'm' => 'i');

        foreach (self::$sql->query('desc ['.$table.']') as $info)
            $schema[$info['Field']] = array(
                'name' => $info['Field'],
                'type' => $info['Type'],
                'null' => ($info['Null'] == 'YES'),
                'key'  => $keys[strtolower($info['Key'][0])],
                'auto' => ($info['Extra'] == 'auto_increment'),
                'default' => $info['Default']
            );

        return $schema;
    }

////////////////////////////////////////////////////////////////////////////////

    function raw($raw){        return new SQL_raw($raw);    }

////////////////////////////////////////////////////////////////////////////////

    function children($table, $id = 0){        if (!$table and $this){
            list($table, $id) = array($this->_table, $table);
            $primary_key = $this->primary_key;
            $parent_key = $this->parent_key;
        } else {
            $vars = get_class_vars($table);
            $primary_key = ($vars['primary_key'] ? $vars['primary_key'] : 'id');
            $parent_key = $vars['parent_key'];
        }

        if (!$parent_key)
            return array();
        $array = self::$sql->query('select ?# as array_key, ?# as parent_key from ?_', $primary_key, $parent_key, $table);
        $array = (self::_children($array, $id));
        $array[] = -1;

        return $array;
    }

////////////////////////////////////////////////////////////////////////////////

    private function _children($array, $id, $parent = false, $result = array()){
        foreach ($array as $k => $v){
            if ($k == $id or $parent){
                $result[] = $k;
                $result = self::_children($v['childNodes'], $id, true, $result);
            } else {
                $result = self::_children($v['childNodes'], $id, $parent, $result);
            }
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////
}