<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Session {
    protected static $config;
    protected static $cache = array();

////////////////////////////////////////////////////////////////////////////////

    static function init(){        $class = new self;
        ini_set('session.save_handler', 'user');
        session_set_save_handler(
            array($class, 'open'),
            array($class, 'close'),
            array($class, 'read'),
            array($class, 'write'),
            array($class, 'destroy'),
            array($class, 'gc')
        );
        session_start();
    }

////////////////////////////////////////////////////////////////////////////////

    static function open($path, $name){        self::$config = b::config('lib.session');
        return true;
    }

////////////////////////////////////////////////////////////////////////////////

    static function close(){
        if (!$divisor = ini_get('session.gc_divisor'))
            $divisor = 100;

        if (!$probability = ini_get('session.gc_probability'))
            $probability = 1;

        $array = range($probability, ($probability * 2 - 1));
        $value = arr::rand(range(1, $divisor));

        if (in_array($value, $array))
            self::gc(ini_get('session.gc_maxlifetime'));
        return true;
    }

////////////////////////////////////////////////////////////////////////////////

    static function read($id){        self::_cache($id);
        if (self::$cache[$id] === false)
            return false;
        return self::$cache[$id]['data'];
    }

////////////////////////////////////////////////////////////////////////////////

    static function write($id, $data){        self::_cache($id);

        if (self::$cache[$id] === false or self::$cache[$id]['data'] == $data)
            return false;

        $table = sql::table(self::$config['table'], $id);

        if (!$table->exists()){
            $table = sql::table(self::$config['table']);
            $table->id = $id;
        }

        $table->data = $data;
        $table->ip = $_SERVER['REMOTE_ADDR'];
        $table->user_agent = $_SERVER['HTTP_USER_AGENT'];

        return ($table->save() !== null);
    }

////////////////////////////////////////////////////////////////////////////////

    static function destroy($id){        unset(self::$cache[$id]);
        return (sql::table(self::$config['table'], $id)->delete() !== null);
    }

////////////////////////////////////////////////////////////////////////////////

    static function gc($lifetime){        $table = sql::table(self::$config['table']);
        $table->where('timestamp < ?d', (time() - $lifetime));
        $table->limit(25);

        return ($table->delete() !== null);
    }

////////////////////////////////////////////////////////////////////////////////

    static protected function _cache($id){        if (isset(self::$cache[$id]))
            return;
        $table = sql::table(self::$config['table'], $id);
        $table->select('data', 'ip', 'user_agent', 'timestamp');

        if (self::$cache[$id] = $table->fetch_row()){
            $ip = (self::$config['check']['ip'] and self::$cache[$id]['ip'] != $_SERVER['REMOTE_ADDR']);
            $user_agent = (self::$config['check']['user_agent'] and self::$cache[$id]['user_agent'] != $_SERVER['HTTP_USER_AGENT']);

            if ($ip or $user_agent)
                self::$cache[$id] = false;
        }    }

////////////////////////////////////////////////////////////////////////////////
}