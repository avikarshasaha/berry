<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_Wrap extends DbSimple_Generic_Database {    protected $q;

////////////////////////////////////////////////////////////////////////////////

    function prefix($table, $q = ''){        $q = ($q ? $q : $this->q);

        if (!$pos = strpos($table, '.'))
            return $q.$this->prefix.$table.$q;

        return $q.substr($table, 0, $pos).$q.'.'.$q.substr($table, ($pos + 1)).$q;
    }

////////////////////////////////////////////////////////////////////////////////

    function _expandPlaceholdersCallback($m){
        if ($m[0] == '`?_`' or $m[0] == '"?_"' or $m[0] == "'?_'"){
            $quote = $m[0][0];
            $m[0] = $m[2] = '?_';
            $m[3] = $m[0][1];
        } elseif ($m[0] == '?_'){
            $quote = $this->q;
        }

        if (!$m[2] or !$this->_placeholderArgs or !in_array($m[3], array('', '_', 'a', 'k', 'v')))
            return parent::_expandPlaceholdersCallback($m);

        if (!($value = array_pop($this->_placeholderArgs)) or $value === DBSIMPLE_SKIP)
            $this->_placeholderNoValueFound = true;

        if ($m[3] == '_'){
            if (!is_array($value)){
                return $this->prefix($value, $quote);
            } else {
                $tmp = array();

                foreach ($value as $table)
                    $tmp[] = $this->prefix($table, $quote);

                return join(', ', $tmp);
            }
        } elseif ($m[3] == 'k' or $m[3] == 'v'){            if (!is_array($value))
                return 'DBSIMPLE_ERROR_VALUE_NOT_ARRAY';

            $tmp = array();

            foreach ($value as $k => $v)
                $tmp[] = ($m[3] == 'k' ? $this->escape($k, true) : $this->escape($v));

            return join(', ', $tmp);        }

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

        foreach ($value as $k => $v){
            if (!is_object($v))
                $v = ($v === null ? 'null' : $this->escape($v));
            elseif (!$v instanceof SQL_Raw)
                continue;

            $parts[] = (!is_int($k) ? $this->escape($k, true).' = ' : '').$v;
        }

        return join(', ', $parts);
    }

////////////////////////////////////////////////////////////////////////////////

}