<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Image {
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

    function save($filename, $quality = null){        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $file = str::format($filename, $this->file);
        $map = array(
            'png' => 'png',
            'gif' => 'gif',
            'jpg' => 'jpeg',
            'jpeg' => 'jpeg',
        );

        unset($this->text);

        if (b::call('*image'.$map[$ext], array($this->im, $file, $quality)))
            return $file;    }

////////////////////////////////////////////////////////////////////////////////

    function resize($width, $height){        unset($this->text);        list($_width, $_height) = array(imagesx($this->im), imagesy($this->im));

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

    function text($text){
        return $this->text = new Image_Text($this->im, str::format($text, $this->file));
    }

////////////////////////////////////////////////////////////////////////////////

    function merge($filename, $pos = 'tl'){        $merge = new self($filename);        $x = $y = 0;
        $width = ($this->file['resize'] ? $this->file['resize']['width'] : $this->file['width']);
        $height = ($this->file['resize'] ? $this->file['resize']['height'] : $this->file['height']);

        if (strpos($pos, 'r') !== false)
            $x = ($width - $merge->file['width']);
        if (strpos($pos, 'b') !== false)
            $y = ($height - $merge->file['height']);

        unset($this->text);
        imagecopy($this->im, $merge->im, $x, $y, 0, 0, $merge->file['width'], $merge->file['height']);
        unset($merge);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function close(){        is_resource($this->im) and imagedestroy($this->im);    }

////////////////////////////////////////////////////////////////////////////////

    function __destruct(){        $this->close();    }

////////////////////////////////////////////////////////////////////////////////
}