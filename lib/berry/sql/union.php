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

////////////////////////////////////////////////////////////////////////////////

    function __construct($array){        foreach ($array as $object){
            $this->union[] = $object->build('select');
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
        $this->offset(max(0, ($page * $this->limit - $this->limit)));
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch(){        $key = self::hash('fetch');

        if (array_key_exists($key, self::$cache))
            return self::$cache[$key];

        $args = $this->placeholders;
        array_unshift($args, self::build('select'));
        return self::$cache[$key] = call_user_method_array('select', self::$sql, $args);
    }

////////////////////////////////////////////////////////////////////////////////

    function fetch_array(){        return arr::assoc(self::fetch());    }

////////////////////////////////////////////////////////////////////////////////
}