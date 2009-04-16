<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    GNU GPL 2 <http://gnu.org/licenses/gpl-2.0.txt>       / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_connect extends DbSimple_Mysql {
////////////////////////////////////////////////////////////////////////////////

    function __construct($dsn){        $dsn = DbSimple_Generic::parseDSN($dsn);

        $this->setIdentPrefix($this->prefix = $dsn['prefix']);        parent::__construct($dsn);

        if ($this->error){
            $this->link = false;
        } else {
            $this->_statistics['count']--;
            $this->query('set names utf8');
        }    }

////////////////////////////////////////////////////////////////////////////////
    function _query($query, &$total){        foreach ($query as $k => $v)
            if (is_array($v)){
                foreach ($v as $k2 => $v2)
                    $query[$k][$k2] = self::_toBerry($v2, false);
            } else {
                $query[$k] = ((is_object($v) or $v === SQL::SKIP) ? $v : self::_toBerry($v));
            }

        return parent::_query($query, $total);    }

////////////////////////////////////////////////////////////////////////////////

    function prefix($table, $q = '`'){        if (!$pos = strpos($table, '.'))
            return $q.$this->prefix.$table.$q;
        return $q.substr($table, 0, $pos).$q.'.'.$q.substr($table, ($pos + 1)).$q;
    }

////////////////////////////////////////////////////////////////////////////////

    private function _toBerry($query, $use_prefix = true){
        $withas = (strtolower(substr($query, 0, 6)) == 'select' or strtolower(substr($query, 0, 6)) == 'update');

        if (!$use_prefix)
            return $query;

        if ($withas){            $query = preg_replace('/\[(\w+\.)(\w+)\](?!\s+as )/i', '\\1\\2 as `\\2`', $query);
            $query = preg_replace('/\[(\w+)\](?!\s+as )/ie', "self::prefix('\\1').' as `\\1`'", $query);

            $query = preg_replace('/\[(\w+\.)(\w+)\]/i', '\\1`\\2`', $query);
            $query = preg_replace('/\[(\w+)\]/ie', "self::prefix('\\1')", $query);
        } else {
            $query = preg_replace('/\[(\w+\.)(\w+)\]/i', '\\1`\\2`', $query);
            $query = preg_replace('/\[(\w+)\]/e', "self::prefix('\\1')", $query);
        }

        return $query;
    }

////////////////////////////////////////////////////////////////////////////////

    function _expandPlaceholdersCallback($m){        if ($m[0] == '`?_`' or $m[0] == '"?_"' or $m[0] == "'?_'"){            $quote = $m[0][0];            $m[0] = $m[2] = '?_';
            $m[3] = $m[0][1];        } elseif ($m[0] == '?_'){            $quote = '`';        }

        if (!$m[2] or !$this->_placeholderArgs or !in_array($m[3], array('', '_', 'a')))
            return parent::_expandPlaceholdersCallback($m);

        if (!($value = array_pop($this->_placeholderArgs)) or $value === SQL::SKIP)
            $this->_placeholderNoValueFound = true;

        if ($m[3] == '_')
            return (!is_scalar($value) ? 'DBSIMPLE_ERROR_VALUE_NOT_SCALAR' : $this->prefix($value, $quote));

        if (!$m[3]){
            if ($value === null)
                return 'null';
            elseif (!is_object($value) and !is_scalar($value))
                return 'DBSIMPLE_ERROR_VALUE_NOT_SCALAR';

            return (is_object($value) ? (string)$value : $this->escape($value));
        }

        if (!is_array($value))
            return 'DBSIMPLE_ERROR_VALUE_NOT_ARRAY';

        $parts = array();

        foreach ($value as $k => $v){
            $v = ($v === null ? 'null' : (is_object($v) ? $v : $this->escape($v)));
            $parts[] = (!is_int($k) ? $this->escape($k, true).' = ' : '').$v;
        }

        return join(', ', $parts);
    }

////////////////////////////////////////////////////////////////////////////////
}