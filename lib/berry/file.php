<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru>                             | ~ )\
                                                           /__/\ \____
    Лёха zloy и красивый <http://lexa.cutenews.ru>         /   \_/    \
    LGPL <http://www.gnu.org/licenses/lgpl.txt>           / <_ ____,_-/\ __
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
}