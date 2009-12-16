<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://goodgirl.ru/berry>                             | ~ )\
    <http://goodgirl.ru/berry/license>                     /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Image_Text {
////////////////////////////////////////////////////////////////////////////////

    function __construct(&$im, $text){        $this->im = &$im;
        $this->text = $text;
        $this->width = imagesx($im);
        $this->height = imagesy($im);

        self::color()->pos()->size()->padding()->margin()->background();
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function hex($color){        $color = strtoupper(substr($color, 1));
        $color = (b::len($color) == 3 ? $color.$color : $color);

        for ($i = 0, $c = b::len($color); $i < $c; $i += 2)
            $result[] = hexdec($color[$i].$color[$i + 1]);

        return $result;    }

////////////////////////////////////////////////////////////////////////////////

    function size($size = 3){        $this->size = $size;        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function color($color = '#fff'){
        $this->color = b::call('*imagecolorallocate', array_merge(array($this->im), self::hex($color)));
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function pos($pos = 'tl'){
        $this->pos = $pos;
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function padding(){
        $this->padding = func_get_args();
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function margin(){
        $this->margin = func_get_args();
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function background($color = 0, $alpha = 100){        $this->background = array(
            ($color ? b::call('*imagecolorallocate', array_merge(array($this->im), self::hex($color))) : imagecolortransparent($this->im)),
            (!$color ? 0 : $alpha)
        );
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function __destruct(){        $x = $y = $pos_x = $pos_y = 0;
        $width = (b::len($this->text) * imagefontwidth($this->size));
        $height = imagefontheight($this->size);
        if (strpos($this->pos, 'r') !== false)
            $x = ($this->width - $width);
        if (strpos($this->pos, 'b') !== false)
            $y = ($this->height - $height);

        if (($padding = $this->padding) and ($len = b::len($padding)) != 3){
            $padding = ($len == 1 ? array($padding[0], $padding[0]) : $padding);
            $padding = (b::len($padding) == 2 ? array($padding[0], $padding[1], $padding[0], $padding[1]) : $padding);

            $pos_x = ($x ? ($x - $padding[3] * 2) : 0);
            $x = ($x ? ($x - $padding[3]) : $padding[1]);
            $width += ($padding[3] + $padding[1]);

            $pos_y = ($y ? ($y - $padding[2] * 2) : 0);
            $y = ($y ? ($y - $padding[2]) : $padding[0]);
            $height += ($padding[2] + $padding[0]);
        }

        if (($margin = $this->margin) and ($len = b::len($margin)) != 3){
            $margin = ($len == 1 ? array($margin[0], $margin[0]) : $margin);
            $margin = (b::len($margin) == 2 ? array($margin[0], $margin[1], $margin[0], $margin[1]) : $margin);

            if (strpos($this->pos, 'r') === false){
                $pos_x += $margin[3];
                $x += $margin[3];
            } else {
                $pos_x -= $margin[1];
                $x -= $margin[1];
            }

            if (strpos($this->pos, 'b') === false){
                $pos_y += $margin[0];
                $y += $margin[0];
            } else {
                $pos_y -= $margin[2];
                $y -= $margin[2];
            }
        }

        $im = imagecreatetruecolor($width, $height);
        imagefilledrectangle($im, $width, $height, 0, 0, $this->background[0]);
        imagecopymerge($this->im, $im, $pos_x, $pos_y, 0, 0, $width, $height, $this->background[1]);
        imagedestroy($im);

        imagestring($this->im, $this->size, $x, $y,  $this->text, $this->color);
    }

////////////////////////////////////////////////////////////////////////////////
}