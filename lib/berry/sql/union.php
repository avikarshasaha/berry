<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class SQL_Union extends SQL_Etc {    protected $table = '<union>';
    protected $select = array();

////////////////////////////////////////////////////////////////////////////////

    function __construct($array){        foreach ($array as $object){
            $this->union[] = $object->build('get');
            $this->placeholders = array_merge($this->placeholders, $object->placeholders);
        }
    }

////////////////////////////////////////////////////////////////////////////////

    function order_by(){
        foreach (func_get_args() as $arg)
            $this->order_by[] = ($arg[0] == '-' ? substr($arg, 1).' desc' : $arg);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function limit($limit){
        $this->limit = max(0, $limit);
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function offset($offset){
        $this->offset = (int)$offset;
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function page($page){
        $this->offset(max(0, $page * $this->limit - $this->limit));
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function get(){        $key = self::hash('get');

        if (array_key_exists($key, self::$cache))
            return self::$cache[$key];

        $args = $this->placeholders;
        array_unshift($args, self::build('get'));
        return self::$cache[$key] = call_user_method_array('select', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function as_array(){        return arr::assoc(self::get());    }

////////////////////////////////////////////////////////////////////////////////

    function as_object(){
        $result = array();

        foreach (self::as_array() as $id => $row)
            foreach ($row as $k => $v)
                if (is_array($v[0])){
                    foreach ($v as $i => $value)
                        $result[$id]->{$k}[$i] = (object)$value;
                } else {
                    $result[$id]->$k = (is_array($v) ? (object)$v : $v);
                }

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////
}