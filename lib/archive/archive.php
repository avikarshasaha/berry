<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Archive {
////////////////////////////////////////////////////////////////////////////////

    function __construct($filename){
        $this->filename = $filename;
        $this->ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($this->ext == 'zip'){            $this->type = $this->ext;            $this->object = new ZIP;
        } elseif (in_array($this->ext, array('tar', 'bz', 'bz2', 'gz', 'tgz'))){
            $this->type = 'tar';
            $this->object = new TAR;
        } elseif ($this->ext == 'rar'){
            //$this->type = $this->ext;
            //$this->object = new RAR;
        }
    }

////////////////////////////////////////////////////////////////////////////////

    function is_valid(){        return isset($this->object);    }

////////////////////////////////////////////////////////////////////////////////

    function compact($files, $dinamic = false){        foreach ($files as $k => $v)
            $_files[] = ((is_int($k) and substr($v, -1) == '/') ? array($v) : array($k, $v));

        if ($this->type == 'zip'){
            $this->_add($_files, extension_loaded('zlib'));
            $result = $this->_get_file();
        } elseif ($this->type == 'tar'){
            $this->_add($_files);
            $result = $this->_getDynamicArchive();
        }

        if ($dinamic)
            return $result;

        return file_put_contents($this->filename, $result);
    }

////////////////////////////////////////////////////////////////////////////////

    function files(){
        if ($this->type == 'zip'){
            return $this->_get_list($this->filename);
         } elseif ($this->type == 'tar'){
             $this->_setArchive($this->filename);
             return $this->_listContents();
         } elseif ($this->type == 'rar'){
         }
    }

////////////////////////////////////////////////////////////////////////////////

    function extract($target = './', $indexes = array(-1)){
        if ($this->type == 'zip'){
            return $this->_extract($this->filename, $target, $indexes);
        } elseif ($this->type == 'tar'){            $this->_setArchive($this->filename);
            return $this->_extract($indexes, $target);
        }
    }

////////////////////////////////////////////////////////////////////////////////

    protected function __call($method, $args){
        if (extension_loaded('mbstring') and ($encoding = mb_internal_encoding()))
            mb_internal_encoding('ASCII');

        $result = call_user_func_array(array($this->object, substr($method, 1)), $args);

        if ($encoding)
            mb_internal_encoding($encoding);

        return $result;
    }

////////////////////////////////////////////////////////////////////////////////
}