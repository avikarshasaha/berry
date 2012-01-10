<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
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

    function save($filename, $quality = 80){
        $file = str::format($filename, $this->file);
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $map = array(
            'png' => 'png',
            'gif' => 'gif',
            'jpg' => 'jpeg',
            'jpeg' => 'jpeg',
        );

        if ($quality < 0)
            $quality = 10;

        if ($quality and $ext == 'png')            $quality = round(($quality > 0 ? $quality : 10) / -10 + 10);

        if ($file == '.'.$ext)
            $file = null;

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
        imagecopyresampled($this->im, $im, 0, 0, 0, 0, $width, $height, $_width, $_height);
        imagedestroy($im);

        $this->file['resize'] = compact('width', 'height');

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function merge($filename, $params = array()){        if ($filename instanceof self){
            $merge = new parent($this->im, $filename->im);
        } elseif (is_resource($filename)){
            $merge = new parent($this->im, $filename);
        } else {
            $image = new self($filename);
            $merge = new parent($this->im, $image->im);
        }

        foreach ($params as $k => $v)
            if (method_exists($merge, $k))
                call_user_func(array($merge, $k), $v);

        unset($merge);
        unset($image);
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function text($text, $params = array()){        $text = strtr(str::format($text, $this->file), array("\r" => '', "\t" => '    '));
        $size = 2;
        $ims = $width = $height = array();

        if ($params['font'])
            foreach (explode(' ', $params['font']) as $v){
                $v = trim($v);

                if ($v[0] == '#')
                    $color = $v;
                elseif (is_numeric($v))
                    $size = (int)$v;
                elseif ($v)
                    $font = $v;
            }

        foreach (explode("\n", $text) as $v){            $width[] = (strlen($v) * imagefontwidth($size));
            $height[] = imagefontheight($size);

            $ims[] = $im = imagecreatetruecolor(end($width), end($height));
            imagefill($im, 0, 0, -1);

            if ($font)
                imagettftext($im, $size, 0, 0, ($size + 1), self::color($color, $im), $font, $v);
            else
                imagestring($im, $size, 0, 0, $v, self::color($color, $im));
        }

        $im = imagecreatetruecolor(max($width), array_sum($height));
        imagefill($im, 0, 0, -1);

        foreach ($ims as $k => $v){
            imagecopy($im, $v, 0, ($k ? ($height[$k] * $k) : 0), 0, 0, $width[$k], $height[$k]);
            imagedestroy($v);
        }

        self::merge($im, $params);
        imagedestroy($im);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function GPS(){
        $exif = exif_read_data($this->file['path']);
        $func = create_function('$v', '$v = explode("/", $v); return ($v[0] / $v[1]);');

        $latitude = array_map($func, $exif['GPSLatitude']);
        $latitude = ($latitude[0] + ($latitude[1] / 60.0) + ($latitude[2] / 3600.0));
        $latitude = ($exif['GPSLatitudeRef'] != 'N' ? -$latitude : $latitude);

        $longitude = array_map($func, $exif['GPSLongitude']);
        $longitude = ($longitude[0] + ($longitude[1] / 60.0) + ($longitude[2] / 3600.0));
        $longitude = ($exif['GPSLongitudeRef'] != 'E' ? -$longitude : $longitude);

        return array($latitude, $longitude);
    }

////////////////////////////////////////////////////////////////////////////////

    function close(){        is_resource($this->im) and imagedestroy($this->im);    }

////////////////////////////////////////////////////////////////////////////////

    function __destruct(){        $this->close();    }

////////////////////////////////////////////////////////////////////////////////
}
