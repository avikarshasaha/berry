<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Debug {
////////////////////////////////////////////////////////////////////////////////

    static function print_r($var, $return = false){
        $echo = print_r($var, true);
        return highlight_string($echo, $return);
    }
////////////////////////////////////////////////////////////////////////////////
    static function timer($id = 'main'){
        static $timer;

        if (!$timer[$id])
            return $timer[$id] = microtime(true);

        return (microtime(true) - $timer[$id]);
    }////////////////////////////////////////////////////////////////////////////////

    static function info($save = true){
        $file = file::path('tmp').'/debug/info.log';

        if (!$save)
            return file_get_contents($file);

        $array = array(
            'pgt' => str_replace(',', '.', round(self::timer(), 3)),
            'sql' => array(
                'time'  => str_replace(',', '.', round(sql::statistics('time'), 3)),
                'count' => sql::statistics('count')
            ),
            'memory' => array(
                'limit' => int::size(int::bytes(ini_get('memory_limit'))),
                'usage' => (function_exists('memory_get_usage') ? int::size(memory_get_usage()) : 'N/A'),
                'peak'  => (function_exists('memory_get_peak_usage') ? int::size(memory_get_peak_usage()) : 'N/A')
            ),
            'check' => array(
                'php'   => (phpversion() >= '5.2.0' ? phpversion() : false),
                'mysql' => (b::call('mysql_get_server_info') >= '4.1' ? mysql_get_server_info() : false),
                'pcre'  => extension_loaded('pcre'),
                'tokenizer' => extension_loaded('tokenizer')
            )
        );

        file::mkdir(dirname($file));

        $fp = fopen($file, 'w+');
        fwrite($fp, b::q(0, 0));
        fwrite($fp, "\r\n\r\n");
        fwrite($fp, yaml::dump($array));
        fclose($fp);

        return file_get_contents($file);
    }

////////////////////////////////////////////////////////////////////////////////

    static function sql($object = null, $string = ''){
        static $fp;

        $file = file::path('tmp').'/debug/sql.log';

        if (!is_object($object))
            return file_get_contents($file);

        if (!$fp){            file::mkdir(dirname($file));
            $fp = fopen($file, 'w+');
        }

        fwrite($fp, ($string = trim($string)));

        if (substr($string, 0, 3) != '-- '){
            fwrite($fp, ("\r\n\r\n"));
        } else {
            fwrite($fp, ("\r\n\r\n"));
        	fwrite($fp, (str_repeat('=', 80)));
        	fwrite($fp, ("\r\n\r\n"));
    	}
    }

////////////////////////////////////////////////////////////////////////////////
}