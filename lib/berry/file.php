<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class File {
////////////////////////////////////////////////////////////////////////////////

    static function path($filename){
        if (b::$path[1] and file_exists($path = b::$path[1].'/'.$filename))
            return $path;

        return b::$path[0].'/'.$filename;
    }
////////////////////////////////////////////////////////////////////////////////

    static function mkdir($path){
        if (b::is_windows())
            $path = str_replace('/', '\\', $path);

        // проверитиь на символьных и жёстких ссылках
        if (is_dir($path))
            return true;

        $umask = umask(0);
        $result = mkdir($path, 0777, true);

        umask($umask);
        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    static function rmdir($filename){
        foreach (self::dir($filename, true) as $file => $iter){
            self::chmod($file);
            ($iter->isDir() ? rmdir($file) : unlink($file));
        }

        self::chmod($filename);
        return rmdir($filename);
    }

////////////////////////////////////////////////////////////////////////////////

    static function glob(){
        $args = func_get_args();
        $files = array();

        if (is_int(end($args)))
            $flag = array_pop($args);

        foreach ($args as $pattern)
            $files = array_merge($files, (array)call_user_func_array('glob', array($pattern, $flag)));

        return $files;
    }

////////////////////////////////////////////////////////////////////////////////

    // http://www.php.net/manual/ru/function.symlink.php#74464
    static function link($target, $link){        self::chmod(dirname($link));

        if (!b::is_windows())
            return link($target, $link);

        $target = str_replace('/', '\\', $target);
        $link = str_replace('/', '\\', $link);
        $key = (is_dir($target) ? 'D' : 'H');

        return (bool)exec(sprintf('mklink /%s "%s" "%s"', $key, $link, $target));
    }

////////////////////////////////////////////////////////////////////////////////

    static function chmod($filename){
        if (is_writable($filename))
            return true;

        $umask = umask(0);
        $result = chmod($filename, 0777);

        umask($umask);
        return $result;
    }

////////////////////////////////////////////////////////////////////////////////

    static function dir($filename, $child_first = false){
        $map = array(
            RecursiveIteratorIterator::SELF_FIRST
            RecursiveIteratorIterator::CHILD_FIRST
        );

        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($filename), $map[(bool)$child_first]
        );
    }

////////////////////////////////////////////////////////////////////////////////
}