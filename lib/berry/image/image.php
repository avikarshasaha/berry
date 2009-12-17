<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Image extends Image_Merge {
////////////////////////////////////////////////////////////////////////////////

    function __construct($filename){        $info = pathinfo($filename);
        $this->file = array(
            'path' => $filename,
            'dir'  => $info['dirname'],
            'name' => $info['filename'],
            'ext'  => $info['extension'],
            'size' => filesize($filename),
            'hsize' => int::size(filesize($filename), array('b' => 'b', 'kb' => 'KB','mb' => 'MB')),
        );

        $info = getimagesize($filename);
        $channels = array(3 => 'RGB', 'CMYK');
        $this->file += array(
            'width'  => $info[0],
            'height' => $info[1],
            'bits' => $info['bits'],
            'mime' => $info['mime'],
            'type' => substr(strstr($info['mime'], '/'), 1),
            'channels' => $channels[$info['channels']]
        );

        $func = 'imagecreatefrom'.$this->file['type'];
        $this->im = $func($filename);
    }

////////////////////////////////////////////////////////////////////////////////

    function save($filename, $quality = 80){        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $file = str::format($filename, $this->file);
        $map = array(
            'png' => 'png',
            'gif' => 'gif',
            'jpg' => 'jpeg',
            'jpeg' => 'jpeg',
        );

        if ($quality < 0)
            $quality = 10;

        if ($quality and $ext == 'png')            $quality = round(($quality > 0 ? $quality : 10) / -10 + 10);

        if (b::call('*image'.$map[$ext], array($this->im, $file, $quality)))
            return $file;    }

////////////////////////////////////////////////////////////////////////////////

    function resize($width, $height){        list($_width, $_height) = array(imagesx($this->im), imagesy($this->im));

        if (substr($width, -1) == '%')
            $width = (($_width / 100) * substr($width, 0, -1));
        if (substr($height, -1) == '%')
            $height = (($_height / 100) * substr($height, 0, -1));        if (!$width)
            $width = ($_width / ($_height / $height));
        if (!$height)
            $height = ($_height / ($_width / $width));

        list($im, $this->im) = array($this->im, imagecreatetruecolor($width, $height));
        imagecopyresized($this->im, $im, 0, 0, 0, 0, $width, $height, $_width, $_height);
        imagedestroy($im);

        $this->file['resize'] = compact('width', 'height');

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function merge($filename, $params = array()){
        if (is_resource($filename)){
            $merge = new parent($this->im, $filename);
        } else {
            $image = new self($filename);
            $merge = new parent($this->im, $image->im);
        }

        foreach ($params as $k => $v)
            if (method_exists($merge, $k))
                call_user_method($k, $merge, $v);

        unset($merge);
        unset($image);
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function text($text, $params = array()){        $text = str::format($text, $this->file);
        $size = 2;

        if ($params['font'])
            foreach (explode(' ', $params['font']) as $v){
                $v = trim($v);

                if ($v[0] == '#')
                    $color = $v;
                elseif (is_numeric($v))
                    $size = (int)$v;
            }
        $width = (b::len($text) * imagefontwidth($size));
        $height = imagefontheight($size);

        $im = imagecreatetruecolor($width, $height);
        imagefill($im, 0, 0, -1);
        imagestring($im, $size, 0, 0, $text, self::color($color, $im));

        self::merge($im, $params);
        imagedestroy($im);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function close(){        is_resource($this->im) and imagedestroy($this->im);    }

////////////////////////////////////////////////////////////////////////////////

    function __destruct(){        $this->close();    }

////////////////////////////////////////////////////////////////////////////////
}