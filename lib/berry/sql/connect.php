<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_Connect extends DbSimple_Mysql {
////////////////////////////////////////////////////////////////////////////////

    function __construct($dsn){        define('DBSIMPLE_SKIP', log(0));
        define('DBSIMPLE_ARRAY_KEY', 'array_key');
        define('DBSIMPLE_PARENT_KEY', 'parent_key');
        if (is_array($dsn)){
            $pos = strrpos($dsn['database'], '/');
            $host = substr($dsn['database'], 0, $pos);
            $path = substr($dsn['database'], ($pos + 1));
            $dsn = array(
                'scheme' => 'mysql',
                'host' => $host,
                'path' => $path,
                'user' => $dsn['username'],
                'pass' => $dsn['password'],
                'prefix' => $dsn['prefix'],
            );
        }
        $dsn = DbSimple_Generic::parseDSN($dsn);
        $this->setIdentPrefix($this->prefix = $dsn['prefix']);        parent::__construct($dsn);

        if ($this->error){
            $this->link = false;
        } else {
            $this->_statistics['count']--;
            $this->query('set names utf8');
        }    }

////////////////////////////////////////////////////////////////////////////////

    function prefix($table, $q = '`'){        if (!$pos = strpos($table, '.'))
            return $q.$this->prefix.$table.$q;
        return $q.substr($table, 0, $pos).$q.'.'.$q.substr($table, ($pos + 1)).$q;
    }

////////////////////////////////////////////////////////////////////////////////

    function _expandPlaceholdersCallback($m){        if ($m[0] == '`?_`' or $m[0] == '"?_"' or $m[0] == "'?_'"){            $quote = $m[0][0];            $m[0] = $m[2] = '?_';
            $m[3] = $m[0][1];        } elseif ($m[0] == '?_'){            $quote = '`';        }

        if (!$m[2] or !$this->_placeholderArgs or !in_array($m[3], array('', '_', 'a')))
            return parent::_expandPlaceholdersCallback($m);

        if (!($value = array_pop($this->_placeholderArgs)) or $value === DBSIMPLE_SKIP)
            $this->_placeholderNoValueFound = true;

        if ($m[3] == '_')
            if (!is_array($value)){                return $this->prefix($value, $quote);
            } else {
                $tmp = array();

                foreach ($value as $table)
                    $tmp[] = $this->prefix($table, $quote);

                return join(', ', $tmp);
            }

        if (!$m[3]){
            if ($value === null)
                return 'null';
            elseif (!$value instanceof SQL_Raw and !is_scalar($value))
                return 'DBSIMPLE_ERROR_VALUE_NOT_SCALAR';

            return (is_object($value) ? (string)$value : $this->escape($value));
        }

        if (!is_array($value))
            return 'DBSIMPLE_ERROR_VALUE_NOT_ARRAY';

        $parts = array();

        foreach ($value as $k => $v){            if (!is_object($v))
                $v = ($v === null ? 'null' : $this->escape($v));
            elseif (!$v instanceof SQL_Raw)
                continue;

            $parts[] = (!is_int($k) ? $this->escape($k, true).' = ' : '').$v;
        }

        return join(', ', $parts);
    }

////////////////////////////////////////////////////////////////////////////////

    function _performTransaction(){
        $this->query('SET AUTOCOMMIT = 0');
        return $this->query('BEGIN');
    }

////////////////////////////////////////////////////////////////////////////////

    function _performCommit(){
        $result = $this->query('COMMIT');
        $this->query('SET AUTOCOMMIT = 1');
        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function _performRollback(){
        $result = $this->query('ROLLBACK');
        $this->query('SET AUTOCOMMIT = 1');
        return $result;
    }

////////////////////////////////////////////////////////////////////////////////
}