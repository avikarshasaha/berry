<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_etc extends SQL_vars {
////////////////////////////////////////////////////////////////////////////////

    function connect($dsn){
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

    function table($table, $id = 0){
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

    function last_error($what = ''){
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

    function statistics($what = ''){
            return array();

	    return ($what ? $stat[$what] : $stat);
    }

////////////////////////////////////////////////////////////////////////////////

    function logger($func){
	    return self::$sql->setLogger($func);
    }

////////////////////////////////////////////////////////////////////////////////

    function relations($table1, $table2, $type){
            $key = key($table2);

            if (!is_numeric($key))
                list($table2, $keys) = array($key, reset($table2));
            else
                $table2 = reset($table2);
        }

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

        if ($type == 'has_one'){
            $local['table'] = ($vars1['table'] ? $vars1['table'] : $local['table']);
            $foreign['table'] = ($vars2['table'] ? $vars2['table'] : $foreign['table']);
        }

        if (in_array($type, array('belongs_to', 'has_many'))){
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

    function schema($table = ''){
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

    function raw($raw){

////////////////////////////////////////////////////////////////////////////////
