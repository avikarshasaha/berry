<?php                                                      /* `,
                                                           ,\, #
    B E R R Y                                              |/  ?
    <http://berry.goodgirl.ru/>                            | ~ )\
    <http://berry.goodgirl.ru/license/>                    /__/\ \____
                                                           /   \_/    \
    Лёха zloy и красивый <http://lexa.cutenews.ru>        / <_ ____,_-/\ __
---------------------------------------------------------/___/_____  \--'\|/----
                                                                   \/|*/
class Image_Merge {
////////////////////////////////////////////////////////////////////////////////

    function __construct(&$im1, $im2){        $this->im1 = &$im1;
        $this->im2 = $im2;

        self::background();
    }

////////////////////////////////////////////////////////////////////////////////

    protected static function color($color, $im = null){        $color = strtoupper(substr($color, 1));
        $len = strlen($color);

        if ($len == 3)
            $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];

        for ($i = 0; $i < 6; $i += 2)
            $result[] = hexdec($color[$i].$color[$i + 1]);

        if (!$im)
            return $result;

        array_unshift($result, $im);
        return b::call('*imagecolorallocate', $result);    }

////////////////////////////////////////////////////////////////////////////////

    protected static function csslike($string, $width = 0, $height = 0){
        $string = str_replace('px', '', strtolower($string));
        $array = array_map('trim', explode(' ', $string));

        if (reset($array) === '' or ($len = count($array)) == 3)
            return array();

        $array = ($len == 1 ? array($array[0], $array[0]) : $array);
        $array = (count($array) == 2 ? array($array[0], $array[1], $array[0], $array[1]) : $array);

        foreach ($array as $k => $v)
            if (substr($v, -1) == '%'){
                $w = ($k%2 == 0 ? $height : $width);
                $array[$k] = (($w / 100) * $v);
            }

        return $array;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function position($pos = 'tl'){
        $this->pos = $pos;
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function padding($padding = ''){        $this->padding = self::csslike($padding, imagesx($this->im1), imagesy($this->im1));
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function margin($margin = ''){        $this->margin = self::csslike($margin, imagesx($this->im1), imagesy($this->im1));
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function background($background = '100'){        $background = str_replace('px', '', strtolower($background));

        foreach (explode(' ', $background) as $v){            $v = trim($v);

            if ($v[0] == '#')
                $color = $v;
            elseif (is_numeric($v) or substr($v, -1) == '%')
                $alpha = $v;
        }

        $this->background = array($color, (100 - $alpha));
        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    protected function rotate($angle = 0){
        if ($angle)
            $this->im2 = imagerotate($this->im2, $angle, -1);

        return $this;
    }

////////////////////////////////////////////////////////////////////////////////

    function __destruct(){        $x = $y = $pos_x = $pos_y = 0;

        $width1 = imagesx($this->im1);
        $height1 = imagesy($this->im1);

        $width2 = imagesx($this->im2);
        $height2 = imagesy($this->im2);

        $is_top_pos = (strpos($this->pos, 'b') === false);
        $is_left_pos = (strpos($this->pos, 'r') === false);

        if (!$is_top_pos)
            $y = $pos_y = ($height1 - $height2);
        if (!$is_left_pos)
            $x = $pos_x = ($width1 - $width2);

        if ($padding = $this->padding){            $width2 += ($padding[1] + $padding[3]);
            $height2 += ($padding[0] + $padding[2]);

            if ($is_top_pos){
                $y += $padding[0];
            } else {
                $pos_y = ($y - $padding[0] - $padding[2]);
                $y -= $padding[2];
            }

            if ($is_left_pos){
                $x += $padding[3];
            } else {
                $pos_x = ($x - $padding[1] - $padding[3]);
                $x -= $padding[1];

            }
        }

        if ($margin = $this->margin){            if ($is_top_pos){                if ($margin[0] > 0){
                    $pos_y += $margin[0];
                    $y += $margin[0];
                } else {                    $height1 += abs($margin[0]);                }
            } else {
                if ($margin[2] > 0){
                    $pos_y -= $margin[2];
                    $y -= $margin[2];
                } else {
                    $height1 += abs($margin[2]);
                    $pos_y -=  $margin[2];
                    $y -=  $margin[2];
                    $margin[2] -= $margin[2];
                }
            }

            if ($is_left_pos){                if ($margin[3] > 0){
                    $pos_x += $margin[3];
                    $x += $margin[3];
                } else {
                    $width1 += abs($margin[3]);
                }
            } else {                if ($margin[1] > 0){
                    $pos_x -= $margin[1];
                    $x -= $margin[1];
                } else {
                    $width1 += abs($margin[1]);
                    $pos_x -=  $margin[1];
                    $x -=  $margin[1];
                    $margin[1] -= $margin[1];
                }
            }

            list($im, $this->im1) = array($this->im1, imagecreatetruecolor($width1, $height1));
            imagefill($this->im1, 0, 0, -1);
            imagesavealpha($this->im1, true);
            imagecopy($this->im1, $im, ($is_left_pos ? ($margin[3] < 0 ? abs($margin[3]) : 0) : ($margin[1] < 0 ? $margin[1] : 0)), ($is_top_pos ?  ($margin[0] < 0 ? abs($margin[0]) : 0) : ($margin[2] < 0 ? $margin[2] : 0)), 0, 0, imagesx($im), imagesy($im));
            imagedestroy($im);
        }

        $im = imagecreatetruecolor($width2, $height2);
        $background = $this->background;

        if ($background[0]){            $background[0] = (is_int($background[0]) ? $background[0] : self::color($background[0], $im));
            imagefilledrectangle($im, $width2, $height2, 0, 0, $background[0]);
        }

        imagecopymerge($this->im1, $im, $pos_x, $pos_y, 0, 0, $width2, $height2, $background[1]);
        imagedestroy($im);

        imagecopy($this->im1, $this->im2, $x, $y, 0, 0, imagesx($this->im2), imagesy($this->im2));
    }

////////////////////////////////////////////////////////////////////////////////
}
