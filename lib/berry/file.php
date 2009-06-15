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

    function path($filename){
        if (b::$path[1] and file_exists($path = b::$path[1].'/'.$filename))
            return $path;

        return b::$path[0].'/'.$filename;
    }
////////////////////////////////////////////////////////////////////////////////

    function mkdir($path){
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

    function rmdir($filename){
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($filename),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file => $iter){
            self::chmod($file);
            ($iter->isDir() ? rmdir($file) : unlink($file));
        }

        self::chmod($filename);
        return rmdir($filename);
    }

////////////////////////////////////////////////////////////////////////////////

    function glob(){
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
    function link($target, $link){
        if (b::is_windows())
            return exec('mklink '.(is_dir($target) ? '/D' : '').' "'.str_replace('/', '\\', $link).'" "'.str_replace('/', '\\', $target).'"');

        return link($target, $link);
    }

////////////////////////////////////////////////////////////////////////////////

    function chmod($filename){
        if (is_writable($filename))
            return true;

        $umask = umask(0);
        $result = chmod($filename, 0777);

        umask($umask);
        return $result;
    }

////////////////////////////////////////////////////////////////////////////////
}