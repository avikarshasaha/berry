<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_Query extends SQL_Etc {    protected $query;

////////////////////////////////////////////////////////////////////////////////

    function __construct($query, $placeholders = array()){
        $this->placeholders = $placeholders;
        $this->query = ($placeholders ? self::_placeholders($query) : $query);

        if (self::$cache['logger'])
            $this->log($this->query);
    }

////////////////////////////////////////////////////////////////////////////////

    function exec(){        $time = microtime(true);
        $count = self::$connection['link']->exec($this->query);
        self::$cache['stat']['count'] += 1;
        self::$cache['stat']['time'] += (microtime(true) - $time);

        if ($count === false){
            if (self::$cache['logger']){
                $error = self::last_error();
                $this->log('-- #%d: %s', $error['code'], $error['string']);
            }

            return;
        }

        if (stripos(trim($this->query), 'insert ') === 0){            if (self::$cache['logger']){                $id = self::$connection['link']->lastInsertId();
                $this->log('-- %d row%s, id %d, %f', $count, ($count != 1 ? 's' : ''), $id, (microtime(true) - $time));
            }
        } elseif (self::$cache['logger']){
            $this->log('-- %d row%s, %f', $count, ($count != 1 ? 's' : ''), (microtime(true) - $time));
        }

        return $count;
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch(){
        $time = microtime(true);
        self::$cache['stat']['count'] += 1;

        if (!$query = self::$connection['link']->query($this->query)){
            if (self::$cache['logger']){
                $error = self::last_error();
                $this->log('-- #%d: %s', $error['code'], $error['string']);
            }

            return array();
        }

        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        self::$cache['stat']['time'] += (microtime(true) - $time);

        if (self::$cache['logger']){
            $count = $query->rowCount();
            $this->log('-- %d row%s, %f', $count, ($count != 1 ? 's' : ''), (microtime(true) - $time));
        }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_cell(){
        if (is_array($array = self::fetch_col()))
            return reset($array);

        return $array;
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_col(){
        $array = self::fetch();
        self::_fetch_col($array);

        return ($array ? $array : array());
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function _fetch_col(&$v){        if (!is_array($cell = reset($v)))
            $v = $cell;
        else
            array_walk($v, array('self', '_fetch_col'));
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_row(){
        if ($array = reset(self::fetch()))
            return $array;

        return array();
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_select(){
        return $this->query;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_subquery_select($parent){
        $parent->placeholders = array_merge($this->placeholders, $parent->placeholders);
        return $this->query;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _build_subquery_join($parent){
        return self::_build_subquery_select($parent);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function log(){
        $func = self::$cache['logger'];
        $args = func_get_args();
        $func(func_num_args() > 1 ? call_user_func_array('sprintf', $args) : $args[0]);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function quote($string, $q = ''){
        if (!$q)
            return self::$connection['link']->quote($string);

        if (!$pos = strpos($string, '.'))
            return $q.$string.$q;

        return $q.substr($string, 0, $pos).$q.'.'.$q.substr($string, ($pos + 1)).$q;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _placeholders($query){
        return preg_replace_callback(b::config('lib.sql.placeholders'), array($this, '_placeholdersCallback'), $query);
    }

////////////////////////////////////////////////////////////////////////////////

    protected function _placeholdersCallback($m){
        list($full, $_, $_, $type) = $m;

        if (in_array($full[0].substr($full, -1), array('`', '""', "''"))){
            $quote = $full[0];
            $full = substr($full, 1, -1);
            $type = (string)$full[1];
        } else {
            $quote = '`';
        }

        if ($full[0] != '?')
            return $full;

        $value = array_shift($this->placeholders);
        $parts = array();

        if ($type == 't')
            if (!is_array($value)){
                return $this->quote(self::$connection['prefix'].$value, $quote);
            } else {
                foreach ($value as $v)
                    $parts[] = $this->quote(self::$connection['prefix'].$v, $quote);

                return join(', ', $parts);
            }

        if ($type == 'f')
            if (!is_array($value)){
                return $this->quote($value, $quote);
            } else {
                foreach ($value as $v)
                    $parts[] = $this->quote($v, $quote);

                return join(', ', $parts);
            }

        if ($type == '' and is_numeric($value))
            return (float)$value;

        if ($type == '' and is_string($value))
            return $this->quote($value);

        if ($type == 'd' or ($type == '' and is_int($value)))
            return (int)$value;

        if ($type == 'f' or ($type == '' and is_float($value)))
            return (float)$value;

        if ($type == 'n' or ($type == '' and $value === null))
            return 'null';

        if (!$type and $value instanceof SQL_Raw)
            return $value;

        if (in_array($type, array('a', 'v')) or ($type == '' and is_array($value))){
            $value = (array)$value;

            if ($type == 'v')
                $value = array_values($value);

            foreach ($value as $k => $v){
                if (!is_object($v))
                    $v = ($v === null ? 'null' : $this->quote($v));
                elseif (!$v instanceof SQL_Raw)
                    continue;

                $parts[] = (!is_int($k) ? $this->quote($k, $quote).' = ' : '').$v;
            }

            return join(', ', $parts);
        }

        if ($type == 'k'){
            $value = (array)$value;

            foreach (array_keys($value) as $v)
                $parts[] = $this->quote($v, $quote);

            return join(', ', $parts);
        }

        ob_start();
            var_dump($value);
        $value = ob_get_clean();

        return 'WTF??? (?'.$type.': '.$value.')';
    }

////////////////////////////////////////////////////////////////////////////////
}